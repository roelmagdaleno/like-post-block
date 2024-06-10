=== Like Post Block ===
Contributors:      rokumetal
Tags:              like, heart, like post, block
Requires at least: 6.2
Tested up to:      6.5.4
Requires PHP:      7.4
Stable tag:        1.4.0
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Add a button to like any post type.

== Description ==

The Like Post Block plugin registers a WordPress block that allows you to add a like button to your WordPress block editor.

You can insert this block in a single post, page, custom post type, and you can also insert it in any Gutenberg template.

=== Key Features ===

* Add a like button to any post, page or custom post type
* Limit the number of likes per user
* Save user's IP address to prevent multiple likes
* Supports any Gutenberg template

=== Development ===

* [View on GitHub](https://github.com/roelmagdaleno/like-post-block)

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the plugin files to the `/wp-content/plugins/like-post-block` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Inside the Block Editor, search the "Like Post" block and insert it.

== Frequently Asked Questions ==

= Can I insert the block in any Gutenberg template? =

Yes, you can insert the block in any Gutenberg template.

= Can I limit the number of likes per user? =

Yes, you can limit the number of likes per user. The default value is 10 and the minimum value is 1. This setting is per block, so you can have different values for each block.

= Can I save the user's IP address to prevent multiple likes? =

Yes, you can save the user's IP address to prevent multiple likes. This action happens automatically.

= Can the user likes comments? =

No, the user can only like posts, pages and custom post types.

== Screenshots ==

1. Like Post block
2. Like Post block settings
3. Like Post block style settings
4. Inactive like button on the front-end
5. Active like button on the front-end

== Changelog ==

= 1.4.0 =

* New setting: Unlike when click the button again (#10)
* New setting: Add unlimited setting (#12)

= 1.3.0 =

* Like counter functionality uses last post inside query loop (#8).

= 1.2.0 =

* Restore the `index.asset.php` file because it's needed by WordPress.

= 1.1.0 =

* Render the like button with AJAX to avoid caching systems.
* The `rolpb_likes` meta key can be found in each REST API for posts, pages and custom post types.
* New `Likes` column in the posts, pages and custom post types list tables.

= 1.0.0 =

* Initial release

