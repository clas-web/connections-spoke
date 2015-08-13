<?php

/**
 * ConnectionsSpoke_OptionsAdminPage
 * 
 * This class controls the admin page "Synch Connections".
 * 
 * @package    connection-hub
 * @subpackage admin-pages/pages
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('ConnectionsSpoke_OptionsAdminPage') ):
class ConnectionsSpoke_OptionsAdminPage extends APL_AdminPage
{
	
	private $model = null;
	
	
	/**
	 * Creates an ConnectionsSpoke_SynchConnections object.
	 */
	public function __construct(
		$name = 'connections-options',
		$menu_title = 'Connections Options',
		$page_title = 'Connections Options',
		$capability = 'administrator' )
	{
		parent::__construct( $name, $menu_title, $page_title, $capability );
		$this->model = ConnectionsSpoke_Model::get_instance();
	}
	

	/**
	 * Register each individual settings for the Settings API.
	 */
	public function register_settings()
	{
		$this->register_setting( CONNECTIONS_SPOKE_OPTIONS );
	}
	

	/**
	 * Add the sections used for the Settings API. 
	 */
	public function add_settings_sections()
	{
		$this->add_section(
			'post-to-synch',
			'Post to Synchronize',
			'print_section_post_to_synch'
		);
		$this->add_section(
			'contact-info',
			'Contact Information',
			'print_section_contact_info'
		);
		$this->add_section(
			'connections_hub_sites',
			'Connection Hub Site(s)',
			'print_section_connections_hub_sites'
		);
	}
	
	
	public function print_section_post_to_synch( $args )
	{
		apl_print('print_section_post_to_synch');
		
		$pages = get_posts( array('post_type' => 'page') );
		$categories = get_categories();
		
		$options = $this->model->get_options();
		
		$synch_name 		= array( CONNECTIONS_SPOKE_OPTIONS, 'synch' );
		$synch_page_name 	= array( CONNECTIONS_SPOKE_OPTIONS, 'synch_page' );
		$synch_cat_name 	= array( CONNECTIONS_SPOKE_OPTIONS, 'synch_category' );

		?>
		
		<div class="cs-option">
		<input type="radio" name="<?php apl_name_e( $synch_name ); ?>" value="home" <?php checked($options['synch'], 'home'); ?> />
		Home Page
		</div>
		
		<div class="cs-option">
			<div class="radio-label">
				<input type="radio" name="<?php apl_name_e( $synch_name ); ?>" value="page" <?php checked($options['synch'], 'page'); ?> />
				Page
			</div>
		
		<select name="<?php apl_name_e( $synch_page_name ); ?>">
			<?php foreach( $pages as $page ): ?>
				<option value="<?php echo $page->ID; ?>" <?php selected($options['synch_page'], $page->ID); ?>><?php echo $page->post_title; ?></option>
			<?php endforeach; ?>
		</select>
		</div>
		
		<div class="cs-option">
			<div class="radio-label">
				<input type="radio" name="<?php apl_name_e( $synch_name ); ?>" value="category" <?php checked($options['synch'], 'category'); ?> />
				Category
			</div>
		
		<select name="<?php apl_name_e( $synch_cat_name ); ?>">
			<?php foreach( $categories as $category ): ?>
				<option value="<?php echo $category->cat_ID; ?>" <?php selected($options['synch_category'], $category->cat_ID); ?>><?php echo $category->name; ?></option>
			<?php endforeach; ?>
		</select>
		</div>
		
		<?php
	}

	public function print_section_contact_info( $args )
	{
		apl_print('print_section_contact_info');
		
		$options = $this->model->get_options();
		
		$contact_me_widget = ConnectionsSpoke_Main::get_contact_me_contents();
		if( $contact_me_widget == null ) $options['contact_type'] = 'entry';

// 		$contact_type_name 			= array( CONNECTIONS_SPOKE_OPTIONS, 'contact_type' );
		$contact_entry_name 		= array( CONNECTIONS_SPOKE_OPTIONS, 'contact_entry' );
		$contact_entry_filter_name	= array( CONNECTIONS_SPOKE_OPTIONS, 'contact_entry_filter' );
		
		?>
<!-- 
		<div class="cs-option">
		<input type="radio" name=<?php apl_name_e( $contact_type_name ); ?>" value="widget" <?php checked($options['contact_type'], 'widget'); ?> <?php echo ($contact_me_widget == null ? 'disabled' : ''); ?> />
		Contact Me Widget
		</div>
		
		<?php
			$class = 'widget_text';
			if( $contact_me_widget == null )
			{
				$class .= ' no_text';
				$contact_me_widget = 'No contact widget found.';
			}
		?>
		<div class="<?php echo $class; ?>">
			<?php echo $contact_me_widget; ?>
		</div>
 -->
		
		<div class="cs-option">
<!-- 
		<input type="radio" name=<?php apl_name_e( $contact_type_name ); ?>" value="entry" <?php checked($options['contact_type'], 'entry'); ?> />
		Contact Entry
		<br/>
 -->
		<textarea name="<?php apl_name_e( $contact_entry_name ); ?>" class="contact_entry"><?php echo $options['contact_entry']; ?></textarea>
		<input type="hidden" name="<?php apl_name_e( $contact_entry_filter_name ); ?>" value="no" />
		<input type="checkbox" name="<?php apl_name_e( $contact_entry_filter_name ); ?>" value="yes" <?php checked($options['contact_entry_filter'], 'yes'); ?> />
		Automatically add paragraphs
		</div>
		<?php
	}
	
	
	public function print_section_connections_hub_sites( $args )
	{
		apl_print( 'print_section_connections_hub_sites' );
		
		$options = $this->model->get_options();
		$sites = $options['connections_hub_sites'];
		
		foreach( $sites as $name => $url ):
			
			?>
			<div class="connection-hub-site">
				<a href="<?php echo $url; ?>" target="_blank"><?php echo $name; ?></a>
			</div>
			<?php
		
		endforeach;
	}
	
	
	/**
	 * Enqueues all the scripts or styles needed for the admin page. 
	 */
	public function enqueue_scripts()
	{
		wp_enqueue_style( 'connection-spoke-main', CONNECTIONS_SPOKE_PLUGIN_URL.'/admin-pages/style.css' );
	}
	
	
	/**
	 * Displays the current admin page.
	 */
	public function display()
	{
		$this->print_settings();
	}
	
} // class ConnectionsSpoke_OptionsAdminPage extends APL_AdminPage
endif; // if( !class_exists('ConnectionsSpoke_OptionsAdminPage') )

