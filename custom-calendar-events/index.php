<?php
/*
Plugin Name: Custom calendar events
Description: Calendrier des évènements en page d'accueil, pour site LPO AuRA
Version: 1.0.0
Author: PyCroyal
Author URI: https://pycroyal.fr
*/

add_action('rest_api_init', 'register_your_routes');
function register_your_routes() {
	register_rest_route(
		'custom/v1',
		'calendarevents',
		array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => 'rest_call_get_calendar_events',
			'permission_callback' => '__return_true'
		)
	);
}

function cce_get_postmeta_single_value($val) {
    return $val[0];
}

function rest_call_get_calendar_events() {
    $posts = get_posts(
        array(
         'numberposts' => -1,
         'post_status' => 'publish',
         'post_type' => 'evenements',
        )
    );
    
    if (empty($posts)) {
        return new WP_Error( 'empty_type', 'There are no posts to display', array('status' => 404) );
    }

    $now = time();
    $controle = $now - (60*60*24*31);

    $results=array();
    foreach ($posts as $post) {
		$event = new stdClass();
        $meta = get_post_meta($post->ID,'', true);
        $meta = array_map('cce_get_postmeta_single_value', $meta);
		
		if (isset($meta['event_date'])) {
			$event->id = $post->ID;
			$event->name = $post->post_title;
			$event->start = date(DateTimeInterface::ATOM, $meta['event_date']);
			$event->end = (isset($meta['event_date_end']) && $meta['event_date_end']!='') ? date(DateTimeInterface::ATOM, $meta['event_date_end']) : date(DateTimeInterface::ATOM, $meta['event_date']);
			$event->link = get_post_permalink($post->ID);
			$event->delegation = $meta['delegation'];
			$event->commune = $meta['commune'];
			$event->complet = isset($meta['complet']) ? $meta['complet'] : false;
			$compare_date = (isset($meta['event_date_end']) && $meta['event_date_end']!='') ? $meta['event_date_end'] : $meta['event_date'];
			if ($compare_date>$controle) $results[]=$event;
		}
    }

    $response = new WP_REST_Response($results);
    $response->set_status(200);

    return $response;
}

function cce_enqueue_scripts() {
    wp_register_style( 'cce-theme-basic-css', plugins_url( 'custom-calendar-events/css/theme-basic.css' ), array(), uniqid() );
    wp_register_style( 'cce-theme-glass-css', plugins_url( 'custom-calendar-events/css/theme-glass.css' ), array(), uniqid() );
    wp_register_style( 'cce-css', plugins_url( 'custom-calendar-events/css/plugin.css' ), array('cce-theme-basic-css','cce-theme-glass-css'), uniqid() );
    wp_register_script( 'cce-bundle-js', plugins_url( 'custom-calendar-events/js/bundle.js' ), array('jquery'), uniqid(), true );
    wp_register_script( 'cce-js', plugins_url( 'custom-calendar-events/js/plugin.js' ), array('jquery','cce-bundle-js'), uniqid(), true );

    if( is_front_page() ) {
        // Load style & scripts.
        wp_enqueue_style( 'cce-theme-basic-css' );
        wp_enqueue_style( 'cce-theme-glass-css' );
        wp_enqueue_style( 'cce-css' );
        wp_enqueue_script( 'cce-js' );
        wp_enqueue_script( 'cce-bundle-js' );
    }
}
add_action( 'wp_enqueue_scripts', 'cce_enqueue_scripts' );