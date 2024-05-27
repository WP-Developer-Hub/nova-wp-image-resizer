<?php
/**
 * Resize images dynamically.
 *
 * @param string $attach_id
 * @param string|array $size
 * @param bool $retina
 * @return array|string
 * @since 1.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}
function nova_resize_thumbnail($attach_id, $size = '', $retina = false) {
    if (!$attach_id) {
        return 'Invalid attachment ID.';
    }

    // Initialize variables
    $retina_support = false;
    $is_intermediate = false;
    $int_size = isset($size['size']) ? $size['size'] : '';
    $width = isset($size['width']) ? $size['width'] : '';
    $height = isset($size['height']) ? $size['height'] : '';
    $crop = isset($size['crop']) ? $size['crop'] : 'center-center';

    // Sanitize dimensions
    $width = intval($width);
    $height = intval($height);
    $crop = is_array($crop) ? implode('-', $crop) : $crop;

    // Retrieve attachment data
    $src = wp_get_attachment_image_src($attach_id, 'full');
    if (!$src) {
        return 'Image not found.';
    }
    $path = get_attached_file($attach_id);
    if (empty($path)) {
        return 'File path not found.';
    }

    // Prepare file paths and names
    $info = pathinfo($path);
    $extension = '.' . $info['extension'];
    $path_no_ext = $info['dirname'] . '/' . $info['filename'];

    // Crop validation
    $crop_locations = array(
        'left-top', 'right-top', 'center-top', 'left-center', 'right-center',
        'center-center', 'left-bottom', 'right-bottom', 'center-bottom'
    );
    $crop_suffix = (is_string($crop) && in_array($crop, $crop_locations)) ? $crop : '';
    $crop_array = $crop_suffix ? explode('-', $crop) : $crop;

    // Calculate output dimensions after resize
    $cropped_dims = image_resize_dimensions($src[1], $src[2], $width, $height, $crop_array);
    if (!$cropped_dims) {
        return 'Invalid resize dimensions.';
    }

    // Target image size dims
    $dst_w = isset($cropped_dims[4]) ? $cropped_dims[4] : '';
    $dst_h = isset($cropped_dims[5]) ? $cropped_dims[5] : '';

    // Suffix width and height values
    $s_width = $width ? $width : $dst_w;
    $s_height = $height ? $height : $dst_h;

    // Define suffix
    if ($retina) {
        $suffix = ($s_width / 2) . 'x' . ($s_height / 2);
    } else {
        $suffix = $s_width . 'x' . $s_height;
    }
    $suffix = $crop_suffix ? $suffix . '-' . $crop_suffix : $suffix;
    $suffix = $retina ? $suffix . '@2x' : $suffix;

    // Define custom intermediate_size based on suffix
    $int_size = $int_size ? $int_size : 'nova_' . $suffix;

    // If current image size is smaller or equal to target size return full image
    if (empty($cropped_dims) || $dst_w > $src[1] || $dst_h > $src[2] || ($dst_w == $src[1] && $dst_h == $src[2])) {
        if ($retina) {
            return 'Retina image not available.';
        }
        return array(
            'url' => $src[0],
            'width' => $src[1],
            'height' => $src[2],
        );
    }

    // Retina dimensions validation
    if ($retina && ($dst_w !== $width || $dst_h !== $height)) {
        return array(
            'url' => $src[0],
            'width' => $src[1],
            'height' => $src[2],
        );
    }

    // Generate cropped path
    $cropped_path = $path_no_ext . '-' . $suffix . $extension;

    // Return cached image
    if (file_exists($cropped_path)) {
        $new_path = str_replace(basename($src[0]), basename($cropped_path), $src[0]);
        if (!$retina && $retina_support) {
            $retina_dims = array(
                'width' => $dst_w * 2,
                'height' => $dst_h * 2,
                'crop' => $crop
            );
            $retina_src = nova_resize_thumbnail($attach_id, $retina_dims, true);
        }
        return array(
            'url' => $new_path,
            'width' => $dst_w,
            'height' => $dst_h,
            'retina' => !empty($retina_src['url']) ? $retina_src['url'] : '',
        );
    }

    // Crop image
    $editor = wp_get_image_editor($path);
    if (!is_wp_error($editor) && !is_wp_error($editor->resize($width, $height, $crop_array))) {
        $new_path = $editor->generate_filename($suffix);
        $editor = $editor->save($new_path);

        if (!is_wp_error($editor)) {
            $cropped_img = str_replace(basename($src[0]), basename($new_path), $src[0]);

            // Generate retina version
            if (!$retina && $retina_support) {
                $retina_dims = array(
                    'width' => $dst_w * 2,
                    'height' => $dst_h * 2,
                    'crop' => $crop
                );
                $retina_src = nova_resize_thumbnail($attach_id, $retina_dims, true);
            }

            // Get thumbnail meta
            $meta = wp_get_attachment_metadata($attach_id);

            // Update meta
            if (is_array($meta)) {
                $meta['sizes'] = isset($meta['sizes']) ? $meta['sizes'] : array();

                if (!array_key_exists($int_size, $meta['sizes']) || ($dst_w != $meta['sizes'][$int_size]['width'] || $dst_h != $meta['sizes'][$int_size]['height'])) {
                    $mime_type = wp_check_filetype($cropped_img);
                    $mime_type = isset($mime_type['type']) ? $mime_type['type'] : '';

                    $dst_filename = $info['filename'] . '-' . $suffix . $extension;

                    $meta['sizes'][$int_size] = array(
                        'file' => $dst_filename,
                        'width' => $dst_w,
                        'height' => $dst_h,
                        'mime-type' => $mime_type,
                        'nova-wp' => true,
                    );

                    update_post_meta($attach_id, '_wp_attachment_metadata', $meta);
                }
            }

            return array(
                'url' => $cropped_img,
                'width' => $dst_w,
                'height' => $dst_h,
                'retina' => !empty($retina_src['url']) ? $retina_src['url'] : '',
                'intermediate_size' => $int_size,
            );
        }
    }

    // Couldn't dynamically create image so return original
    return array(
        'url' => $src[0],
        'width' => $src[1],
        'height' => $src[2],
    );
}

function nova_resize_image_attachments($attach_id, $size = '', $retina = false) {
    // Resize the thumbnail
    $attachment = wp_get_attachment_image_src($attach_id, $size);
    if (!$attachment) {
        return '';
    }

    $imagePosition = get_post_meta($attach_id, 'image_position', true) ?: 'center-top';
    return nova_resize_thumbnail($attach_id, array('width' => $attachment[1], 'height' => $attachment[is_single() ? 2 : 1], 'crop' => $imagePosition), $retina);
}
