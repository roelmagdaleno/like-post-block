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

require_once 'required-files.php';

( new LPB_Block() )->hooks();
