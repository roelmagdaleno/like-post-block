<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_Post' ) ) {
	return;
}

class ROLPB_Post {
	/**
	 * The post object.
	 *
	 * @since 1.0.0
	 *
	 * @var   WP_Post   $post   The post object.
	 */
	protected WP_Post $post;

	/**
	 * Initialize the class.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|int|string   $post   The post object or ID.
	 */
	public function __construct( $post ) {
		$this->post = $post instanceof WP_Post ? $post : get_post( $post );
	}

	/**
	 * Get the number of likes for a post.
	 *
	 * @since  1.0.0
	 *
	 * @return int The number of likes for the post.
	 */
	public function likes(): int {
		$likes = get_post_meta( $this->post->ID, ROLPB_META_KEY, true );
		return empty( $likes ) ? 0 : $likes;
	}

	/**
	 * Get the number of likes from the current ip address.
	 *
	 * @since  1.0.0
	 *
	 * @return int   The number of likes from the current ip address.
	 */
	public function likes_from_user(): int {
		$user_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		if ( empty( $user_ip ) ) {
			return 0;
		}

		$ip_addresses = $this->ip_addresses();
		return $ip_addresses[ $user_ip ] ?? 0;
	}

	/**
	 * Get the ip addresses for the current post.
	 * These ip addresses are used to prevent users from liking a post multiple times.
	 *
	 * @since  1.0.0
	 *
	 * @return array   The IP addresses for the post.
	 */
	public function ip_addresses(): array {
		$ip_addresses = get_post_meta( $this->post->ID, 'rolpb_ip_addresses', true );
		return empty( $ip_addresses ) ? array() : $ip_addresses;
	}
}
