<?php
/*
Plugin Name: Connections Spoke
Plugin URI: 
Description: Manages the data that is outputted for the Connections Hub.
Version: 0.1.0
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/


// Global plugin variables
if( !defined('CONNECTIONS_SPOKE') ):

define( 'CONNECTIONS_SPOKE', 'Connections Spoke' );

define( 'CONNECTIONS_SPOKE_DEBUG', false );

define( 'CONNECTIONS_SPOKE_PLUGIN_PATH', dirname(__FILE__) );
define( 'CONNECTIONS_SPOKE_PLUGIN_URL', plugins_url('', __FILE__) );

define( 'CONNECTIONS_SPOKE_VERSION', '2.0.0' );
define( 'CONNECTIONS_SPOKE_OPTIONS', 'csm_options' );

endif;


// Require the Connections Spoke model.
require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/classes/model.php' );

// Setup the Contact Widget
require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/classes/contact-widget/control.php' );
ConnectionsSpokeContact_WidgetShortcodeControl::register_widget();

// Make sure APL is present in order to activate plugin.
register_activation_hook( __FILE__, array('ConnectionsSpoke_Main', 'activate_plugin') );

// Setup Connections Spoke API used by Connection Hub for data requests.
add_filter( 'query_vars', array('ConnectionsSpoke_Main', 'query_vars') );
add_action( 'parse_request', array('ConnectionsSpoke_Main', 'parse_request') );

if( is_admin() )
{
	// Setup the admin pages.
	add_action( 'wp_loaded', array('ConnectionsSpoke_Main', 'load') );
}


/**
 * ConnectionsSpoke_Main
 * 
 * This is the main control class for the "Connections Spoke" plugin.
 * 
 * @package    connection-spoke
 * @author     Crystal Barton <cbarto11@uncc.edu>
 */
if( !class_exists('ConnectionsSpoke_Main') ):
class ConnectionsSpoke_Main
{

	/**
	 * Checks that the Admin Page Library is activated and the correct version before
	 * activating the plugin.
	 */
	public static function activate_plugin()
	{
		if( !defined('APL') || !defined('APL_VERSION') )
		{
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'The '.CONNECTIONS_SPOKE.' plugin requires the Admin Page Library.' );
		}
		
		if( version_compare(APL_VERSION, '1.0') < 0 )
		{
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( 'The '.CONNECTIONS_SPOKE.' plugin requires version 1.0 or greater of the Admin Page Library.' );
		}
	}
	
	
	/**
	 * Loads the needed files and sets up the admin pages.
	 */
	public static function load()
	{
		$model = ConnectionsSpoke_Model::get_instance();
		$options = $model->get_options();
		if( empty($options['connections_hub_sites']) ) return;
		
		require_once( dirname(__FILE__).'/admin-pages/require.php' );
		
		$handler = new APL_Handler( false );
		$handler->add_page( new ConnectionsSpoke_OptionsAdminPage );
		$handler->setup();
	}
	
	
	/**
	 * Setup the Connections Spoke API query var.
	 * @param   array  $query_vars  An array of vars to search for in the URL arguments.
	 * @return  array  The altered query vars.
	 */
	public static function query_vars( $query_vars )
	{
		$query_vars[] = 'connections-spoke-api';
		return $query_vars;
	}


	/**
	 * Check for the plugin's tag and if found, then process the mobile post data
	 * from the Android device.
	 * @param   WP  $wp  Current WordPress environment instance
	 */
	public static function parse_request( &$wp )
	{
		global $wp;
		if( array_key_exists('connections-spoke-api', $wp->query_vars) )
		{
			require_once(dirname(__FILE__).'/api.php');
			$api = new ConnectionsSpoke_Api;
			$api->process();
			$api->output();
			exit;
		}
	}
	
	
	/**
	 * 
	 */
	public static function get_contact_me_contents()
	{
		global $wpdb;

		$widgets = get_option( 'widget_text', null );
		if( (!$widgets) || !is_array($widgets) ) return null;

		$text = null;
		foreach( $widgets as $widget )
		{
			if( !is_array($widget) ) break;;
			
			if( (isset($widget['title'])) && ($widget['title'] == 'Contact Me') )
			{
				$text = $widget['text'];
				
				if( $widget['filter'] )
					$text = wpautop( $text );

				break;
			}
		}
		
		return $text;
	}	
	
}
endif;

