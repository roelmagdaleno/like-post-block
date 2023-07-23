<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_REST_API' ) ) {
	return;
}

class ROLPB_REST_API {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.1.0
	 */
	public function hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_meta_fields' ) );
	}

	/**
	 * Register the meta fields for the REST API.
	 * The only meta key we need inside the REST API is the number of likes.
	 *
	 * The custom fields functionality must be enabled for the post type in order to work.
	 *
	 * @since 1.1.0
	 */
	public function register_meta_fields(): void {
		$post_types = get_post_types( array( 'public' => true ) );

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			register_meta( $post_type, ROLPB_META_KEY, array(
				'show_in_rest' => true,
				'single'       => true,
				'type'         => 'integer',
			) );
		}
	}
}
