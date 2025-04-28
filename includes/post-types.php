<?php

// phpcs:disable
if (!defined('ABSPATH')) {
    exit;
}
// phpcs:enable
add_action('init', function () {
    $labels = [
        'name'               => __('Templates', 'cpig'),
        'singular_name'      => __('Template', 'cpig'),
        'menu_name'          => __('Templates', 'cpig'),
    ];
    register_post_type('cpig_template', [
        'labels'             => $labels,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => false,
        'supports'           => ['title','custom-fields'],
        'menu_icon'          => 'dashicons-media-document',
    ]);
});
