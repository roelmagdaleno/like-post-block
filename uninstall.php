<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_post_meta_by_key( 'lpb_likes' );
delete_post_meta_by_key( 'lpb_ip_addresses' );
