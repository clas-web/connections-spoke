<?php
/**
 *
 */



/// 
/// 
/// 
class ConnectionsSpoke_AdminPage
{
	/**
	 *
	 */	
	private function __construct() { }
	
	
	public static function init()
	{
	}


	/**
	 *
	 */	
	public static function show_page()
	{
		echo '<div id="admin-page-container" class="clearfix">';
		call_user_func( array('ConnectionsSpoke_AdminPage', 'show_' . self::get_page() . '_page') );
		echo '</div>';
	}
	
	
	/**
	 *
	 */	
	private static function get_page()
	{
		return 'main';
	}
	

	/**
	 *
	 */	
	private static function show_main_page()
	{
		require_once( CONNECTIONS_SPOKE_PLUGIN_PATH.'/main-admin-page.php' );
		ConnectionsSpoke_MainAdminPage::init();
		ConnectionsSpoke_MainAdminPage::process_post();
		ConnectionsSpoke_MainAdminPage::display_messages();
		ConnectionsSpoke_MainAdminPage::show_page();
	}


	/**
	 *
	 */	
	public static function setup_actions()
	{
		call_user_func( array('ConnectionsSpoke_AdminPage', 'setup_' . self::get_page() . '_actions') );
	}
	

	/**
	 *
	 */	
	private static function setup_main_actions()
	{
		require_once( dirname(__FILE__).'/main-admin-page.php' );
		add_action( 'admin_enqueue_scripts', array('ConnectionsSpoke_MainAdminPage', 'enqueue_scripts') );
		add_action( 'admin_head', array('ConnectionsSpoke_MainAdminPage', 'add_head_script') );
	}
	
}

