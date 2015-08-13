<?php
require_once( __DIR__.'/widget-shortcode-control.php' );


/**
 * ConnectionsSpokeContact_WidgetShortcodeControl
 * 
 * The ConnectionsSpokeContact_WidgetShortcodeControl class for the "Connections Spoke" plugin's Contact Information widget.
 * Derived from the official WP RSS widget.
 * 
 * Shortcode Example:
 * [connections_contact_info title="Contact Me"]
 * 
 * @package    clas-buttons
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('ConnectionsSpokeContact_WidgetShortcodeControl') ):
class ConnectionsSpokeContact_WidgetShortcodeControl extends WidgetShortcodeControl
{
	
	private $model = null;
	
	/**
	 * Constructor.
	 * Setup the properties and actions.
	 */
	public function __construct()
	{
		$widget_ops = array(
			'description'	=> 'Display the contact information.',
		);
		
		parent::__construct( 'connections-contact-info', 'Contact Information', $widget_ops );
//		$this->update_widget_settings();
		$this->model = ConnectionsSpoke_Model::get_instance();

		// Save the widget settings to the Connections Spoke options when the customizer saves.
		add_action( 'customize_save_after', array($this, 'theme_customizer_save') );
		add_action( 'wp_register_sidebar_widget', array($this, 'update_widget_settings') );
	}
	
	
	/**
	 * Enqueues the scripts or styles needed for the control in the site frontend.
	 */
	public function admin_enqueue_scripts()
	{
		wp_enqueue_style( 'connections-contact-info', CONNECTIONS_SPOKE_PLUGIN_URL.'/classes/contact-widget/admin-style.css' );
	}
	
	
	/**
	 * Output the widget form in the admin.
	 * Use this function instead of form.
	 * @param   array   $options  The current settings for the widget.
	 */
	public function print_widget_form( $options )
	{
		global $wp_customize;
		
		$options = $this->merge_options( $options );
		extract( $options );

		if( !isset($wp_customize) || !isset($options['contact_entry']) )
		{
			$csm_options = $this->model->get_options();
			extract( $csm_options );
		}
		?>

		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<br/>
		<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" class="widefat">
		<br/>
		</p>
		
		<p>
		<label for="<?php echo $this->get_field_id( 'contact_entry' ); ?>"><?php _e( 'Contact Info:' ); ?></label> 
		<br/>
		<textarea name="<?php echo $this->get_field_name( 'contact_entry' ); ?>"><?php echo $contact_entry; ?></textarea>
		<input type="hidden" name="<?php echo $this->get_field_name( 'contact_entry_filter' ); ?>" value="no" />
		<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_entry_filter' ); ?>" value="yes" <?php checked($contact_entry_filter, 'yes'); ?> />
		Automatically add paragraphs
		</p>
		
		<?php
	}
	
	
	/**
	 * Get the default settings for the widget or shortcode.
	 * @return  array  The default settings.
	 */
	public function get_default_options()
	{
		return array(
			'title'	=> '',
			'contact_entry' => '',
			'contact_entry_filter' => 'no',
		);
	}


	/**
	 * Update a particular instance.
	 * Override function from WP_Widget parent class.
	 * @param   array       $new_options  New options set in the widget form by the user.
	 * @param   array       $old_options  Old options from the database.
	 * @return  array|bool  The settings to save, or false to cancel saving.
	 */
	public function update( $new_options, $old_options )
	{
		global $wp_customize;

		$widget_options = array(
			'title'	=> $new_options['title'],
			'contact_entry' => $new_options['contact_entry'],
			'contact_entry_filter' => $new_options['contact_entry_filter'],
		);

		if( !isset($wp_customize) )
		{
			$this->save( $new_options );
		}
		
		return $widget_options;
	}


	/**
	 * Save the widget properties to the Connection Spoke options when the Theme Customizer saves.
	 */
	public function theme_customizer_save()
	{
		$settings = $this->get_settings();
		$this->save( $settings[$this->number] );
	}
	
	
	public function update_widget_settings( $widget = null )
	{
		if( $widget !== null && $widget['id'] !== $this->id ) return;
		
		$csm_options = $this->model->get_options();
		$settings = $this->get_settings();
		
		$this_settings = $settings[$this->number];
		$this_settings['contact_entry'] = $csm_options['contact_entry'];
		$this_settings['contact_entry_filter'] = $csm_options['contact_entry_filter'];
		$settings[$this->number] = $this_settings;
		
		$this->save_settings( $settings );
	}


	/**
	 * Save the options to the Connection SPoke options.
	 * @param  Array  $options  The array of options.
	 */
	protected function save( $options )
	{
		$csm_options = $this->model->get_options();
		if( isset($options['contact_entry']) ) 
			$csm_options['contact_entry'] = $options['contact_entry'];
		if( isset($options['contact_entry_filter']) ) 
			$csm_options['contact_entry_filter'] = $options['contact_entry_filter'];
		$this->model->set_options( $csm_options );
	}
	
	
	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
		global $wp_customize;
		extract( $options );
		
		echo $args['before_widget'];
		echo '<div id="connections-contact-info-control-'.self::$index.'" class="wscontrol connections-contact-info-control">';
		
		if( !empty($title) )
		{
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		if( !isset($wp_customize) || !isset($options['contact_entry']) )
			list( $contact_entry, $contact_entry_filter ) = $this->model->get_contact_information();

		if( $contact_entry_filter === 'yes' )
		{
			$contact_entry = wpautop( $contact_entry );
		}
		
		echo $contact_entry;
		
		echo '</div>';
		echo $args['after_widget'];		
	}
	
}
endif;

