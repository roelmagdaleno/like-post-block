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
		add_action( 'wp_ajax_nopriv_rolpb_like_post', array( $this, 'like' ) );
		add_action( 'wp_ajax_rolpb_like_post', array( $this, 'like' ) );

		add_action( 'wp_ajax_nopriv_rolpb_unlike_post', array( $this, 'unlike' ) );
		add_action( 'wp_ajax_rolpb_unlike_post', array( $this, 'unlike' ) );

		add_action( 'wp_ajax_nopriv_rolpb_get_post_likes', array( $this, 'get' ) );
		add_action( 'wp_ajax_rolpb_get_post_likes', array( $this, 'get' ) );
	}

	/**
	 * Get the likes from a post.
	 * This function is called via AJAX.
	 *
	 * @since 1.1.0
	 * @since 1.3.0 Accept post ids as a comma-separated string (bulk mode).
	 */
	public function get(): void {
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'rolpb-get-post-likes-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		if ( empty( $_POST['post_ids'] ) ) {
			wp_send_json_error( 'Missing post ids.' );
		}

		$post_ids = explode( ',', sanitize_text_field( $_POST['post_ids'] ) );

		if ( empty( $post_ids ) ) {
			wp_send_json_error( 'Invalid post ids.' );
		}

		$likes = array();

		foreach ( $post_ids as $post_id ) {
			$post = new ROLPB_Post( $post_id );
			$likes[ $post_id ] = $post->likes();
		}

		wp_send_json_success( $likes );
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
			$rolpb_post   = new ROLPB_Post( $post_id );
			$ip_addresses = $rolpb_post->ip_addresses();
			$user_count   = $ip_addresses[ $user_ip ] ?? 0;

			$ip_addresses[ $user_ip ] = $user_count + $count;

			update_post_meta( $post_id, 'rolpb_ip_addresses', $ip_addresses );
		}

		wp_send_json_success( array(
			'count' => $count,
			'likes' => $likes,
		) );
	}

	/**
	 * Unlike a post.
	 * This function is called via AJAX.
	 *
	 * @since 1.4.0
	 */
	public function unlike(): void {
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'rolpb-unlike-post-nonce' ) ) {
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
		$likes = $likes - $count;
		update_post_meta( $post_id, ROLPB_META_KEY, $likes );

		$user_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

		// Update likes from the current user.
		if ( ! empty( $user_ip ) ) {
			$rolpb_post   = new ROLPB_Post( $post_id );
			$ip_addresses = $rolpb_post->ip_addresses();
			$user_count   = $ip_addresses[ $user_ip ] ?? 0;

			$ip_addresses[ $user_ip ] = $user_count - $count;

			update_post_meta( $post_id, 'rolpb_ip_addresses', $ip_addresses );
		}

		wp_send_json_success( array(
			'count' => $count,
			'likes' => $likes,
		) );
	}
}
