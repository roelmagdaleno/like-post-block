<?php

if ( class_exists( 'LPB_Block' ) ) {
	return;
}

class LPB_Block {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_filter( 'render_block', array( $this, 'enqueue_assets' ), 10, 2 );

		( new LPB_Like() )->hooks();
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

		if ( empty( $block['blockName'] ) ) {
			return $block_content;
		}

		if ( LPB_BLOCK_NAMESPACE !== $block['blockName'] ) {
			return $block_content;
		}

		$lpb_post = new LPB_Post( $post );

		wp_enqueue_script(
			'lpb-like',
			plugins_url( 'public/js/lpb-like.js', __FILE__ ),
			array(),
			LPB_VERSION,
			true
		);

		wp_localize_script( 'lpb-like', 'LPB', array(
			'limit' => 10,
			'nonce' => wp_create_nonce( 'lpb-like-post-nonce' ),
			'post'  => array(
				'id'    => $post->ID,
				'likes' => array(
					'total'    => $lpb_post->likes(),
					'fromUser' => $lpb_post->likes_from_user(),
				),
			),
			'url'   => admin_url( 'admin-ajax.php' ),
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
				'icon'      => array(
					'type'    => 'string',
					'default' => 'HandThumbUpIcon',
				),
				'iconWidth' => array(
					'type'    => 'number',
					'default' => 30,
				),
			),
			'render_callback' => array( $this, 'render' ),
		) );
	}

	/**
	 * Render the block on the frontend.
	 *
	 * @since  1.0.0
	 *
	 * @param  array   $attributes   The block attributes.
	 * @return string                The rendered block HTML.
	 */
	public function render( array $attributes ): string {
		global $post;

		if ( ! $post ) {
			return $this->template( 0, $attributes );
		}

		$lpb_post  = new LPB_Post( $post );
		$icon_type = $lpb_post->likes_from_user() ? 'active' : 'inactive';

		return $this->template( $lpb_post->likes(), $attributes, $icon_type );
	}

	/**
	 * Returns the block HTML.
	 *
	 * @since  1.0.0
	 *
	 * @param  int      $likes        The number of likes.
	 * @param  array    $attributes   The block attributes.
	 * @param  string   $icon_type    The icon type.
	 * @return string                 The rendered block HTML.
	 */
	protected function template( int $likes, array $attributes, string $icon_type = 'inactive' ): string {
		$html  = '<div class="wp-like-post__wrapper">';
		$html .= '<button type="button" class="wp-like-post__button" style="height: ' . $attributes['iconWidth'] . 'px">';
		$html .= lpb_get_svg_icon( $attributes['icon'], $attributes['iconWidth'], $icon_type );
		$html .= '</button>';
		$html .= '<div class="wp-like-post__count">';
		$html .= $likes;
		$html .= '</div> </div>';

		return $html;
	}
}
