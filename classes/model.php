<?php

/**
 * ConnectionsSpoke_Model
 * 
 * The main model for the Connections Spoke plugin.
 * 
 * @package    connections-spoke
 * @subpackage classes
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('ConnectionsSpoke_Model') ):
class ConnectionsSpoke_Model
{
	
	private static $instance = null;	// The only instance of this class.
	public $last_error = null;			// The error logged by a model.
	
	
	/**
	 * Private Constructor.  Needed for a Singleton class.
	 * Creates an ConnectionsSpoke_Model object.
	 */
	protected function __construct()
	{
		
	}


	/**
	 * Get the only instance of this class.
	 * @return  ConnectionsSpoke_Model  A singleton instance of the model class.
	 */
	public static function get_instance()
	{
		if( self::$instance	=== null )
		{
			self::$instance = new ConnectionsSpoke_Model();
		}
		return self::$instance;
	}

	
	/**
	 * Gets an array of default options.
	 * @return  array  An array of default options.
	 */
	public function get_defaults()
	{
		return array(
			'synch' 				=> 'home',
			'synch_page' 			=> -1,
			'synch_category' 		=> -1,
			'contact_type' 			=> 'entry',
			'contact_entry' 		=> '',
			'contact_entry_filter'	=> 'no',
			'connections_hub_sites'	=> array(),
		);
	}
	
	
	/**
	 * Merge the default options with passed in options.
	 * @param   array  $options  An array of options.
	 * @return  array  An array of merged options.
	 */
	public function merge_options( $options )
	{
		return array_merge( $this->get_defaults(), $options );
	}
	
	
	/**
	 * Get the Connection Spoke's options.
	 * @return  array  An array of the Connections Spoke options.
	 */
	public function get_options()
	{
		$options = get_option( CONNECTIONS_SPOKE_OPTIONS, array() );
		if( !is_array($options) ) $options = array();
		
		$options = $this->merge_options( $options );
		
		return $options;
	}
	
	
	/**
	 * Save a complete list of options.
	 * @param   array  $options  An array of options.
	 */
	public function set_options( $options )
	{
		$old_level = error_reporting(-1);
		$options = $this->merge_options( $options );

 		$results = update_option( CONNECTIONS_SPOKE_OPTIONS, $options );
	}


	/**
	 * Get the selected Connections post for synching.
	 * @return  array|null  An array of post data or null on error.
	 */	
	public function get_post_data()
	{
		global $wpdb;
		$options = $this->get_options();
		
		// Get the page or post used for Connections data.
		$post = null;
		switch( $options['synch'] )
		{
			case 'page':
				$post = $this->get_page( $options['synch_page'] );
				break;
			
			case 'category':
				$post = $this->get_first_post( $options['synch_category'] );
				break;
			
			case 'home':
			default:
				$post = $this->get_home_page();
				break;
		}		
		if( $post === null ) return null;
		
		// Filter content.
		$content = apply_filters( 'the_content', $post->post_content );
		
		// Get post author.
		$last_author = '';
		if( $last_id = get_post_meta( $post->ID, '_edit_last', true) )
		{
			$last_user = get_userdata( $last_id );
			$last_author = apply_filters( 'the_modified_author', $last_user->display_name );
		}
		
		// Get post URL.
		$view_url = get_permalink( $post->ID );
		
		// Return complete post data.
		return array(
			'plugin-version'	=> CONNECTIONS_SPOKE_VERSION,
			'post-id'			=> $post->ID,
			'last-modified'		=> $post->post_modified,
			'last-author'		=> $last_author,
			'view-url'			=> $view_url,
			'content'			=> $content,
		);
	}
	
	
	/**
	 * Gets the front page or first post on front page if a posts page.
	 * @return  array|null  An array of post data or null on error.
	 */
	public function get_home_page()
	{
		$post = null;
		switch( get_option('show_on_front') )
		{
			case 'page':
				$id = get_option( 'page_on_front' );
				if( empty($id) ) break;
				
				$query = new WP_Query(
					array(
						'p'					=> $id,
						'post_type'			=> 'page',
						'post_status'		=> 'publish',
						'posts_per_page'	=> 1,
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
						'post_type'			=> 'post',
						'post_status'		=> 'publish',
						'posts_per_page'	=> 1
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
	
	
	/**
	 * Gets a page based on its page id.
	 * @param   int           $page_id  The page's id.
	 * @return  WP_Post|null  The page's WP_Post object or null on error.
	 */
	public function get_page( $page_id )
	{
		if( !is_numeric($page_id) ) return null;
		
		$page_id = intval( $page_id );
		$page = get_post( $page_id, OBJECT, 'display' );
		
		return $page;
	}
	
	
	/**
	 * Gets the first post in a category.
	 * @param   int           $category_id  The category's id.
	 * @return  WP_Post|null  The first post's WP_Post object or null on error.
	 */
	public function get_first_post( $category_id )
	{
		if( !is_numeric($category_id) ) return null;

		$category_id = intval( $category_id );
		$category = get_category( $category_id );
		
		if( empty($category) ) return null;
		
		$post = get_posts(
			array(
				'category'			=> $category_id,
				'posts_per_page'	=> 1,
			)
		);
		
		if( empty($post) || count($post) == 0 ) return null;
		
		return $post[0];
	}
	
	
	/**
	 * Gets the stored Connections contact information.
	 * @return  string  The user/site's Connections contact information.
	 */
	public function get_contact_information()
	{
		$options = $this->get_options();
		$contact_information = $options['contact_entry'];
		$filter_contact = $options['contact_entry_filter'];
		return array( $contact_information, $filter_contact );
	}

} // class ConnectionsSpoke_Model
endif; // if( !class_exists('ConnectionsSpoke_Model') ):

