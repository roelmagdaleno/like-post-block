<?php

/**
 * Plugin Name:       Like Post Block
 * Description:       Example block written with ESNext standard and JSX support – build step required.
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Version:           1.0.0
 * Author:            Roel Magdaleno
 * Author URI:        https://roelmagdaleno.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       like-post-block
 *
 * @package           Like Post Block
 */

require_once 'constants.php';

/**
 * Render the block on the frontend.
 *
 * @since  1.0.0
 *
 * @param  array   $attributes   The block attributes.
 * @return string                The rendered block HTML.
 */
function lpb_render_block( array $attributes ): string {
	global $post;

	$likes = ! $post ? 0 : lpb_get_post_likes( $post->ID );

	$html  = '<div class="wp-like-post__wrapper">';
	$html .= lpb_get_svg_icon( $attributes['icon'], $attributes['iconWidth'] );
	$html .= '<div class="wp-like-post__count">';
	$html .= $likes;
	$html .= '</div> </div>';

	return $html;
}

/**
 * Get the number of likes for a post.
 *
 * @since  1.0.0
 *
 * @param  int   $post_id   The ID of the post to get the likes for.
 * @return int              The number of likes for the post.
 */
function lpb_get_post_likes( int $post_id ): int {
	$likes = get_post_meta( $post_id, 'lpb_likes', true );

	if ( empty( $likes ) ) {
		$likes = 0;
	}

	return $likes;
}

/**
 * Get the SVG icon markup for an icon.
 * The `$icon` parameter can be `HandThumbUpIcon` or `HeartIcon`.
 *
 * @since  1.0.0
 *
 * @param  string   $icon   The name of the icon to display.
 * @param  int      $size   The size of the icon in pixels.
 * @return string           The SVG markup.
 */
function lpb_get_svg_icon( string $icon = 'HandThumbUpIcon', int $size = 30 ): string {
	$icons = array(
		'HandThumbUpIcon' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="' . $size . '" height="' . $size . '"><path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" /></svg>',
		'HeartIcon'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="' . $size . '" height="' . $size . '"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" /></svg>',
	);

	return $icons[ $icon ];
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
function lpb_enqueue_assets( string $block_content, array $block ): string {
	global $post;

	if ( empty( $block['blockName'] ) ) {
		return $block_content;
	}

	if ( LPB_BLOCK_NAMESPACE !== $block['blockName'] ) {
		return $block_content;
	}

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
		'post'  => $post->ID,
		'url'   => admin_url( 'admin-ajax.php' ),
	) );

	return $block_content;
}

function lpb_like_post(): void {
	$nonce = sanitize_text_field( $_POST['nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'lpb-like-post-nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
	}

	$post_id = intval( $_POST['post_id'] );
	$likes   = get_post_meta( $post_id, 'lpb_likes', true );

	if ( ! $likes ) {
		$likes = 0;
	}

	$likes = $likes + 1;

	update_post_meta( $post_id, 'lpb_likes', $likes );

	wp_send_json_success( $likes );
}

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets, so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @since 1.0.0
 */
function create_block_like_post_block_init(): void {
	register_block_type( __DIR__ . '/build', array(
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
		'render_callback' => 'lpb_render_block',
	) );
}

add_action( 'init', 'create_block_like_post_block_init' );
add_filter( 'render_block', 'lpb_enqueue_assets', 10, 2 );
add_action( 'wp_ajax_nopriv_lpb_like_post', 'lpb_like_post' );
add_action( 'wp_ajax_lpb_like_post', 'lpb_like_post' );
