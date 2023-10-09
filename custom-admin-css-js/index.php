<?php
/*
Plugin Name: Custom admin css et js
Description: Ajout de css et js au back-office
Version: 1.0.0
Author: PyCroyal
Author URI: https://pycroyal.fr
*/

function register_custom_admin_css_js_scripts() {
    wp_register_style( 'custom-admin-css-js', plugins_url( 'custom-admin-css-js/css/plugin.css' ) );
    wp_register_script( 'custom-admin-css-js', plugins_url( 'custom-admin-css-js/js/plugin.js' ), array('jquery'), uniqid(), true );
}
add_action( 'admin_enqueue_scripts', 'register_custom_admin_css_js_scripts' );

function load_custom_admin_css_js_scripts( $hook ) {
    if( $hook != 'evenements_page_export-admin-page' ) {
        //return;
    }

    // Load style & scripts.
    wp_enqueue_style( 'custom-admin-css-js' );
    wp_enqueue_script( 'custom-admin-css-js' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_admin_css_js_scripts' );

add_action('admin_head', 'admin_styles');
function admin_styles() {
    echo '<style>
        .components-button.editor-post-taxonomies__hierarchical-terms-add, .components-button.editor-post-taxonomies__hierarchical-terms-submit {
            display: none !important;
        }
    </style>';
}