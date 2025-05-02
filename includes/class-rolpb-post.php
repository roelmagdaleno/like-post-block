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
	    $user_id = get_current_user_id();

        if ( empty( $user_ip ) && empty( $user_id ) ) {
            return 0;
        }

        $ip_addresses = $this->ip_addresses();
        $user_ids     = $this->user_ids();

        $likes_from_ip_address = $ip_addresses[ $user_ip ] ?? 0;
		$likes_from_user_id    = $user_ids[ $user_id ] ?? 0;

		// If the user is logged in, we will use the user ID to track likes.
		if ( $user_id ) {
			return $likes_from_user_id;
		}

		// If the user is not logged in, we will use the IP address to track likes.
		return $likes_from_ip_address;
    }

    /**
     * Get the IP addresses for the current post.
     * These IP addresses are used to prevent users from liking a post multiple times.
     *
     * @since  1.0.0
     *
     * @return array   The IP addresses for the post.
     */
    public function ip_addresses(): array {
        $ip_addresses = get_post_meta( $this->post->ID, 'rolpb_ip_addresses', true );
        return empty( $ip_addresses ) ? array() : $ip_addresses;
    }

    /**
     * Get the user IDs for the current post.
     * These user IDs are used to prevent users from liking a post multiple times.
     *
     * @since  1.5.0
     *
     * @return array   The user IDs for the post.
     */
    public function user_ids(): array {
        $user_ids = get_post_meta( $this->post->ID, 'rolpb_user_ids', true );
        return empty( $user_ids ) ? array() : $user_ids;
    }
}
