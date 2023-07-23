<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( 'rolpb_likes' );
delete_post_meta_by_key( 'rolpb_ip_addresses' );
