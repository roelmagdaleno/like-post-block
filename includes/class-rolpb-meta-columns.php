<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_Meta_Columns' ) ) {
	return;
}

class ROLPB_Meta_Columns {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.1.0
	 */
	public function hooks(): void {
		add_action( 'admin_init', array( $this, 'post_type_hooks' ) );
		add_action( 'pre_get_posts', array( $this, 'sort_by_likes' ) );
	}

	/**
	 * Sort the posts by likes.
	 *
	 * @since 1.1.0
	 *
	 * @param WP_Query   $query   The WP_Query instance.
	 */
	public function sort_by_likes( WP_Query $query ): void {
		$order_by = $query->get( 'orderby' );

		if ( 'likes' !== $order_by ) {
			return;
		}

		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', ROLPB_META_KEY );
	}

	/**
	 * Register the action and filter hooks for public post types.
	 *
	 * @since 1.1.0
	 */
	public function post_type_hooks(): void {
		$post_types = get_post_types( array( 'public' => true ) );

		if ( empty( $post_types ) ) {
			return;
		}

		foreach ( $post_types as $post_type ) {
			add_filter( 'manage_' . $post_type . '_posts_columns', array( $this, 'column_heading' ) );
			add_action( 'manage_' . $post_type . '_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
			add_action( 'manage_edit-' . $post_type . '_sortable_columns', array( $this, 'column_sort' ), 10, 2 );
		}
	}

	/**
	 * Add the "Likes" column in post type tables columns.
	 *
	 * @since  1.1.0
	 *
	 * @param  array   $columns   The post type tables columns.
	 * @return array              The post type tables columns.
	 */
	public function column_heading( array $columns ): array {
		$columns['likes'] = 'Likes';
		return $columns;
	}

	/**
	 * Render the "Likes" column content.
	 *
	 * @since 1.1.0
	 *
	 * @param string   $column_name   The column name.
	 * @param int      $post_id       The post ID.
	 */
	public function column_content( string $column_name, int $post_id ): void {
		if ( 'likes' !== $column_name ) {
			return;
		}

		$likes = get_post_meta( $post_id, ROLPB_META_KEY, true );

		echo esc_html( $likes );
	}

	/**
	 * Add the columns to be sorted in the post type tables.
	 *
	 * @since  1.1.0
	 *
	 * @param  array   $columns   The post type tables columns to be sorted.
	 * @return array              The post type tables columns to be sorted.
	 */
	public function column_sort( array $columns ): array {
		$columns['likes'] = 'likes';
		return $columns;
	}
}
