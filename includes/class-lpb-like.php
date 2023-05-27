<?php

if ( class_exists( 'LPB_Like' ) ) {
	return;
}

class LPB_Like {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.0.0
	 */
	public function hooks(): void {
		add_action( 'wp_ajax_nopriv_lpb_like_post', array( $this, 'like' ) );
		add_action( 'wp_ajax_lpb_like_post', array( $this, 'like' ) );
	}

	/**
	 * Like a post.
	 * This function is called via AJAX.
	 *
	 * @since 1.0.0
	 */
	public function like(): void {
		$nonce = sanitize_text_field( $_POST['nonce'] );

		if ( ! wp_verify_nonce( $nonce, 'lpb-like-post-nonce' ) ) {
			wp_send_json_error( 'Invalid nonce' );
		}

		if ( ! isset( $_POST['post_id'] ) || ! isset( $_POST['count'] ) ) {
			wp_send_json_error( 'Invalid request' );
		}

		$post_id = intval( $_POST['post_id'] );
		$likes   = get_post_meta( $post_id, LPB_META_KEY, true );

		if ( ! $likes ) {
			$likes = 0;
		}

		// Update likes from the current post.
		$likes = $likes + intval( $_POST['count'] );
		update_post_meta( $post_id, LPB_META_KEY, $likes );

		$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

		// Update likes from the current user.
		if ( ! empty( $user_ip ) ) {
			$lpb_post     = new LPB_Post( $post_id );
			$ip_addresses = $lpb_post->ip_addresses();
			$user_count   = $ip_addresses[ $user_ip ] ?? 0;

			$ip_addresses[ $user_ip ] = $user_count + intval( $_POST['count'] );

			update_post_meta( $post_id, 'lpb_ip_addresses', $ip_addresses );
		}

		wp_send_json_success( array(
			'count' => (int) $_POST['count'],
			'likes' => $likes,
		) );
	}
}
