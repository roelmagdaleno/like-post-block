<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_TrackLikesBy' ) ) {
    return;
}

class ROLPB_TrackLikesBy {
    /**
     * The post object.
     *
     * @since 1.0.0
     *
     * @var ROLPB_Post $rolpb_post The post object.
     */
    protected ROLPB_Post $rolpb_post;

    /**
     * Initialize the class properties.
     *
     * @since 1.5.0
     *
     * @param array{post_id: int, count: int, action: string, track_likes_by: string} $data The data array.
     */
    public function __construct( public array $data ) {
        $this->rolpb_post = new ROLPB_Post( $this->data['post_id'] );
    }

    /**
     * Update the likes count based on the action and `track_likes_by`.
     *
     * This method checks the `track_likes_by` property to determine how to track likes.
     * It can either track by IP address or user ID.
     *
     * The method first checks if the `track_likes_by` property is set and if the corresponding method exists.
     * If it does, it calls that method to update the likes count.
     *
     * @since 1.5.0
     *
     * @return void
     */
    public function update(): void {
        $track_likes_by = $this->data['track_likes_by'] ?? 'ip_address';

        if ( ! method_exists( $this, $track_likes_by ) ) {
            return;
        }

        $this->{$track_likes_by}();
    }

    /**
     * Update the likes count based on the user's IP address.
     *
     * This method retrieves the user's IP address and updates the likes count accordingly.
     *
     * @since 1.5.0
     *
     * @return void
     */
    protected function ip_address(): void {
        $user_ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] ?? '' );

        if ( empty( $user_ip ) ) {
            return;
        }

        $ip_addresses = $this->rolpb_post->ip_addresses();
        $user_count   = $ip_addresses[ $user_ip ] ?? 0;

        // Decide whether to sum or subtract the count
        $ip_addresses[ $user_ip ] = $user_count + ('like' === $this->data['action'] ? $this->data['count'] : -$this->data['count']);

        update_post_meta( $this->data['post_id'], 'rolpb_ip_addresses', $ip_addresses );
    }

    /**
     * Update the likes count based on the user's ID.
     *
     * This method retrieves the current user's ID and updates the likes count accordingly.
     *
     * @since 1.5.0
     *
     * @return void
     */
    protected function user_id(): void {
        $user_id = get_current_user_id();

        if ( empty( $user_id ) ) {
            return;
        }

        $user_ids   = $this->rolpb_post->user_ids();
        $user_count = $user_ids[ $user_id ] ?? 0;

        // Decide whether to sum or subtract the count
        $user_ids[ $user_id ] = $user_count + ('like' === $this->data['action'] ? $this->data['count'] : -$this->data['count']);

        update_post_meta( $this->data['post_id'], 'rolpb_user_ids', $user_ids );
    }
}
