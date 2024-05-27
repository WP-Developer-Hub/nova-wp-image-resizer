<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class NRT_Image_Position_Meta_Box {

    public function __construct() {
        add_action('add_meta_boxes', array($this, 'register_meta_box'));
        add_action('save_post', array($this, 'save_meta_box_data'));
        add_action('admin_enqueue_scripts', array($this, 'nrt_enqueue_thumbnail_preview_script'));
    }

    public function nrt_enqueue_thumbnail_preview_script() {
        wp_enqueue_script('nova-resize-thumbnail-preview', plugin_dir_url(__FILE__) . 'js/nova-resize-thumbnail-preview.js', array('jquery'), '1.0', true);
    }

    // Register the meta box
    public function register_meta_box() {
        // Get all post types that support thumbnails
        $post_types = get_post_types(array('supports' => 'thumbnail'));
        add_meta_box(
            'image_position_meta_box',
            __( 'Image Position', 'nrt' ),
            array( $this, 'render_meta_box' ),
            $post_types,
            'side',
            'default'
        );
    }

    // Render the meta box
    public function render_meta_box( $post ) {
        $image_position = array(
            'left-top' => 'Left Top', 'center-top' => 'Center Top', 'right-top' => 'Right Top',
            'left-center' => 'Left Center', 'center-center' => 'Center Center', 'right-center' => 'Right Center',
            'left-bottom' => 'Left Bottom', 'center-bottom' => 'Center Bottom', 'right-bottom' => 'Right Bottom'
        );

        // Retrieve the current crop location if it exists
        $current_image_position = get_post_meta($post->ID, 'image_position', true) ?: 'center-top';

        wp_nonce_field('save_image_position', 'image_position_nonce');

        // Output the meta box content
        echo '<div style="display: flex; flex-direction: column; align-items: center;">';
        echo '<div class="nrt-thumbnail" style=" width: 256px; height: 256px; margin-bottom: 10px; background-repeat: no-repeat; background-size: cover; background-image: url(\'' . esc_url( wp_get_attachment_image_url(get_post_thumbnail_id($post->ID), 'full')) . '\');"">';
        echo '</div>';
        echo '</div>';

        echo '<table align="center" style="border: 1px solid black; padding: 0px; border-collapse: collapse;">';
        $i = 0;
        foreach ( $image_position as $position => $label ) {
            $checked = ( $position === $current_image_position ) ? 'checked' : '';
            if ($i % 3 === 0) {
                echo '<tr>';
            }
            echo '<td style="border: 1px solid black; padding: 0px; margin: 0px; height: 85px; width: 85px; vertical-align: middle; text-align: center;"><label class="screen-reader-text"> ' . esc_html( $label ) . '</label><input type="radio" name="image_position" value="' . esc_attr( $position ) . '" ' . $checked . '></td>';
            $i++;
            if ($i % 3 === 0) {
                echo '</tr>';
            }
        }
        // Close the row if the last row is not completed
        if ($i % 3 !== 0) {
            echo '</tr>';
        }
        echo '</table>';
    }

    // Save the selected crop location
    public function save_meta_box_data($post_id) {
        // Check if nonce is set and valid
        if (!isset($_POST['image_position_nonce'] ) || !wp_verify_nonce( $_POST['image_position_nonce'], 'save_image_position')) {
            return;
        }

        // Check if the current user has permission to edit the post
        if (!current_user_can( 'edit_post', $post_id)) {
            return;
        }

        // Sanitize and save the crop location
        if (isset($_POST['image_position'])) {
            $crop_location = sanitize_text_field($_POST['image_position']);
            update_post_meta($post_id, 'image_position', $crop_location);
        } else {
            update_post_meta($post_id, 'image_position', 'center-top');
        }
    }
}

// Initialize the class
new NRT_Image_Position_Meta_Box();
