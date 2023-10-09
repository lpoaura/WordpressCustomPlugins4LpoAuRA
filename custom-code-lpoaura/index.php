<?php
/*
Plugin Name: Custom code LPO AuRA
Description: Custom snippets pour site LPO AuRA
Version: 1.0.0
Author: PyCroyal
Author URI: https://pycroyal.fr
*/

// function register_export_admin_page_scripts() {
//     wp_register_style( 'export-admin-page', plugins_url( 'export-admin-page/css/plugin.css' ), array(), uniqid() );
//     wp_register_script( 'export-admin-page', plugins_url( 'export-admin-page/js/plugin.js' ), array('jquery'), uniqid(), true );
// }
// add_action( 'admin_enqueue_scripts', 'register_export_admin_page_scripts' );

// function load_export_admin_page_scripts( $hook ) {
//     if( $hook != 'evenements_page_export-admin-page' ) {
//         return;
//     }

//     // Load style & scripts.
//     wp_enqueue_style( 'export-admin-page' );
//     wp_enqueue_script( 'export-admin-page' );
// }
// add_action( 'admin_enqueue_scripts', 'load_export_admin_page_scripts' );

if ( ! function_exists('write_log')) {
	function write_log ( $log )  {
	   if ( is_array( $log ) || is_object( $log ) ) {
		  error_log( print_r( $log, true ) );
	   } else {
		  error_log( $log );
	   }
	}
}

/*
Personnalisation des pages Admin colonnes filtres
*/
//Transforme des données de la bdd en infos lisibles pour les filtres event_date
function custom_filter_label( $label, $filter ) {
	//timestamp -> format date pour les champs event_date
    if ( 'meta' == $filter['type'] && 'event_date' == $filter['meta_key'] ) {
        $label = date_i18n( get_option( 'date_format' ), $label );
    }

	//A la une -> booléen
    if ( 'meta' == $filter['type'] && 'mise-avant' == $filter['meta_key'] ) {
        if ($label == 'true') $label = 'Oui';
		else $label = 'Non';
    }

	//ID evenement -> Titre
    if ( 'meta' == $filter['type'] && 'evenement' == $filter['meta_key'] ) {
		if ($label) $label = get_the_title($label);
		else $label = 'Evenement vide';
    }

    return $label;
}
add_filter( 'jet-engine/admin-filters/filter-label', 'custom_filter_label', 10, 2 );

//Supprime le filtre "date de publication" pour les custom post type indiqués
function custom_remove_date_filter( $months ) {
    global $typenow; // use this to restrict it to a particular post type
    if ( $typenow == 'evenements' ) {
        return array(); // return an empty array
    }
    return $months; // otherwise return the original for other post types
}
add_filter('months_dropdown_results', 'custom_remove_date_filter');

//Edition/Suppression colonnes
function custom_post_columns( $columns, $post_type ) {
	$columns['date'] = 'Publication';

	return $columns;
}
add_filter( 'manage_posts_columns', 'custom_post_columns', 10, 2 );


/*
Custom Jet Smart Filters -> Modification Query Date
*/
//Hook/Query afin de filter les évènements
function get_custom_query( $fields, $values ) {
	$result = array(
		'relation' => 'OR',
	);

	foreach ( $fields as $field ) {
		$result[] = array(
			'relation' => 'AND',
				array(
					'key'     => $field,
					'value'   => $values,
					'type'    => 'NUMERIC',
					'compare' => 'BETWEEN',
				)
		);
	}

	/*
	$niveau2 = array(
		'relation' => 'AND',
		array(
			'key'     => 'event_date',
			'value'   => $values[0],
			'type'    => 'NUMERIC',
			'compare' => '<=',
		),
		array(
			'key'     => 'event_date_end',
			'value'   => $values[1],
			'type'    => 'NUMERIC',
			'compare' => '>=',
		)
	);

	$result[] = $niveau2;*/

	return $result;
}

function apply_dates_filter( $query ) {
	//Masque de requête à indiquer au début du champs "Query Variable" du Filter
	$base_mask = 'evenement_inside::';
	$separator = ';';


	if ( empty( $query['meta_query'] ) ) {
		return $query;
	}

	foreach ( $query['meta_query'] as $index => $meta_query ) {

		//On va modifier uniquement la requête ayant pour masque : evenement_inside
		if ( isset($meta_query['key']) && false !== strpos( $meta_query['key'], $base_mask ) ) {

			$data = explode( '::', $meta_query['key'] );

			//On récupère les champs dates debut et fin de l'evenement : event_date et event_date_fin
			$fields = explode($separator, str_replace($base_mask, "", $meta_query['key']));

			//unset( $query['meta_query'][ $index ] );
			$query['meta_query'][$index] = get_custom_query( $fields, $meta_query['value'] );

		}
	}


	return $query;

}
add_filter( 'jet-smart-filters/query/final-query', 'apply_dates_filter' );

/*
Gestion evenements/inscriptions ajout/edition/suppression
*/
function remove_custom_meta_form() {
    remove_meta_box( 'postcustom', 'evenements', 'normal' );
}
add_action( 'admin_menu' , 'remove_custom_meta_form' );

add_action( 'updated_post_meta', 'custom_after_post_meta', 10, 4 );
function custom_after_post_meta( $meta_id, $post_ID, $meta_key, $meta_value )
{
	if (get_post_type($post_ID)=='evenements' && get_post_meta($post_ID,'reservation',true)=='true') {
		update_dispo_evenement($post_ID);
	}

	if (get_post_type($post_ID)=='inscriptions' && get_post_meta($post_ID,'nombre',true)) {//si on modifie le nbre de personnes d'une inscription on recalcule dispo evenement
		$event_ID = get_post_meta($post_ID,'evenement',true);
		update_dispo_evenement($event_ID);
	}

	if (get_post_type($post_ID)=='inscriptions' && get_post_meta($post_ID,'evenement',true)) {//si evenement renseigné pour l'inscription
		$event_ID = get_post_meta($post_ID,'evenement',true);
		$delegation = get_post_meta($event_ID,'delegation',true);
		update_post_meta($post_ID,'delegation',$delegation);
	} else if (get_post_type($post_ID)=='inscriptions' && !get_post_meta($post_ID,'evenement',true)) {
		update_post_meta($post_ID,'delegation','');
	}
}

//Mettre à jour la dispo d'un evenement quand une incription est mise à la corbeille et définitivement supprimée
add_action( 'trashed_post', 'custom_trashed_post_function', 99, 2 );
function custom_trashed_post_function( $post_ID ) {

    if ( 'inscriptions' !== get_post_type($post_ID) ) {
        return;
    }

	$event_ID = get_post_meta($post_ID,'evenement',true);
	update_dispo_evenement($event_ID);
}

add_action( 'jet-form-builder/custom-action/update-evenement', function( $request, $action_handler ) {

	$post_ID = ! empty( $request['inserted_post_id'] ) ? $request['inserted_post_id'] : false;

	if ( ! $post_ID ) {
		return;
	}

	$event_ID = $request['post_id'];
	$delegation = get_post_meta($event_ID,'delegation',true);
	update_post_meta($post_ID,'delegation',$delegation);
	update_dispo_evenement($event_ID);
}, 10, 2 );

function update_dispo_evenement ($evenementID) {
	$formulaire = get_post_meta($evenementID,'formulaire',true);
	$quota = get_post_meta($evenementID,'quota',true);
	$participants = get_post_meta($evenementID,'nombre-participants',true);
    $date_event = get_post_meta($evenementID,'event_date',true);
    $date_end = get_post_meta($evenementID,'event_date_end',true);
    $date_limite = get_post_meta($evenementID,'event_date_limite',true);

	if ($formulaire=='true' && $date_limite=='') {
    	update_post_meta($evenementID,'event_date_limite',$date_event-(24*60*60));
    }
    
    if ($formulaire=='false') {
    	update_post_meta($evenementID,'event_date_limite','');
    }
    
    if ($date_end =='' && $date_event) {
    	//update_post_meta($evenementID,'event_date_end',$date_event);
    }

	if ($formulaire=='true' && $quota) {//Uniquement pour les evenements avec inscription via formulaire et quota
		$inscriptions = get_posts(array(
			'numberposts'	=> -1,
			'post_type'		=> 'inscriptions',
			'fields' => 'ids',
			'status' => 'publish',
			'meta_query'	=> array(
				'relation'		=> 'AND',
				array(
					'key'	 	=> 'evenement',
					'value'	  	=> $evenementID,
					'compare' 	=> '=',
				),
			),
		));

		$nb_personnes = 0;
		foreach ($inscriptions as $inscriptionID) {
			$nb_personnes = $nb_personnes + get_post_meta($inscriptionID,'nombre',true);
		}

		//wp_die($quota-$nb_personnes);

		update_post_meta($evenementID,'dispo',$quota-$nb_personnes);
		update_post_meta($evenementID,'nombre-participants',$nb_personnes);

		if ($quota-$nb_personnes<=0) {
			update_post_meta($evenementID,'complet',1);
		} else {
			update_post_meta($evenementID,'complet',0);
		}
	} else if ($quota) {
		//Pour les evenements avec inscription obligatoire (avec quota) mais pas de formulaire on initialise nombre de participants = quota
		if ($participants=='') {
			update_post_meta($evenementID,'nombre-participants',$quota);
		}
	} else {
		//Si pas de quota on limite le champs select nb personnes à 30
		update_post_meta($evenementID,'dispo',30);
		//update_post_meta($evenementID,'complet',0);
	}
}

/*
JetEngine add customs Callbacks
*/
add_filter( 'jet-engine/listings/allowed-callbacks', 'jet_custom_callbacks' );

//Add callback to list.
function jet_custom_callbacks( $callbacks ) {

	$callbacks['jet_post_edit_link'] = 'Admin Edit Post Link';
	$callbacks['jet_projetid_to_delegation'] = 'Projet : page de la délégation';
	$callbacks['jet_projetid_to_delegation_action'] = 'Projet : page action de la délégation';

	return $callbacks;
}

//Callback function for post edit link.
function jet_post_edit_link( $id ) {
	return '<a href="'.get_edit_post_link( $id ).'" target="_blank" rel="noopener">Modifier</a>';
}

//Callback function pour transformer le choix d'une délégation vers page de la delegation
function jet_projetid_to_delegation( $id ) {
	$dt = get_post_meta($id,'delegation',true);
	$dt_sanitized = sanitize_title($dt);
	return '<a href="'.get_site_url().'/lpo-locales/'.$dt_sanitized.'">'.$dt.'</a>';
}

//Callback function pour transformer le choix d'une délégation et action en lien vers page actions de la delegation
function jet_projetid_to_delegation_action( $id ) {
	$action = get_post_meta($id,'projet-action',true);
	$action_sanitized = sanitize_title($action);
	$dt = get_post_meta($id,'delegation',true);
	$dt_sanitized = sanitize_title($dt);

	if ($dt_sanitized == 'lpo-aura') {
		return '<a href="'.get_site_url().'/qui-sommes-nous/nos-missions/'.$action_sanitized.'">'.$action.'</a>';
	} else {
		return '<a href="'.get_site_url().'/actions/'.$dt_sanitized.'/'.$action_sanitized.'">'.$action.'</a>';
	}
}

/*
JetEngine custom : ajout option 'Override posts_num' pour Widget Listing Grid
*/
add_filter( 'jet-engine/listing/grid/query/query', function( $query, $settings ) {
	if ( isset( $settings[ 'override_posts_num' ] ) && 'yes' == $settings[ 'override_posts_num' ] ) {
		$query = array_slice( $query, 0, $settings['posts_num'] );
	}

	return $query;
}, 100, 2 );

add_action( 'elementor/element/jet-listing-grid/section_general/after_section_start', 'listing_grid_add_override_query_post_num', 10, 2 );
function listing_grid_add_override_query_post_num( $widget = null, $args = array() ) {
	$widget->add_control(
		'override_posts_num',
		array(
			'label' => __( 'Override posts_num', 'jet-engine' ),
			'type' => \Elementor\Controls_Manager::SWITCHER,
			'description' => '',
			'label_on' => __( 'Yes', 'jet-engine' ),
			'label_off' => __( 'No', 'jet-engine' ),
			'return_value' => 'yes',
			'default' => '',
			)
	);
}

/*
Ajout Classes pour les items de menu
*/
add_action( 'nav_menu_css_class', 'menu_item_classes', 10, 3 );
function menu_item_classes( $classes, $item, $args ) {
     $classes[] = 'post-'.$item->object_id ;
    return $classes;
}


/*
CRON pour passer en brouillon les offres benevolat/recrutement expirées
*/
if (!wp_next_scheduled('expire_events')){
	wp_schedule_event(time(), 'daily', 'expire_events'); // this can be hourly, twicedaily, or daily
}
add_action('expire_events', 'expire_events_function');
function expire_events_function() {
	$today = time();
	$args = array(
		'post_type' => array('post'), // post types you want to check
		'posts_per_page' => -1
	);

	$posts = get_posts($args);

	foreach($posts as $p){
		$type = get_post_meta($p->ID,'type',true);
		$fin_validite = get_post_meta($p->ID,'date-validite',true);

		if ($type == 'Offres de recrutement' || $type == 'Offres de bénévolat') {
			if($fin_validite && $fin_validite < $today){
				$postdata = array(
					'ID' => $p->ID,
					'post_status' => 'draft'
				);
				wp_update_post($postdata);
			}
		}
	}
}

/*
Controle et envoi des email récapitulatifs aux animateurs une fois la date de cloture des inscriptions atteinte
*/
function check_send_email_animateurs() {
	$today = strtotime("today 00:00");
	$yesterday = strtotime('-1 day', $today);

	$args = array(
		'post_type' => array('evenements'), // post types you want to check
		'posts_per_page' => -1
	);

	$posts = get_posts($args);

	foreach($posts as $p){
		$reservation = get_post_meta($p->ID,'reservation',true);
		$formulaire = get_post_meta($p->ID,'formulaire',true);
		$date_limite = get_post_meta($p->ID,'event_date_limite',true);
		$date_event = get_post_meta($p->ID,'event_date',true);
		$email_animateur = get_post_meta($p->ID,'email',true);

		$jour = date('d/m/Y',$date_event);

		$body = '<h2>'.$p->post_title.'</h2>';
		$body .= '<h3>Récapitulatif des inscriptions du '.$jour.'</h3>';
		
		if ($reservation && $formulaire && $date_limite == $today) {
			$inscriptions = get_posts(array(
				'numberposts'	=> -1,
				'post_type'		=> 'inscriptions',
				'fields' => 'ids',
				'status' => 'publish',
				'meta_query'	=> array(
					'relation'		=> 'AND',
					array(
						'key'	 	=> 'evenement',
						'value'	  	=> $p->ID,
						'compare' 	=> '=',
					),
				),
			));
	
			$nb_personnes = 0;
			foreach ($inscriptions as $inscriptionID) {
				$nb_personnes = $nb_personnes + get_post_meta($inscriptionID,'nombre',true);
			}

			$body .= '<h4>'.$nb_personnes.' inscrits</h4>';

			foreach ($inscriptions as $inscriptionID) {
				$prenom = get_post_meta($inscriptionID,'prenom',true);
				$nom = get_post_meta($inscriptionID,'nom',true);
				$telephone = get_post_meta($inscriptionID,'telephone',true);
				$email = get_post_meta($inscriptionID,'email',true);
				$nombre = get_post_meta($inscriptionID,'nombre',true);

				$body .= $prenom.' '.$nom.'<br>';
				$body .= $telephone .'<br>';
				$body .= $email .'<br>';
				$body .= $nombre .' personne(s)<br><br>';
			}


			$to = $email_animateur;
            $subject = 'Inscriptions du '.$jour.' - '.$p->post_title;
            
            $headers = array();
            $headers[] = 'MIME-Version: 1.0';
            $headers[] = 'Content-Type: text/html; charset=UTF-8';
            $headers[] = 'From: Site LPO AuRA <webadmin.aura@lpo.fr>';

            wp_mail( $to, $subject, $body, $headers );
            //wp_mail( 'croyal.py@gmail.com', $subject, $body, $headers );
		}
	}
}

//Fonction pour modification en masse sur evenements
function modifie_events() {
	$args = array(
		'post_type' => array('evenements'), // post types you want to check
		'posts_per_page' => -1
	);

	$posts = get_posts($args);

	foreach($posts as $p){
		$date_event = get_post_meta($p->ID,'event_date',true);
		$date_end = get_post_meta($p->ID,'event_date_end',true);

		if ($date_end=='' && $date_event) {
			//update_post_meta($p->ID,'event_date_end',$date_event);
		}
	}
}

//Filtre pour autoriser code HTML dans textarea -> par exemple champs iframe visite des espaces naturels
add_filter( 'wp_kses_allowed_html', function ( $tags, $context ) {
    if ( 'post' === $context ) {
        $tags['iframe'] = array(
            'src' => true,
            'width' => true,
            'height' => true,
            'width' => true, 
            'frameborder' => true,
            'allowtransparency' => true,
            'allow' => true,
        );
    }
    return $tags;
},10,2);