# Nova Image Resizer

Nova Image Resizer was originally developed by `wpexplorer` 7 years ago, and recently converted into a plugin by `DJABHipHop` of `WP-Developer-Hub` on `GitHub`.

Nova Image Resizer allow you to easily crops images on-the-fly in WordPress with supports custom crop locations and updates image metadata on image creation to support third-party image optimization plugins. The plugin uses all native WP functions for image resizing.

## Function: nova_resize_image_attachments

### Description:
nova_resize_image_attachments is meant to be use in a plugin.
Resizes and crops an image attachment on-the-fly according to the specified size and crop location.

### Parameters:
- `$attach_id` (int): The ID of the image attachment to resize.
- `$size` (string): Optional. The target size of the image. Accepts standard WordPress image size names (e.g., 'thumbnail', 'medium', 'large') or custom sizes defined in the theme or by plugins. Default is an empty string.
- `$retina` (bool): Optional. Whether to generate a retina version of the image. Default is `false`.

### Return Value:
- (string|array|bool) The resized image URL or an array containing the resized image attributes. Returns `false` if the function fails to resize the image.

### Example Usage:
```php
if (function_exists('nova_resize_image_attachments')) {
    // The function exists, you can use it
    $attach_id = get_post_thumbnail_id($post->ID);
    $size = 'medium';
    $retina = true;

    $resizeResult = nova_resize_image_attachments($attach_id, $size, $retina);
    
    // Check if the function successfully resized the image
    if ($resizeResult) {
        // Example usage of the resized image URL in HTML markup
        echo '<img src="' . esc_url($resizeResult['url']) . '" width="' . intval($resizeResult['width']) . '" height="' . intval($resizeResult['height']) . '" alt="Resized Image">';
    } else {
        // Use default image HTML markup here
        echo '<img src="default-image.jpg" alt="Default Image">';
    }
} else {
    // The function does not exist
    // Use default WordPress image HTML markup with parameters here
    echo '<img src="default-wordpress-image.jpg" alt="Default WordPress Image">';
}
```

#### Notes:
- Ensure that the `Nova Image Resizer plugin` is install in your WordPress website.
- Use standard WordPress image size names or custom sizes for the `$size` parameter.
- Set `$retina` to `true` to generate a retina version of the image.

Original ther: **wpexplorer**
License: **GPL**
