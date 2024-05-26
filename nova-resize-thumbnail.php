<?php
/*
 * Plugin Name: Nova Resize Thumbnail
 * Description: Resize images dynamically & adjust the crop point.
 * Version: 1.0.0
 * Author: DJABHipHop
 * Text Domain: nrt
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Nova_Resize_Thumbnail {
    public function __construct() {
        $this->init();
    }

    public function init() {
        require_once plugin_dir_path(__FILE__) . 'nova-resize-thumbnail-func.php';
        require_once plugin_dir_path(__FILE__) . 'nova-resize-thumbnail-metabox.php';
        add_filter('post_thumbnail_html', array($this, 'nrt_modify_post_thumbnail_html'), 10, 5);
    }

    public function nrt_modify_post_thumbnail_html($html, $postId, $postThumbnailId, $size, $attr) {
        // Get the sizes attribute for responsive images
        $attachmentSizes = wp_get_attachment_image_sizes($postThumbnailId, $size);

        // Resize the thumbnail
        $attachment = wp_get_attachment_image_src($postThumbnailId, $size);
        if (!$attachment) {
            return $html;
        }

        $imagePosition = get_post_meta($postThumbnailId, 'image_position', true);
        $resizeResult = nova_resize_thumbnail($postThumbnailId, array('width' => $attachment[1], 'height' => $attachment[is_single() ? 2 : 1], 'crop' => $imagePosition), true);

        if (is_array($resizeResult) && isset($resizeResult['url'], $resizeResult['width'], $resizeResult['height'])) {
            $newHtml = sprintf(
                '<img src="%s" width="%d" height="%d" %s sizes="%s" alt="%s"/>',
                esc_url($resizeResult['url']),
                intval($resizeResult['width']),
                intval($resizeResult['height']),
                $this->nrt_build_attribute_string($attr),
                esc_attr($attachmentSizes),
                esc_attr(get_post_meta($postThumbnailId, '_wp_attachment_image_alt', true))
            );
            return $newHtml;
        }

        return $html;
    }

    private function nrt_build_attribute_string($attributes) {
        // Check if $attributes is an array
        if (!is_array($attributes)) {
            return ''; // Return an empty string if $attributes is not an array
        }

        $attributeString = '';
        foreach ($attributes as $key => $value) {
            $attributeString .= sprintf('%s="%s" ', esc_attr($key), esc_attr($value));
        }
        return trim($attributeString);
    }
}

new Nova_Resize_Thumbnail();

// Define the function to update image position for image attachments
function nrt_update_all_image_position() {
    // Get all image attachment IDs
    $imageAttachments = get_posts(array(
        'post_type'      => 'attachment',
        'post_status'    => 'inherit',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'post_mime_type' => 'image', // Filter by MIME type to include only images
    ));

    // Update image position for each image attachment
    foreach ($imageAttachments as $attachmentId) {
        update_post_meta($attachmentId, 'image_position', 'center-top');
    }
}

// Register the activation hook
register_activation_hook(__FILE__, 'nrt_update_all_image_position');
