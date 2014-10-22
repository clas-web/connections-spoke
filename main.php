<?php
/*
Plugin Name: Connections Spoke
Plugin URI: 
Description: Manages the data that is outputted for the Connections Hub.
Version: 0.1.0
Author: Crystal Barton
Author URI: http://www.crystalbarton.com
*/

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

define( 'CONNECTIONS_SPOKE_PLUGIN_NAME', 'Connections Spoke' );
define( 'CONNECTIONS_SPOKE_PLUGIN_VERSION', '1.0' );
define( 'CONNECTIONS_SPOKE_PLUGIN_PATH', dirname(__FILE__) );
define( 'CONNECTIONS_SPOKE_PLUGIN_URL', plugins_url(basename(CONNECTIONS_SPOKE_PLUGIN_PATH)) );

add_filter( 'query_vars', array('ConnectionsSpoke_Main', 'query_vars') );
add_action( 'parse_request', array('ConnectionsSpoke_Main', 'parse_request') );

if( is_admin() )
{
	add_action( 'admin_init', array('ConnectionsSpoke_Main', 'setup_actions') );
	add_action( 'admin_menu', array('ConnectionsSpoke_Main', 'setup_admin_pages') ); 
}


///
///
///
class ConnectionsSpoke_Main
{

	/**
	 * Adds plugin's tag to the list of parseable query variables.
	 */
	public static function query_vars( $query_vars )
	{
		$query_vars[] = 'connections-spoke-api';
		return $query_vars;
	}


	/**
	 * Check for the plugin's tag and if found, then process the mobile post data
	 * from the Android device.
	 */
	public static function parse_request( &$wp )
	{
		global $wp;
		if( array_key_exists('connections-spoke-api', $wp->query_vars) )
		{
			require_once(dirname(__FILE__).'/api.php');
			ConnectionsSpoke_Api::init();
			ConnectionsSpoke_Api::process_post();
			ConnectionsSpoke_Api::output_data();
			exit();
		}
		return;
	}
	
	
	/**
	 *
	 */
	public static function setup_admin_pages()
	{
	    add_menu_page(
	    	'Connections', 
	    	'Connections',
	    	'administrator', 
	    	'connections-spoke-admin-page', 
	    	array('ConnectionsSpoke_Main', 'show_admin_page')
	    );
	}


	/**
	 * Shows the admin page for the plugin.
	 */
	public static function show_admin_page()
	{
		require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/admin-page.php' );
		ConnectionsSpoke_AdminPage::init();
		ConnectionsSpoke_AdminPage::show_page();
	}
	
	
	/**
	 * Adds the needed JavaScript and CSS files needed for the plugin.
	 */	
	public static function setup_actions()
	{
		require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/admin-page.php' );
		ConnectionsSpoke_AdminPage::init();
		ConnectionsSpoke_AdminPage::setup_actions();
	}
	
	
	
	
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
					$text = wpautop($text);

				break;
			}
		}
		
		return $text;
	}	
	
	
	
	
	
}

