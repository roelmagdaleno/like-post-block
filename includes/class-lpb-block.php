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

		if ( ! is_admin() ) {
			return;
		}

		( new LPB_Meta_Columns() )->hooks();
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

		if ( LPB_BLOCK_NAMESPACE !== $block['blockName'] ) {
			return $block_content;
		}

		$lpb_post = new LPB_Post( $post );

		wp_enqueue_script(
			'lpb-like',
			plugins_url( 'public/js/lpb-like.min.js', __DIR__ ),
			array(),
			LPB_VERSION,
			true
		);

		$icon       = $block['attrs']['icon'] ?? LPB_DEFAULT_ICON;
		$icon_width = $block['attrs']['iconWidth'] ?? LPB_DEFAULT_ICON_WIDTH;
		$icons      = array(
			'inactive' => lpb_get_svg_icon( $icon, $icon_width ),
			'active'   => lpb_get_svg_icon( $icon, $icon_width, 'active' ),
		);

		wp_localize_script( 'lpb-like', 'LPB', array(
			'limit'   => $block['attrs']['limit'] ?? LPB_DEFAULT_LIMIT,
			'nonce'   => wp_create_nonce( 'lpb-like-post-nonce' ),
			'post_id' => $post->ID,
			'likes'   => array(
				'total'    => $lpb_post->likes(),
				'fromUser' => $lpb_post->likes_from_user(),
			),
			'url'     => admin_url( 'admin-ajax.php' ),
			'icons'   => $icons,
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
					'default' => LPB_DEFAULT_ICON,
				),
				'iconWidth'      => array(
					'type'    => 'number',
					'default' => LPB_DEFAULT_ICON_WIDTH,
				),
				'iconColorValue' => array(
					'type'    => 'string',
					'default' => LPB_DEFAULT_ICON_COLOR_VALUE,
				),
				'limit'          => array(
					'type'    => 'number',
					'default' => LPB_DEFAULT_LIMIT,
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
	protected function template(
		int $likes,
		array $attributes,
		string $icon_type = 'inactive'
	): string {
		$button_css       = 'active' === $icon_type ? 'wp-like-post__button--liked' : '';
		$block_attributes = ! str_contains( $_SERVER['REQUEST_URI'], '/wp-json/' )
			? get_block_wrapper_attributes( array( 'class' => 'wp-like-post__count' ) )
			: 'class="wp-like-post__count""';

		$gap_styles = $this->gap_styles( $attributes );

		$html  = '<div class="wp-like-post__wrapper" style="' . $gap_styles . '">';
		$html .= '<button type="button" class="wp-like-post__button ' . $button_css . '" ';
		$html .= 'style="height: ' . $attributes['iconWidth'] . 'px; ';
		$html .= 'color: ' . $attributes['iconColorValue'] . ';">';
		$html .= lpb_get_svg_icon( $attributes['icon'], $attributes['iconWidth'], $icon_type );
		$html .= '</button>';

		$html .= '<div ' . $block_attributes . '>' . $likes . '</div>';
		$html .= '</div>';

		/**
		 * Filters the rendered block HTML.
		 *
		 * @since 1.1.0
		 *
		 * @param string   $html         The rendered block HTML.
		 * @param array    $attributes   The block attributes.
		 */
		return apply_filters( 'lpb_likes_rendered_html', $html, $attributes );
	}

	/**
	 * Returns the gap styles.
	 * This code is the same as in the `wp-includes/blocks/gallery.php` file.
	 *
	 * @since  1.0.0
	 *
	 * @param  array   $attributes   The block attributes.
	 * @return string                The gap styles.
	 */
	protected function gap_styles( array $attributes ): string {
		$gap = _wp_array_get( $attributes, array( 'style', 'spacing', 'blockGap' ) );
		$gap = is_string( $gap ) ? $gap : '';
		$gap = $gap && preg_match( '%[\\\(&=}]|/\*%', $gap ) ? null : $gap;

		// Get spacing CSS variable from preset value if provided.
		if ( is_string( $gap ) && str_contains( $gap, 'var:preset|spacing|' ) ) {
			$index_to_splice = strrpos( $gap, '|' ) + 1;
			$slug            = _wp_to_kebab_case( substr( $gap, $index_to_splice ) );
			$gap             = "var(--wp--preset--spacing--$slug)";
		}

		return empty( $gap ) ? '' : 'gap:' . $gap . ';';
	}
}
