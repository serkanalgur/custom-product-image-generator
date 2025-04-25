<?php

/**
 * Plugin Name: Custom Product Image Generator
 * Description: Extended controls over WooCommerce product images.
 * Version:     1.0.0
 * Text Domain: cpig
 * Author:          serkanalgur
 * Author URI:      https://serkanalgur.com.tr
 */

if (! defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', 'cpig_add_admin_menu');
function cpig_add_admin_menu()
{
    add_submenu_page(
        'woocommerce',
        __('Custom Product Image Generator', 'cpig'),
        __('Custom Product Image Generator', 'cpig'),
        'manage_woocommerce',
        'cpig-image-overlay',
        'cpig_render_admin_page'
    );
}

add_action('admin_enqueue_scripts', 'cpig_enqueue_assets');
function cpig_enqueue_assets($hook)
{
    if ('woocommerce_page_cpig-image-overlay' !== $hook) {
        return;
    }

    // WP media & Select2
    wp_enqueue_media();
    wp_dequeue_style('select2');
    wp_deregister_style('select2');

    wp_dequeue_script('select2');
    wp_deregister_script('select2');

    // Enqueue the Select2 styles and scripts from the CDN
    wp_enqueue_style("select2", "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.min.css");
    wp_enqueue_script("select2", "https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.min.js", array('jquery'), null, true);

    // Google Fonts stylesheet
    wp_enqueue_style(
        'cpig-google-fonts',
        'https://fonts.googleapis.com/css2?&family=Roboto:wght@400;700&family=Montserrat:wght@400;700&family=Open+Sans:wght@400;700&family=Noto+Sans&family=Inter&family=Oswald&display=swap',
        [],
        null
    );

    // WebFontLoader for Fabric.js
    wp_enqueue_script(
        'webfontloader',
        'https://cdnjs.cloudflare.com/ajax/libs/webfont/1.6.28/webfontloader.js',
        [],
        '1.6.28',
        true
    );

    // Fabric.js CDN
    wp_enqueue_script(
        'fabric-js',
        'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/460/fabric.min.js',
        [],
        '4.6.0',
        true
    );

    // Notify.js CDN
    wp_enqueue_script(
        'notify-js',
        'https://cdnjs.cloudflare.com/ajax/libs/notify/0.4.2/notify.min.js',
        ['jquery'],
        '0.4.2',
        true
    );

    // Plugin JS & CSS
    wp_enqueue_script(
        'cpig-admin-js',
        plugin_dir_url(__FILE__) . 'assets/js/cpig.js',
        ['jquery', 'select2', 'fabric-js', 'notify-js', 'webfontloader'],
        '1.0.0',
        true
    );
    wp_enqueue_style(
        'cpig-admin-css',
        plugin_dir_url(__FILE__) . 'assets/css/cpig.css',
        [],
        '1.0.0'
    );

    wp_localize_script('cpig-admin-js', 'cpig_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('cpig_nonce')
    ]);
}

function cpig_render_admin_page()
{
?>
<div class="wrap">
    <h1><?php esc_html_e('Custom Product Image Generator', 'cpig'); ?> <small>by Serkan Algur</small></h1>
    <hr>
    <div class="cpig-admin-container">

        <div class="cpig-sidebar">
            <button id="cpig-select-image"
                class="button button-primary"><?php esc_html_e('Select Base Image', 'cpig'); ?></button>
            <button id="cpig-add-text" class="button"><?php esc_html_e('Add Text Field', 'cpig'); ?></button>

            <div id="cpig-text-list" class="cpig-text-list"></div>

            <div class="cpig-logo-item">
                <label><?php esc_html_e('Upload Logo 1:', 'cpig'); ?></label>
                <button id="cpig-add-logo1" class="button"><?php esc_html_e('Add Logo 1', 'cpig'); ?></button>
            </div>

            <div class="cpig-logo-item">
                <label><?php esc_html_e('Upload Logo 2:', 'cpig'); ?></label>
                <button id="cpig-add-logo2" class="button"><?php esc_html_e('Add Logo 2', 'cpig'); ?></button>
            </div>


            <p>
                <label><?php esc_html_e('Choose Product:', 'cpig'); ?><br>
                    <select id="cpig-product-select" style="width:100%;"></select>
                </label>
            </p>



            <button id="cpig-generate" class="button button-primary"><span
                    class="dashicons dashicons-update mika dnon"></span>
                <?php esc_html_e('Generate & Save', 'cpig'); ?></button>
        </div>

        <div class="cpig-canvas-wrapper">
            <canvas id="cpig-canvas"></canvas>
            <div class="cpig-ruler">
                <div class="cpig-ruler-h"></div>
                <div class="cpig-ruler-v"></div>
            </div>
        </div>

    </div>
</div>
<?php
}

add_action('wp_ajax_cpig_search_products', 'cpig_search_products');
function cpig_search_products()
{
    check_ajax_referer('cpig_nonce', 'nonce');
    $term  = sanitize_text_field($_GET['q'] ?? '');
    $paged = absint($_GET['page'] ?? 1);

    $args = ['post_type' => 'product', 'posts_per_page' => 50, 'paged' => $paged];
    if ($term) {
        $args['s'] = $term;
    }
    $query = new WP_Query($args);

    $results = [];
    foreach ($query->posts as $post) {
        $results[] = ['id' => $post->ID, 'text' => $post->post_title];
    }
    wp_send_json(['results' => $results, 'pagination' => ['more' => $query->max_num_pages > $paged]]);
}

add_action('wp_ajax_cpig_save_image', 'cpig_save_image');
function cpig_save_image()
{
    check_ajax_referer('cpig_nonce', 'nonce');
    if (empty($_POST['image']) || empty($_POST['product_id'])) {
        wp_send_json_error('Missing data');
    }
    list(, $encoded) = explode(',', $_POST['image']);
    $decoded = base64_decode($encoded);

    $filename = 'cpig-overlay-' . time() . '.png';
    $upload = wp_upload_bits($filename, null, $decoded);
    if ($upload['error']) {
        wp_send_json_error($upload['error']);
    }

    $filetype = wp_check_filetype($filename, null);
    $attachment = [
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($filename),
        'post_status'    => 'inherit'
    ];
    $attach_id = wp_insert_attachment($attachment, $upload['file']);
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $meta = wp_generate_attachment_metadata($attach_id, $upload['file']);
    wp_update_attachment_metadata($attach_id, $meta);

    set_post_thumbnail(intval($_POST['product_id']), $attach_id);
    wp_send_json_success();
}
