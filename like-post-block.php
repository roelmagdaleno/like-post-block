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
require_once 'helpers.php';

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

	$likes      = ! $post ? 0 : lpb_get_post_likes( $post->ID );
	$user_likes = lpb_get_likes_from_current_ip_address( $post );
	$icon_type  = $user_likes ? 'active' : 'inactive';

	$html  = '<div class="wp-like-post__wrapper">';
	$html .= '<button type="button" class="wp-like-post__button" style="height: ' . $attributes['iconWidth'] . 'px">';
	$html .= lpb_get_svg_icon( $attributes['icon'], $attributes['iconWidth'], $icon_type );
	$html .= '</button>';
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

	$likes = get_post_meta( $post->ID, 'lpb_likes', true );
	$likes = empty( $likes ) ? 0 : $likes;

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
				'total'    => (int) $likes,
				'fromUser' => lpb_get_likes_from_current_ip_address( $post ),
			),
		),
		'url'   => admin_url( 'admin-ajax.php' ),
	) );

	return $block_content;
}

/**
 * Like a post.
 * This function is called via AJAX.
 *
 * @since 1.0.0
 */
function lpb_like_post(): void {
	$nonce = sanitize_text_field( $_POST['nonce'] );

	if ( ! wp_verify_nonce( $nonce, 'lpb-like-post-nonce' ) ) {
		wp_send_json_error( 'Invalid nonce' );
	}

	if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['count'] ) ) {
		wp_send_json_error( 'Invalid request' );
	}

	$post_id = intval( $_POST['post_id'] );
	$likes   = get_post_meta( $post_id, 'lpb_likes', true );

	if ( ! $likes ) {
		$likes = 0;
	}

	// Update likes from the current post.
	$likes = $likes + intval( $_POST['count'] );
	update_post_meta( $post_id, 'lpb_likes', $likes );

	$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

	// Update likes from the current user.
	if ( ! empty( $user_ip ) ) {
		$ip_addresses = lpb_get_ip_addresses_from_post( get_post( $post_id ) );
		$user_count   = $ip_addresses[ $user_ip ] ?? 0;

		$ip_addresses[ $user_ip ] = $user_count + intval( $_POST['count'] );

		update_post_meta( $post_id, 'lpb_ip_addresses', $ip_addresses );
	}

	wp_send_json_success( array(
		'count' => (int) $_POST['count'],
		'likes' => $likes,
	) );
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
