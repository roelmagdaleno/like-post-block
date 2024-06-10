<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Get the SVG icon markup for an icon.
 * The `$icon` parameter can be `HandThumbUpIcon` or `HeartIcon`.
 *
 * @since  1.0.0
 *
 * @param  string   $icon   The name of the icon to display.
 * @param  int      $size   The size of the icon in pixels.
 * @param  string   $type   The type of icon to display (inactive, active).
 * @return string           The SVG markup.
 */
function rolpb_get_svg_icon(
	string $icon = ROLPB_DEFAULT_ICON,
	int $size = 30,
	string $type = 'inactive'
): string {
	$icons = array(
		'inactive' => array(
			'HandThumbUpIcon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="' . $size . '" height="' . $size . '"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" /></svg>',
			'HeartIcon'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="' . $size . '" height="' . $size . '"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>',
		),
		'active'   => array(
			'HandThumbUpIcon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="' . $size . '" height="' . $size . '"><path d="M7.493 18.75c-.425 0-.82-.236-.975-.632A7.48 7.48 0 016 15.375c0-1.75.599-3.358 1.602-4.634.151-.192.373-.309.6-.397.473-.183.89-.514 1.212-.924a9.042 9.042 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H14.23c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23h-.777zM2.331 10.977a11.969 11.969 0 00-.831 4.398 12 12 0 00.52 3.507c.26.85 1.084 1.368 1.973 1.368H4.9c.445 0 .72-.498.523-.898a8.963 8.963 0 01-.924-3.977c0-1.708.476-3.305 1.302-4.666.245-.403-.028-.959-.5-.959H4.25c-.832 0-1.612.453-1.918 1.227z" /></svg>',
			'HeartIcon'       => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="' . $size . '" height="' . $size . '"><path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" /></svg>',
		),
	);

	return $icons[ $type ][ $icon ];
}

/**
 * Returns the block HTML.
 *
 * @since  1.0.0
 * @since  1.3.0 Change parameter type to WP_Post to get custom properties inside function.
 *
 * @param  int     $total_likes   The total number of likes for the post.
 * @param  int     $post_id       The post ID.
 * @param  array   $attributes    The block attributes.
 * @return string                 The rendered block HTML.
 */
function rolpb_get_rendered_html( int $total_likes, int $post_id, array $attributes ): string {
	// Set default for custom attributes.
	$attributes['icon_type'] ??= 'inactive';
	$attributes['likes_from_user'] ??= 0;

	$button_css       = 'active' === $attributes['icon_type'] ? 'wp-like-post__button--liked' : '';
	$block_attributes = ! str_contains( $_SERVER['REQUEST_URI'], '/wp-json/' )
		? get_block_wrapper_attributes( array( 'class' => 'wp-like-post__count' ) )
		: 'class="wp-like-post__count""';

	$gap_styles = rolpb_gap_styles( $attributes );

	$attributes['icon']           ??= ROLPB_DEFAULT_ICON;
	$attributes['iconColorValue'] ??= ROLPB_DEFAULT_ICON_COLOR_VALUE;
	$attributes['iconWidth']      ??= ROLPB_DEFAULT_ICON_WIDTH;

	$html  = '<div class="wp-like-post__wrapper" style="' . esc_attr( $gap_styles ) . '">';
	$html .= '<button type="button" class="wp-like-post__button ' . esc_attr( $button_css ) . '" ';
	$html .= 'style="height: ' . esc_attr( $attributes['iconWidth'] ) . 'px; ';
	$html .= 'color: ' . esc_attr( $attributes['iconColorValue'] ) . ';" ';
	$html .= 'data-post-id="' . esc_attr( $post_id ) . '" data-total-likes="' . esc_attr( $total_likes ) . '" ';
	$html .= 'data-likes-from-user="' . esc_attr( $attributes['likes_from_user'] ) . '">';
	$html .= rolpb_get_svg_icon( $attributes['icon'], $attributes['iconWidth'], $attributes['icon_type'] );
	$html .= '</button>';

	$like_unlike = $attributes['likeUnlike'] ?? false;

	if ( ! $like_unlike ) {
		$html .= '<div ' . $block_attributes . '>' . esc_html( $total_likes ) . '</div>';
	}

	$html .= '</div>';

	/**
	 * Filters the rendered block HTML.
	 *
	 * @since 1.1.0
	 * @since 1.3.0 Add `$post_id` parameters.
	 *
	 * @param string   $html         The rendered block HTML.
	 * @param int      $post_id      The post id.
	 * @param array    $attributes   The block attributes.
	 */
	return apply_filters( 'rolpb_likes_rendered_html', $html, $post_id, $attributes );
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
function rolpb_gap_styles( array $attributes ): string {
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
