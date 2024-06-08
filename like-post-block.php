<?php

/**
 * Plugin Name:       Like Post Block
 * Description:       Add a button to like any post type.
 * Requires at least: 6.2
 * Requires PHP:      7.4
 * Version:           1.4.0
 * Author:            Roel Magdaleno
 * Author URI:        https://roelmagdaleno.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       like-post-block
 *
 * @package           Like Post Block
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once 'required-files.php';

( new ROLPB_Block() )->hooks();
