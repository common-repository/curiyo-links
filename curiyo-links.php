<?php
/* 
Plugin Name: Curiyo Links
Description: Enrich your content with contextually relevant content.
Version: 1.3.1
Author: Curiyo
Author URI: http://www.curiyo.com
License: GPLv2 or later
*/

// Initialize Settings
require_once(sprintf("%s/settings.php", dirname(__FILE__)));
$Curiyo_Settings = new Curiyo_Settings();

register_activation_hook( __FILE__, 'my_plugin_install_function');

//create new content type curiyo_page
add_action( 'init', 'create_post_type' );

function my_plugin_install_function()
  {
   //post status and options
    $post = array(
          'comment_status' => 'closed',
          'ping_status' =>  'closed' ,
          'post_author' => 1,
          'post_date' => date('Y-m-d H:i:s'),
          'post_name' => 'curiyo-search-page',
          'post_status' => 'publish' ,
          'post_title' => 'Curiyo Search',
          'post_type' => 'curiyo_page',
    );  
    //insert page and save the id
    $newvalue = wp_insert_post( $post, false );
	wp_publish_post($newvalue);
    //save the id in the database
    update_option( 'curiyo_search_page_id', $newvalue );
    //publish
//    $cs = new Curiyo_Settings();
//    $cs->curiyo_publish_site_contents();
}
  
function create_post_type() {
	register_post_type( 'curiyo_page',
		array(
			'labels' => array(
				'name' => __( 'CuriyoSearch' ),
				'singular_name' => __( 'CuriyoSearch' )
			),
		'public' => true,
		'has_archive' => false,
        'rewrite' => array('slug' => 'csearch'),
		'exclude_from_search' => true,
		'publicly_queryable' => true,
		'show_ui'            => false,
		'show_in_menu'       => false,
		'query_var'          => true,
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'menu_position'      => null,
		'show_in_nav_menus'  => false,
		)
	);
	flush_rewrite_rules();
}


//Template fallback
add_action("template_redirect", 'my_theme_redirect');

function my_theme_redirect() {
    global $wp;
    $plugindir = dirname( __FILE__ );

    //New Content Type CuriyoSearch
    if ($wp->query_vars["post_type"] == 'curiyo_page') {
        $templatefilename = 'curiyo-search-template.php';
        $return_template = $plugindir . '/themefiles/' . $templatefilename;
        do_theme_redirect($return_template);
    }
}

function do_theme_redirect($url) {
    global $post, $wp_query;
    if (have_posts()) {
        include($url);
        die();
    } else {
        $wp_query->is_404 = true;
    }
}


register_deactivation_hook( __FILE__, 'my_plugin_deactivate_function');
//delete custom pages
function my_plugin_deactivate_function(){
$args = array (
    'post_type' => 'curiyo_page',
    'nopaging' => true
  );
  $query = new WP_Query ($args);
  while ($query->have_posts ()) {
    $query->the_post ();
    $id = get_the_ID ();
    wp_delete_post ($id, true);
  }
  wp_reset_postdata ();

 $post_type ='curiyo_page';
  if ( ! function_exists( 'unregister_post_type' ) ) :
	function unregister_post_type( $post_type ) {
		global $wp_post_types;
		if ( isset( $wp_post_types[ $post_type ] ) ) {
			unset( $wp_post_types[ $post_type ] );
			return true;
		}
		return false;
	}
	endif;

 }
 
 //The following functions replace standard functions
 //so that we can display multiple options on a single row
 
 
function curiyo_do_settings_sections( $page ) {
	global $wp_settings_sections, $wp_settings_fields;

	if ( ! isset( $wp_settings_sections[$page] ) )
		 return;

	foreach ( (array) $wp_settings_sections[$page] as $section ) {
		 if ( $section['title'] )
			  echo "<h3>{$section['title']}</h3>\n";

		 if ( $section['callback'] )
			  call_user_func( $section['callback'], $section );

		 if ( ! isset( $wp_settings_fields ) || !isset( $wp_settings_fields[$page] ) || !isset( $wp_settings_fields[$page][$section['id']] ) )
			  continue;
		 echo '<table class="form-table">';

		$pos = strpos($section['id'], "display");

		if ($pos === false) {
			 curiyo_do_settings_fields( $page, $section['id'] );
		} else {
			 curiyo_do_display_curiyo_settings_fields( $page, $section['id'] );
		}
		 echo '</table>';
	}
}

function curiyo_do_settings_fields($page, $section) {
	global $wp_settings_fields;

	if ( ! isset( $wp_settings_fields[$page][$section] ) )
		 return;

	
	foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
		echo '<tr>';
		 
		 if ( !empty($field['args']['label_for']) )
			  echo '<th scope="row"><label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label></th>';
		 else
			  echo '<th scope="row">' . $field['title'] . '</th>';
		 echo '<td>';
		 call_user_func($field['callback'], $field['args']);
		 echo '</td>';
		 echo '</tr>';
	}

}

function curiyo_do_display_curiyo_settings_fields($page, $section) {
	global $wp_settings_fields;

	if ( ! isset( $wp_settings_fields[$page][$section] ) )
		 return;

	echo '<tr><td>';
	
	foreach ( (array) $wp_settings_fields[$page][$section] as $field ) {
	 
		 echo '<div style="width:150px; float:left;">';
		 call_user_func($field['callback'], $field['args']);
		 if ( !empty($field['args']['label_for']) )
			  echo '<label for="' . esc_attr( $field['args']['label_for'] ) . '">' . $field['title'] . '</label>';
		 else
			  echo $field['title'];
		echo '</div>';
	}

	echo '</td></tr>';
}

?>