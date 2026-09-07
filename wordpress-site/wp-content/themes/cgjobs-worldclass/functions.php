<?php
/** CGJobs Worldclass theme bootstrap. */
defined('ABSPATH') || exit;

add_action('after_setup_theme', function (): void {
    add_theme_support('title-tag');
    add_theme_support('html5', ['search-form','gallery','caption','style','script']);
    register_nav_menus(['primary' => 'Primary Navigation']);
});

add_action('wp_enqueue_scripts', function (): void {
    wp_enqueue_style('cgjobs-style', get_stylesheet_uri(), [], '1.0.0');
});

add_filter('document_title_separator', static fn() => '•');

add_action('wp_head', function (): void {
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
}, 1);
