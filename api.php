<?php
/**
 *
 */


/// 
/// 
/// 
class ConnectionsSpoke_Api
{

	private static $_status;   // true or false
	private static $_message;  // error message
	private static $_output;   // array of to output for Android app to parse into JSONArray
	
	
	/**
	 *
	 */	
	private function __construct() { }

		
	/**
	 *
	 */	
	public static function init()
	{
		self::$_status = true;
		self::$_message = '';
		self::$_output = array();
	}
	
	
	/**
	 *
	 */	
	public static function process_post()
	{
		switch( $_GET['connections-spoke-api'] )
		{
			case( 'get-site' ):
				self::get_site();
				break;
				
			default:
				self::$_status = false;
				self::$_message = 'Invalid action.';
				return;
				break;
		}
	}
	
	
	/**
	 *
	 */	
	private static function get_site()
	{
		global $wpdb;
		
		$output = array();

		$options = get_option( 'csm_options', array( 'synch' => 'home' ) );
		switch( $options['synch'] )
		{
			case 'page':
				if( isset($options['synch-page']) )
					$post = self::get_page( $options['synch-page'] );
				else
					$post = self::get_home_page();
				break;
			case 'category':
				if( isset($options['synch-category']) )
					$post = self::get_first_post( $options['synch-category'] );
				else
					$post = self::get_home_page();
				break;
			case 'home':
			default:
				$post = self::get_home_page();
				break;
		}
		
		if( $post === null )
		{
			self::$_status = false;
			self::$_message = 'No valid post was found.';
			self::$_output = array();
			return;
		}
		
		// get author
		$last_author = '';
		if( $last_id = get_post_meta( $post->ID, '_edit_last', true) )
		{
			$last_user = get_userdata($last_id);
			$last_author = apply_filters('the_modified_author', $last_user->display_name);
		}
		
		$view_url = get_permalink( $post->ID );
		
		$contact_info = null;
		if( isset($options['contact-type']) )
		{
			switch($options['contact-type'])
			{
				case 'entry':
					$contact_info = '';
					
					if( isset($options['contact-entry']['email']) )
						$contact_info .= '<div class="email">Email: '.$options['contact-entry']['email'].'</div>';

					if( isset($options['contact-entry']['phone']) )
						$contact_info .= '<div class="phone">Phone: '.$options['contact-entry']['phone'].'</div>';

					if( isset($options['contact-entry']['office']) )
						$contact_info .= '<div class="office">Office: '.$options['contact-entry']['office'].'</div>';

					break;
				
				case 'widget':
				default:
					$contact_info = ConnectionsSpoke_Main::get_contact_me_contents();
					break;
			}
		}
	
		self::$_output = array(
			'plugin-version' => CONNECTIONS_SPOKE_PLUGIN_VERSION,
			'post-id' => $post->ID,
			'last-modified' => $post->post_modified,
			'last-author' => $last_author,
			'view-url' => $view_url,
			'content' => $post->post_content,
			'contact-info' => $contact_info,
		);
	}
	
	
	public static function get_home_page()
	{
		$post = null;
		switch( get_option('show_on_front') )
		{
			case 'page':
				$id = get_option('page_on_front');
				if( empty($id) ) break;
				
				$query = new WP_Query(
					array(
						'p' => $id,
						'post_type' => 'page',
						'post_status' => 'publish',
						'numberposts' => 1
					)
				);

				if( $query->have_posts() )
				{
					$query->the_post();
					$post = get_post();
				}

				wp_reset_query();
				break;

			case 'posts':
			default:
				$query = new WP_Query(
					array(
						'post_type' => 'post',
						'post_status' => 'publish',
						'numberposts' => 1
					)
				);

				if( $query->have_posts() )
				{
					$query->the_post();
					$post = get_post();
				}

				wp_reset_query();
				break;
		}

		return $post;
	}
	
	public static function get_page( $page_id )
	{
		if( !is_numeric($page_id) )
			return self::get_home_page();
		
		$page_id = intval( $page_id );
		$page = get_post( $page_id, OBJECT, 'display' );
		
		if( $page == null ) return self::get_home_page();
		
		return $page;
	}
	
	public static function get_first_post( $category_id )
	{
		if( !is_numeric($category_id) )
			return self::get_home_page();

		$category_id = intval( $category_id );
		$category = get_category( $category_id );
		
		if( empty($category) ) return self::get_home_page();
		
		$post = get_posts( array('category' => $category_id, 'numberposts' => 1) );
		
		if( empty($post) || count($post) == 0 )
			return self::get_home_page();

		return $post[0];
	}
	
	/**
	 *
	 */	
	public static function output_data()
	{
		if( isset($_GET['output']) && ($_GET['output'] === 'html') )
		{
			echo '<pre>';
			print_r( array(
		 		'status' => self::$_status,
		 		'message' => self::$_message,
		 		'output' => self::$_output
			));
			echo '</pre>';
		}
		else
		{
			echo (
				json_encode( array(
					'status' => self::$_status,
					'message' => self::$_message,
					'output' => self::$_output
				))
			);
		}
	}

}

