# Like Post Block

The Like Post Block plugin registers a WordPress block that allows you to add a like button to your WordPress block editor.

### Key Features

- Add a like button to any post type
- Limit the number of likes per user
- Save user's IP address to prevent multiple likes

## Styling

Style your like button by using multiple features.

### Colors

You can change the color of the icon and count by using the color picker.

### Typography

Change the typography of the count:

- Appearance
- Decoration
- Font family
- Font size

### Spacing

Change the spacing of the icon and count by changing the block spacing setting.

## Icon

You can select two icons:

- Like
- Heart

When the user hasn't like the post yet, the icon is outlined and when the user has liked the post, the icon is filled.

These icons are powered by [Heroicons](https://heroicons.com/).

### Size

You can only change the size of the icon by using the size setting.

## Count

The count is the number of likes a post has. You can increase the count by clicking the like button. The count is saved in the `wp_postmeta` database table, and is displayed to the user.

### Limit

You can limit the number of likes per user by using the limit setting.

Default: `10`

### IP Address

The IP address of the user is saved in the database to prevent multiple likes.

## Requirements

- WordPress 6.2+
- PHP 7.4+
