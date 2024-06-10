<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_Block' ) ) {
	return;
}

class ROLPB_Block {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'render_block', array( $this, 'enqueue_assets' ), 10, 2 );

		( new ROLPB_Like() )->hooks();
		( new ROLPB_REST_API() )->hooks();

		if ( ! is_admin() ) {
			return;
		}

		( new ROLPB_Meta_Columns() )->hooks();
	}

	/**
	 * Enqueues the assets needed for the block.
	 * If the block is not present in the current post, the assets are not enqueued.
	 *
	 * We have to use the `render_block` filter instead of `enqueue_scripts` because
	 * there's no way to check if the block is present in a template.
	 *
	 * @see https://github.com/WordPress/gutenberg/issues/38120#issuecomment-1019913721
	 *
	 * @since 1.0.0
	 */
	public function enqueue_assets( string $block_content, array $block ): string {
		global $post;

		if ( ! $post || empty( $block['blockName'] ) ) {
			return $block_content;
		}

		if ( ROLPB_BLOCK_NAMESPACE !== $block['blockName'] ) {
			return $block_content;
		}

		wp_enqueue_script(
			'lpb-like',
			plugins_url( 'public/js/rolpb-like.min.js', __DIR__ ),
			array(),
			ROLPB_VERSION,
			true
		);

		$icon       = $block['attrs']['icon'] ?? ROLPB_DEFAULT_ICON;
		$icon_width = $block['attrs']['iconWidth'] ?? ROLPB_DEFAULT_ICON_WIDTH;
		$icons      = array(
			'inactive' => rolpb_get_svg_icon( $icon, $icon_width ),
			'active'   => rolpb_get_svg_icon( $icon, $icon_width, 'active' ),
		);

		$block['attrs']['renderWithAjax'] ??= true;
		$block['attrs']['unlimited'] ??= false;
		$block['attrs']['likeUnlike'] ??= false;

		wp_localize_script( 'lpb-like', 'ROLPB', array(
			'limit'      => $block['attrs']['limit'] ?? LPB_DEFAULT_LIMIT,
			'unlimited'  => $block['attrs']['unlimited'] ?? false,
			'likeUnlike' => $block['attrs']['likeUnlike'] ?? false,
			'nonces'     => array(
				'getLikes'   => wp_create_nonce( 'rolpb-get-post-likes-nonce' ),
				'likePost'   => wp_create_nonce( 'rolpb-like-post-nonce' ),
				'unlikePost' => wp_create_nonce( 'rolpb-unlike-post-nonce' ),
			),
			'url'        => admin_url( 'admin-ajax.php' ),
			'icons'      => $icons,
			'attributes' => $block['attrs'],
		) );

		return $block_content;
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets, so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @since 1.0.0
	 */
	public function register_block(): void {
		register_block_type( dirname( __DIR__ ) . '/build', array(
			'attributes'      => array(
				'icon'           => array(
					'type'    => 'string',
					'default' => ROLPB_DEFAULT_ICON,
				),
				'iconWidth'      => array(
					'type'    => 'number',
					'default' => ROLPB_DEFAULT_ICON_WIDTH,
				),
				'iconColorValue' => array(
					'type'    => 'string',
					'default' => ROLPB_DEFAULT_ICON_COLOR_VALUE,
				),
				'limit'          => array(
					'type'    => 'number',
					'default' => LPB_DEFAULT_LIMIT,
				),
				'unlimited'      => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'likeUnlike'     => array(
					'type'    => 'boolean',
					'default' => false,
				),
				'renderWithAjax' => array(
					'type'    => 'boolean',
					'default' => true,
				),
			),
			'render_callback' => array( $this, 'render' ),
		) );
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @since  1.0.0
	 * @since  1.3.0 Send custom attributes inside `$attributes` array.
	 *
	 * @param  array   $attributes   The block attributes.
	 * @return string                The rendered block HTML.
	 */
	public function render( array $attributes ): string {
		global $post;

		if ( ! $post ) {
			return rolpb_get_rendered_html( 0, 0, $attributes );
		}

		$rolpb_post      = new ROLPB_Post( $post );
		$likes           = $rolpb_post->likes();
		$likes_from_user = $rolpb_post->likes_from_user();

		// Set custom attributes.
		$attributes['icon_type']       = $likes && $likes_from_user ? 'active' : 'inactive';
		$attributes['likes_from_user'] = $likes_from_user;

		return rolpb_get_rendered_html( $likes, $post->ID, $attributes );
	}
}
