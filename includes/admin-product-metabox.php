<?php

/**
 * Custom Product Image Generator Product MEtabox
 *
 * @package Custom_Product_Image_Generator
 */

// phpcs:disable
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:enable


// Add metabox on product
add_action('add_meta_boxes', 'add_metabox_to_product');
add_action('save_post_product', 'save_post_fields_of_cpig', 10, 1);
add_action('save_post_product', 'generate_image_from_template_maybe', 10, 3);
add_action('admin_enqueue_scripts', 'add_meatbox_script');
/**
 * Callback function for the metabox
 *
 * @param WP_Post $post The post object.
 */

function add_metabox_to_product()
{
    add_meta_box('cpig_template_meta', __('Image Template', 'cpig'), 'cpig_template_metabox_cb', 'product', 'side');
}

function cpig_template_metabox_cb($post)
{
    wp_nonce_field('cpig_template_meta', 'cpig_template_nonce');
    $selected   = get_post_meta($post->ID, '_cpig_template', true);
    $regenerate = get_post_meta($post->ID, '_cpig_regenerate', true);
    $templates  = get_posts(['post_type' => 'cpig_template','numberposts' => -1]);
    echo '<p><label><input type="checkbox" id="cpig-regenerate" name="cpig_regenerate" '.checked($regenerate, 1, false).'> '.esc_html__('Regenerate on update', 'cpig').'</label></p>';
    echo '<p><label>'.esc_html__('Select Template', 'cpig').'<br><select id="cpig-template-select" name="cpig_selected_template">';
    echo '<option value="">'.esc_html__('— None —', 'cpig').'</option>';
    foreach ($templates as $t) {
        printf('<option value="%d" %s>%s</option>', $t->ID, selected($selected, $t->ID, false), esc_html($t->post_title));
    }
    echo '</select></label></p>';
    // Generate Now button
    echo '<p><button type="button" id="cpig-generate-now" class="button">'.esc_html__('Generate Image Now', 'cpig').'</button></p>';
}

function save_post_fields_of_cpig($post_id)
{
    if (!isset($_POST['cpig_template_nonce']) || !wp_verify_nonce($_POST['cpig_template_nonce'], 'cpig_template_meta')) {
        return;
    }
    update_post_meta($post_id, '_cpig_template', sanitize_text_field($_POST['cpig_selected_template']));
    update_post_meta($post_id, '_cpig_regenerate', isset($_POST['cpig_regenerate']) ? 1 : 0);
}

function generate_image_from_template_maybe($post_id, $post, $update)
{
    $template_id = get_post_meta($post_id, '_cpig_template', true);
    if (!$template_id) {
        return;
    }
    $regenerate = get_post_meta($post_id, '_cpig_regenerate', true);
    if (!$update || $regenerate) {
        CPIG_Template_Engine::generate_from_template($template_id, $post_id);
    }
}

function add_meatbox_script($hook)
{
    if (in_array($hook, ['post.php','post-new.php'])) {
        wp_enqueue_script(
            'notify-js',
            'https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js',
            ['jquery'],
            '0.4.2',
            true
        );
        wp_enqueue_script('cpig-product-metabox', plugin_dir_url(__FILE__) . '../assets/js/cpig-product-metabox.js', ['jquery','notify-js'], PLGVER, true);
        wp_localize_script('cpig-product-metabox', 'cpig_ajax', ['ajax_url' => admin_url('admin-ajax.php'),'nonce' => wp_create_nonce('cpig_nonce')]);
    }
}
