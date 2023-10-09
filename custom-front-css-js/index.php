<?php
/*
Plugin Name: Custom front css et js
Description: Ajout de css et js au front-end
Version: 1.0.0
Author: PyCroyal
Author URI: https://pycroyal.fr
*/

function load_custom_front_css_js_scripts() {
    wp_register_style( 'custom-front-css-js', plugins_url( 'custom-front-css-js/css/plugin.css' ) );
    wp_register_script( 'custom-front-css-js', plugins_url( 'custom-front-css-js/js/plugin.js' ), array('jquery'), uniqid(), true );
    wp_enqueue_style( 'custom-front-css-js' );
    wp_enqueue_script( 'custom-front-css-js' );
}
add_action( 'wp_enqueue_scripts', 'load_custom_front_css_js_scripts' );