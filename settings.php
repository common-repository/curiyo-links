<?php
/*
 * settings.php
 * Gil Reich	May 19, 2013
 * Kivun Hadash Ltd.
 * Last update: August 25, 2014
 */

define('CURIYO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CURIYO_CALL_SERVER', true); //used like a compiler directive. false: to echo locally (for local debugging). true: call server.
define('CURIYO_DEFAULT_UPDATE_ON_PUBLISH', 1);
define('CURIYO_DEFAULT_ADD_JAVASCRIPT', 1);
define('CURIYO_DEFAULT_ADD_DOWNLOAD_LINK', 0);
define('CURIYO_DEFAULT_PUBLISH_TO_CURIYO', 1);
define('CURIYO_DEFAULT_LOGO', '');
define('CURIYO_DEFAULT_MAX_LINKS', 20);
define('CURIYO_DEFAULT_LINK_COLOR', '#800080');
define('CURIYO_DEFAULT_SINGLE_POST_ON', 1);
define('CURIYO_DEFAULT_PAGES_ON', 1);
define('CURIYO_DEFAULT_HOME_PAGE_ON', 1);
define('CURIYO_DEFAULT_TAGS_ON', 0);
define('CURIYO_DEFAULT_CATEGORIES_ON', 0);
define('CURIYO_DEFAULT_AUTHOR_ARCHIVES_ON', 0);
define('CURIYO_DEFAULT_SEARCH_RESULTS_ON', 0);
define('CURIYO_DEFAULT_ARCHIVES_ON', 0);

define('CURIYO_EMAIL_EXPLANATION',  "We won't spam you or share your email. This is only to contact you regarding your implementation of CuriyoLinks&#8482;.");
define('CURIYO_LOGO_EXPLANATION', "URL of logo that will be displayed in the Curiyo window.");
define('CURIYO_MAX_LINKS_EXPLANATION', "The maximum number of CuriyoLinks&#8482; to display on each page.");


define('CURIYO_TAGS_TO_DISPLAY', 50);
define('CURIYO_POSTS_PER_TAG', 8);


function log_me($message)
{
    if (WP_DEBUG === true) {
        if (is_array($message) || is_object($message)) {
            error_log(print_r($message, true));
        } else {
            error_log($message);
        }
    }
}

function curiyo_escape_json($string_to_escape)
{
    /*
     *	esc_js function escapes single quotes too, and then the json doesn't validate at jsonlint
     *	just escapes slash and double quote
     *	surely there's a better way to do this
     */
    $escaped_string = str_replace('\\', '\\\\', $string_to_escape);
    $escaped_string = str_replace('"', '\"', $string_to_escape);
    //  $escaped_string = addcslashes( $string_to_escape, '\\\"' );
    return $escaped_string;
}



if (!class_exists('Curiyo_Settings')) {
    class Curiyo_Settings
    {
        /**
         * Construct the plugin object
         */
        public function __construct()
        {
            // register actions
            add_action('admin_init', array(
                &$this,
                'admin_init'
            ));
            add_action('admin_menu', array(
                &$this,
                'add_menu'
            ));
            
            add_action('wp_footer', array(
                &$this,
                'curiyo_write_to_footer_conditionally'
            ), 100);
			
			add_action( 'admin_enqueue_scripts', array(
                &$this,
                'curiyo_add_color_picker'
            ));
				
        } // END public function __construct
        
        /**
         * Hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // register your plugin's settings
            register_setting('curiyo-group', 'curiyo_max_links');
            register_setting('curiyo-group', 'curiyo_link_color');
			
            
            register_setting('curiyo-group', 'curiyo_site_email');
            register_setting('curiyo-group', 'curiyo_site_logo');
			
			register_setting('curiyo-group', 'curiyo_single_post_on');
			register_setting('curiyo-group', 'curiyo_pages_on');
			register_setting('curiyo-group', 'curiyo_home_page_on');
			register_setting('curiyo-group', 'curiyo_tags_on');
			register_setting('curiyo-group', 'curiyo_categories_on');
			register_setting('curiyo-group', 'curiyo_author_archives_on');
			register_setting('curiyo-group', 'curiyo_search_results_on');
			register_setting('curiyo-group', 'curiyo_archives_on');
            
            
            // add your settings sections
            
            add_settings_section('curiyo-section', 'Required Settings', array(
                &$this,
                'settings_section_curiyo'
            ), 'curiyo');
            
            add_settings_section('curiyo-section-optional', 'Optional Settings', array(
                &$this,
                'settings_section_curiyo'
            ), 'curiyo');
            
            add_settings_section('curiyo-display-on', 'Display CuriyoLinks&#8482; on the Following Pages:', array(
                &$this,
                'settings_section_curiyo'
            ), 'curiyo');

            // add your setting's fields
            add_settings_field('curiyo-setting_max_links', 'Maximum CuriyoLinks&#8482; to Display', array(
                &$this,
                'settings_field_input_text'
            ), 'curiyo', 'curiyo-section-optional', array(
                'field' => 'curiyo_max_links',
                'default' => CURIYO_DEFAULT_MAX_LINKS,
				'explanation' => CURIYO_MAX_LINKS_EXPLANATION,
				'width' => '50px'
            ));
            
            add_settings_field('curiyo-setting_link_color', 'SmartLink&#8482; Color', array(
                &$this,
                'settings_field_input_color'
            ), 'curiyo', 'curiyo-section-optional', array(
                'field' => 'curiyo_link_color',
                'default' => CURIYO_DEFAULT_LINK_COLOR
            ));

            add_settings_field('curiyo-setting_site_logo', "Site Logo", array(
                &$this,
                'settings_field_input_text'
            ), 'curiyo', 'curiyo-section', array(
                'field' => 'curiyo_site_logo',
                'default' => CURIYO_DEFAULT_LOGO,
				'explanation' => CURIYO_LOGO_EXPLANATION, 
				'width' => '500px'
            ));
            
            add_settings_field('curiyo-setting_site_email', "Contact Email", array(
                &$this,
                'settings_field_input_text'
            ), 'curiyo', 'curiyo-section', array(
                'field' => 'curiyo_site_email',
                'default' => curiyo_escape_json(get_bloginfo('admin_email')),
				'explanation' => CURIYO_EMAIL_EXPLANATION,
				'width' => '500px'
            ));
            
            add_settings_field('curiyo-setting_single_post_on', "Single posts", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_single_post_on',
                'default' => CURIYO_DEFAULT_SINGLE_POST_ON
            ));

            add_settings_field('curiyo-setting_pages_on', "Pages", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_pages_on',
                'default' => CURIYO_DEFAULT_PAGES_ON
            ));

            add_settings_field('curiyo-setting_home_page_on', "Home page", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_home_page_on',
                'default' => CURIYO_DEFAULT_HOME_PAGE_ON
            ));

            add_settings_field('curiyo-setting_tags_on', "Tags", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_tags_on',
                'default' => CURIYO_DEFAULT_TAGS_ON
            ));

            add_settings_field('curiyo-setting_categories_on', "Categories", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_categories_on',
                'default' => CURIYO_DEFAULT_CATEGORIES_ON
            ));

            add_settings_field('curiyo-setting_author_archives_on', "Author archives", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_author_archives_on',
                'default' => CURIYO_DEFAULT_AUTHOR_ARCHIVES_ON
            ));

            add_settings_field('curiyo-setting_archives_on', "Archives", array(
                &$this,
                'settings_field_input_checkbox'
            ), 'curiyo', 'curiyo-display-on', array(
                'field' => 'curiyo_archives_on',
                'default' => CURIYO_DEFAULT_ARCHIVES_ON
            ));

		} // END public static function activate
        
        public function get_site_url() {
       	    $site_url = curiyo_escape_json(get_bloginfo('wpurl'));
			$first_slashes = strpos($site_url, '//');
			$following_slash = strpos($site_url, "/", $first_slashes + 2);
			if (false === $following_slash) {
				$site_url = substr($site_url, $first_slashes + 2);
			} else {
				$site_url = substr($site_url, $first_slashes + 2, $following_slash - $first_slashes - 2);
			}
			
			return $site_url;
        }
        
        public function curiyo_write_to_footer()
        {
			$site_url = $this->get_site_url();
			$link_color = get_option('curiyo_link_color', CURIYO_DEFAULT_LINK_COLOR);
			$link_color = str_replace("#", "%23", $link_color);
			$max_links = get_option('curiyo_max_links', CURIYO_DEFAULT_MAX_LINKS);
			echo '<script type="text/javascript" src="//curiyo.com/js/addcuriyo.js?pid=' . $site_url . '&almax=' . $max_links .'&cuset={%22alColor%22:%22' . $link_color .'%22}"></script>';
        }
		
		public function curiyo_write_to_footer_conditionally()
		{
			$write_to_footer = false;
			
			if (!wp_is_mobile()) {
				if (is_page() ) {
					if (get_option('curiyo_pages_on', CURIYO_DEFAULT_PAGES_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_category() ) {
					if (get_option('curiyo_categories_on', CURIYO_DEFAULT_CATEGORIES_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_tag() ) {
					if (get_option('curiyo_tags_on', CURIYO_DEFAULT_TAGS_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_home() ) {
					if (get_option('curiyo_home_page_on', CURIYO_DEFAULT_HOME_PAGE_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_single() ) {
					if (get_option('curiyo_single_post_on', CURIYO_DEFAULT_SINGLE_POST_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_author() ) {
					if (get_option('curiyo_author_archives_on', CURIYO_DEFAULT_AUTHOR_ARCHIVES_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_search() ) {
					if (get_option('curiyo_search_results_on', CURIYO_DEFAULT_SEARCH_RESULTS_ON)) {
						$write_to_footer = true;
					}
				}

				if (is_date() ) {
					if (get_option('curiyo_archives_on', CURIYO_DEFAULT_ARCHIVES_ON)) {
						$write_to_footer = true;
					}
				}	
			} 
			
			if ($write_to_footer) {
				$this->curiyo_write_to_footer();
			}
		}
        
        
        public function do_on_settings_save()
        {
            if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
                //plugin settings have been saved. Here goes your code
                $this->curiyo_publish_site_contents();
            }
        }
        
        public function curiyo_publish_site_contents()
        {
            
            $site_name        = curiyo_escape_json(get_bloginfo('name'));
            $site_email       = get_option('curiyo_site_email', curiyo_escape_json(get_bloginfo('admin_email')));
            $site_logo       = get_option('curiyo_site_logo', curiyo_escape_json(CURIYO_DEFAULT_LOGO));
            $search_page_id   = get_option('curiyo_search_page_id', 0);
            $publish_to_curiyo = get_option('curiyo_publish_to_curiyo', 0);
            $publish_to_curiyo_display = ($publish_to_curiyo) ? 'true' : 'false';
            
            if (0 <> $search_page_id) {
                $search_url = get_permalink($search_page_id);
				$search_url .= '?keyword=';
            }
            
            $servlet = "http://curiyo.com/pubupdate";          

            $site_url = $this->get_site_url();

            $json = "{\n\t\"_id\": \"" . $site_url . "\",\n";
            $json .= "\t\"name\": \"" . $site_name . "\",\n";
            $json .= "\t\"email\": \"" . $site_email . "\",\n";
            $json .= "\t\"logo\": \"" . $site_logo . "\",\n";
            $json .= "\t\"active\": \"" . $publish_to_curiyo_display . "\",\n";
            $json .= "\t\"prefix\": \"" . curiyo_escape_json($search_url) . "\",\n";
            $json .= "\t\"lang\": \"" . curiyo_escape_json(get_bloginfo('language')) . "\",\n";
            $json .= "\t\"type\": \"WORDPRESS\"";
			$json .= "\n}";
	            
            
            //Call to Curiyo server
            if (CURIYO_CALL_SERVER) {
                $response = wp_remote_post($servlet, array(
                    'timeout' => 60,
                    'redirection' => 5,
                    'httpversion' => '1.0',
                    'blocking' => true,
                    'headers' => array(),
                    'body' => array(
                        'site_contents' => $json
                    ),
                    'cookies' => array()
                ));
//				echo 'called server ' . $servlet . ' json = ' . $json;
                
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();
                    echo "Something went wrong: $error_message";
                } else {
/*                    echo 'Response:<pre>';
                    print_r($response);
                    echo '</pre>';
*/                }
            } else {
                echo $json;
            }
        }
        
        public function settings_section_curiyo()
        {
            // Think of this as help text for the section.
            // echo "Set when to update Curiyo with your site's changes.";
        }
        
        /**
         * This function provides text inputs for settings fields
         */
        public function settings_field_input_text($args)
        {
            // Get the field name from the $args array
            $field   = $args['field'];
            $default = $args['default'];
			$explanation = $args['explanation'];
			$width = $args['width'];
			if (!$width) {
				$width = 50;
			}
			
            // Get the value of this setting
            $value   = get_option($field, $default);
            // echo a proper input type="text"
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" style="width:%dpx;"/>', $field, $field, $value, $width);
			echo '<p><i>' . $explanation . '</i></p>';
        } // END public function settings_field_input_text($args)
        
        /**
         * This function provides checkbox inputs for settings fields
         */
        public function settings_field_input_checkbox($args)
        {
            // Get the field name from the $args array
            $field   = $args['field'];
            $default = $args['default'];
			$explanation = $args['explanation'];
            // Get the value of this setting
            $value   = get_option($field, $default);
            
            if ('' == $value) {
                $checked = '';
            } else {
                $checked = ' checked ';
            }
            // echo a proper input type="checkbox"
            echo sprintf('<div style="width:150 px; float:left"><input type="checkbox" name="%s" id="%s" "%s" /></div>', $field, $field, $checked);
			echo '<p><i>' . $explanation . '</i></p>';
        } // END public function settings_field_input_checkbox($args)

		public function settings_field_input_color($args)
        {
            // Get the field name from the $args array
            $field   = $args['field'];
            $default = $args['default'];
            // Get the value of this setting
            $value   = get_option($field, $default);
            // echo a proper input type="text" with color button
            echo sprintf('<input type="text" name="%s" id="%s" value="%s" class="color-field"/>', $field, $field, $value);
        } // END public function settings_field_input_text($args)
 			 
			 
       /**
         * Add a menu
         */
        public function add_menu()
        {
            // Add a page to manage this plugin's settings
            $page = add_menu_page('Curiyo Settings', 'Curiyo', 'manage_options', 'curiyo-links', array(
                &$this,
                'plugin_settings_page'
            ), plugins_url('curiyo-links/images/CuriyoIconColor16.png'));
            wp_register_style('curiyo-settings.css', CURIYO_PLUGIN_URL . "templates/curiyo-settings.css");
            add_action('admin_print_styles-' . $page, array(
                &$this,
                'curiyo_admin_styles'
            ));
            add_action('load-' . $page, array(
                &$this,
                'do_on_settings_save'
            ));
        } // END public function add_menu()
        
        public function curiyo_admin_styles()
        {
            wp_enqueue_style('curiyo-settings.css');
        }
        
		public function curiyo_add_color_picker( $hook ) {
 
			if( is_admin() ) { 
			 
				// Add the color picker css file       
				wp_enqueue_style( 'wp-color-picker' ); 
				 
				// Include our custom jQuery file with WordPress Color Picker dependency
				wp_enqueue_script( 'custom-script-handle', plugins_url( 'custom-script.js', __FILE__ ), array( 'wp-color-picker' ), false, true ); 
			}
		}
        
		/**
		 * Function that will check if value is a valid HEX color.
		 */
		public function curiyo_check_color( $value ) { 
			 
			if ( preg_match( '/^#[a-f0-9]{6}$/i', $value ) ) { // if user insert a HEX color with #     
				return true;
			}
			 
			return false;
		}
        /**
         * Menu Callback
         */
        public function plugin_settings_page()
        {
            if (!current_user_can('manage_options')) {
                wp_die(__('You do not have sufficient permissions to access this page.'));
            }
            
            // Render the settings template
            include(sprintf("%s/templates/settings.php", dirname(__FILE__)));
        } // END public function plugin_settings_page()
        
    } // END class Curiyo_Settings
    
} // END if(!class_exists('Curiyo_Settings'))