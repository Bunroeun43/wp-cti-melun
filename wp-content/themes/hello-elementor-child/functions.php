<?php
/**
 * activation theme
 */
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles');
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css');
}

// action avec priorité 20. 
add_action('wp_enqueue_scripts', 'style_theme_enfant', 20);
function style_theme_enfant() {
wp_dequeue_style('hello-elementor-child-style', get_stylesheet_uri() );
wp_enqueue_style('enfant-style', get_stylesheet_uri() );
}