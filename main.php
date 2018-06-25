<?php
/*
Plugin Name: Connections Spoke
Plugin URI: https://github.com/clas-web/connections-spoke
Description: Manages the data shared between the Connection Hub site(s) and a profile site.
Version: 1.2.2
Author: Crystal Barton
Author URI: https://www.linkedin.com/in/crystalbarton
GitHub Plugin URI: https://github.com/clas-web/connections-spoke
*/


if( !defined('CONNECTIONS_SPOKE') ):

/**
 * The full title of the Connections Spoke plugin.
 * @var  string
 */
define( 'CONNECTIONS_SPOKE', 'Connections Spoke' );

/**
 * True if debug is active, otherwise False.
 * @var bool
 */
define( 'CONNECTIONS_SPOKE_DEBUG', false );

/**
 * The path to the plugin.
 * @var string
 */
define( 'CONNECTIONS_SPOKE_PLUGIN_PATH', __DIR__ );

/**
 * The url to the plugin.
 * @var string
 */
define( 'CONNECTIONS_SPOKE_PLUGIN_URL', plugins_url('', __FILE__) );

/**
 * The version of the plugin.
 * @var string
 */
define( 'CONNECTIONS_SPOKE_VERSION', '2.0.0' );

/**
 * The database options key for the Connections Spoke options.
 * @var string
 */
define( 'CONNECTIONS_SPOKE_OPTIONS', 'csm_options' );

endif;


require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/classes/model.php' );

require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/classes/contact-widget/control.php' );
ConnectionsSpokeContact_WidgetShortcodeControl::register_widget();

register_activation_hook( __FILE__, 'conspk_activate_plugin' );
add_filter( 'query_vars', 'conspk_query_vars' );
add_action( 'parse_request', 'conspk_parse_request' );

if( is_admin() )
{
	add_action( 'wp_loaded', 'conspk_load' );
}


/**
 * Checks that the Admin Page Library is activated and the correct version before activating the plugin.
 */
if( !function_exists('conspk_activate_plugin') ):
function conspk_activate_plugin()
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
endif;


/**
 * Loads the needed files and sets up the admin pages.
 */
if( !function_exists('conspk_load') ):
function conspk_load()
{
	$model = ConnectionsSpoke_Model::get_instance();
	$options = $model->get_options();
	if( empty($options['connections_hub_sites']) ) return;
	
	require_once( __DIR__.'/admin-pages/require.php' );
	
	$handler = new APL_Handler( false );
	$handler->add_page( new ConnectionsSpoke_OptionsAdminPage );
	$handler->setup();
}
endif;


/**
 * Setup the Connections Spoke API query var.
 * @param  array  $query_vars  An array of vars to search for in the URL arguments.
 * @return  array  The altered query vars.
 */
if( !function_exists('conspk_query_vars') ):
function conspk_query_vars( $query_vars )
{
	$query_vars[] = 'connections-spoke-api';
	return $query_vars;
}
endif;


/**
 * Check for the plugin's tag and if found, then process the mobile post data from the Android device.
 * @param  WP  $wp  Current WordPress environment instance
 */
if( !function_exists('conspk_parse_request') ):
function conspk_parse_request( &$wp )
{
	global $wp;
	if( array_key_exists('connections-spoke-api', $wp->query_vars) )
	{
		require_once(__DIR__.'/api.php');
		$api = new ConnectionsSpoke_Api;
		$api->process();
		$api->output();
		exit;
	}
}
endif;

