<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_Like' ) ) {
	return;
}

class ROLPB_Like {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {
		add_action( 'wp_ajax_nopriv_rolpb_Like_post', array( $this, 'like' ) );
		add_action( 'wp_ajax_rolpb_Like_post', array( $this, 'like' ) );
		add_action( 'wp_ajax_nopriv_rolpb_get_post_likes', array( $this, 'get' ) );
		add_action( 'wp_ajax_rolpb_get_post_likes', array( $this, 'get' ) );
	}

	/**
	 * Get the likes from a post.
	 * This function is called via AJAX.
	 *
	 * @since 1.1.0
	 */
	public function get(): void {
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'rolpb-get-post-likes-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		if ( ! isset( $_POST['post_id'] ) ) {
			wp_send_json_error( 'The required data does not exist.' );
		}

		$post_id = intval( sanitize_text_field( $_POST['post_id'] ) );
		$post    = new ROLPB_Post( $post_id );

		wp_send_json_success( array(
			'likes'   => $post->likes(),
			'post_id' => $post_id,
		) );
	}

	/**
	 * Like a post.
	 * This function is called via AJAX.
	 *
	 * @since 1.0.0
	 */
	public function like(): void {
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'rolpb-like-post-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		if ( ! isset( $_POST['post_id'], $_POST['count'] ) ) {
			wp_send_json_error( 'The required data does not exist.' );
		}

		$post_id = intval( sanitize_text_field( $_POST['post_id'] ) );
		$likes   = get_post_meta( $post_id, ROLPB_META_KEY, true );

		if ( ! $likes ) {
			$likes = 0;
		}

		$count = intval( sanitize_text_field( $_POST['count'] ) );

		// Update likes from the current post.
		$likes = $likes + $count;
		update_post_meta( $post_id, ROLPB_META_KEY, $likes );

		$user_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		// Update likes from the current user.
		if ( ! empty( $user_ip ) ) {
			$ROLPB_Post     = new ROLPB_Post( $post_id );
			$ip_addresses = $ROLPB_Post->ip_addresses();
			$user_count   = $ip_addresses[ $user_ip ] ?? 0;

			$ip_addresses[ $user_ip ] = $user_count + $count;

			update_post_meta( $post_id, 'rolpb_ip_addresses', $ip_addresses );
		}

		wp_send_json_success( array(
			'count' => $count,
			'likes' => $likes,
		) );
	}
}
