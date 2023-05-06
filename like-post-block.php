<?php

/**
 * Plugin Name:       Like Post Block
 * Description:       Example block written with ESNext standard and JSX support – build step required.
 * Requires at least: 6.2
 * Requires PHP:      8.0
 * Version:           1.0.0
 * Author:            Roel Magdaleno
 * Author URI:        https://roelmagdaleno.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       like-post-block
 *
 * @package           Like Post Block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets, so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function create_block_like_post_block_init(): void {
	register_block_type( __DIR__ . '/build' );
}

add_action( 'init', 'create_block_like_post_block_init' );
