<?php

if ( class_exists( 'LPB_Meta_Columns' ) ) {
	return;
}

class LPB_Meta_Columns {
	/**
	 * Register the action and filter hooks.
	 *
	 * @since 1.1.0
	 */
	public function hooks(): void {
		$this->post_type_hooks();
	}

	protected function post_type_hooks(): void {
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
		$columns['likes'] = __( 'Likes', 'like-post-block' );
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

		$likes = get_post_meta( $post_id, 'lpb_likes', true );

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
