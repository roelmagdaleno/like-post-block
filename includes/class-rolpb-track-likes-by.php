<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( class_exists( 'ROLPB_TrackLikesBy' ) ) {
    return;
}

class ROLPB_TrackLikesBy {
    protected ROLPB_Post $rolpb_post;

    public function __construct( public array $data ) {
        $this->rolpb_post = new ROLPB_Post( $this->data['post_id'] );
    }

    public function update(): void {
        $track_likes_by = $this->data['track_likes_by'] ?? 'ip_address';

        if ( ! method_exists( $this, $track_likes_by ) ) {
            return;
        }

        $this->{$track_likes_by}();
    }

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
