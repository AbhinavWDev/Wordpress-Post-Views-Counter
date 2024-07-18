<?php
/*
Plugin Name: Post Views Counter
Description: A simple plugin to count and display the number of views for each post.
Version: 1.2
Author: Abhinav Saxena
*/

// Hook into WordPress to initialize our plugin
add_action('wp_head', 'pvc_count_post_views');
add_filter('the_content', 'pvc_display_post_views');
add_action('admin_menu', 'pvc_admin_menu');
add_action('admin_init', 'pvc_register_settings');

// Function to count post views
function pvc_count_post_views() {
    if (is_single()) {
        global $post;
        $post_id = $post->ID;
        $views = get_post_meta($post_id, 'pvc_views', true);
        if ($views == '') {
            $views = 0;
            delete_post_meta($post_id, 'pvc_views');
            add_post_meta($post_id, 'pvc_views', '0');
        } else {
            $views++;
            update_post_meta($post_id, 'pvc_views', $views);
        }
    }
}

// Function to display post views
function pvc_display_post_views($content) {
    if (is_single()) {
        global $post;
        $post_id = $post->ID;
        $views = get_post_meta($post_id, 'pvc_views', true);
        if ($views == '') {
            $views = 0;
        }

        // Get styling options
        $font_size = get_option('pvc_view_font_size', '14px');
        $color = get_option('pvc_view_color', '#000000');
        $background_color = get_option('pvc_view_background_color', '#ffffff');

        $style = "font-size: $font_size; color: $color; background-color: $background_color; padding: 5px;";

        $content .= '<p style="' . esc_attr($style) . '">This post has been viewed ' . $views . ' times.</p>';
    }
    return $content;
}

// Add admin menu
function pvc_admin_menu() {
    add_menu_page(
        'Post Views Counter',
        'Post Views Counter',
        'manage_options',
        'pvc_admin_page',
        'pvc_admin_page_callback',
        'dashicons-visibility',
        100
    );
    add_submenu_page(
        'pvc_admin_page',
        'Settings',
        'Settings',
        'manage_options',
        'pvc_admin_settings',
        'pvc_admin_settings_callback'
    );
}

// Admin page callback
function pvc_admin_page_callback() {
    echo '<div class="wrap">';
    echo '<h1>Post Views Counter</h1>';
    echo '<table class="widefat fixed">';
    echo '<thead><tr><th>Post Title</th><th>Views</th></tr></thead>';
    echo '<tbody>';
    
    $args = array('post_type' => 'post', 'posts_per_page' => -1);
    $posts = get_posts($args);
    foreach ($posts as $post) {
        $views = get_post_meta($post->ID, 'pvc_views', true);
        echo '<tr><td>' . esc_html($post->post_title) . '</td><td>' . esc_html($views) . '</td></tr>';
    }
    
    echo '</tbody></table>';
    echo '</div>';
}

// Admin settings callback
function pvc_admin_settings_callback() {
    echo '<div class="wrap">';
    echo '<h1>Post Views Counter Settings</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('pvc_settings_group');
    do_settings_sections('pvc_admin_settings');
    submit_button();
    echo '</form>';
    echo '<h2>Preview</h2>';
    echo '<p id="pvc-preview" style="' . esc_attr(pvc_generate_style()) . '">This post has been viewed 0 times.</p>';
    echo '</div>';
}

// Register settings
function pvc_register_settings() {
    register_setting('pvc_settings_group', 'pvc_view_font_size');
    register_setting('pvc_settings_group', 'pvc_view_color');
    register_setting('pvc_settings_group', 'pvc_view_background_color');

    add_settings_section('pvc_settings_section', 'Styling Options', null, 'pvc_admin_settings');

    add_settings_field('pvc_view_font_size', 'Font Size', 'pvc_view_font_size_callback', 'pvc_admin_settings', 'pvc_settings_section');
    add_settings_field('pvc_view_color', 'Font Color', 'pvc_view_color_callback', 'pvc_admin_settings', 'pvc_settings_section');
    add_settings_field('pvc_view_background_color', 'Background Color', 'pvc_view_background_color_callback', 'pvc_admin_settings', 'pvc_settings_section');
}

// Styling options callbacks
function pvc_view_font_size_callback() {
    $font_size = get_option('pvc_view_font_size', '14px');
    echo '<input type="text" name="pvc_view_font_size" value="' . esc_attr($font_size) . '" class="regular-text">';
}

function pvc_view_color_callback() {
    $color = get_option('pvc_view_color', '#000000');
    echo '<input type="text" name="pvc_view_color" value="' . esc_attr($color) . '" class="regular-text">';
}

function pvc_view_background_color_callback() {
    $background_color = get_option('pvc_view_background_color', '#ffffff');
    echo '<input type="text" name="pvc_view_background_color" value="' . esc_attr($background_color) . '" class="regular-text">';
}

// Generate inline style for preview
function pvc_generate_style() {
    $font_size = get_option('pvc_view_font_size', '14px');
    $color = get_option('pvc_view_color', '#000000');
    $background_color = get_option('pvc_view_background_color', '#ffffff');

    return "font-size: $font_size; color: $color; background-color: $background_color; padding: 5px;";
}
?>
