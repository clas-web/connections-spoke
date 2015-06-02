<?php

require_once( dirname(__FILE__).'/widget-shortcode-control.php' );

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
 * @author     Crystal Barton <cbarto11@uncc.edu>
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
		$this->model = ConnectionsSpoke_Model::get_instance();
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
		$options = $this->merge_options( $options );
		extract( $options );

		$csm_options = $this->model->get_options();		
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
		<textarea name="<?php echo $this->get_field_name( 'contact_entry' ); ?>"><?php echo $csm_options['contact_entry']; ?></textarea>
		<input type="hidden" name="<?php echo $this->get_field_name( 'contact_entry_filter' ); ?>" value="no" />
		<input type="checkbox" name="<?php echo $this->get_field_name( 'contact_entry_filter' ); ?>" value="yes" <?php checked($csm_options['contact_entry_filter'], 'yes'); ?> />
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
		$widget_options = array(
			'title'	=> $new_options['title'],
		);
		
		$csm_options = $this->model->get_options();
		$csm_options['contact_entry'] = $new_options['contact_entry'];
		$csm_options['contact_entry_filter'] = $new_options['contact_entry_filter'];
		$this->model->set_options( $csm_options );
		
		return $widget_options;
	}
	
	
	/**
	 * Echo the widget or shortcode contents.
	 * @param   array  $options  The current settings for the control.
	 * @param   array  $args     The display arguments.
	 */
	public function print_control( $options, $args = null )
	{
		extract( $options );
		
		echo $args['before_widget'];
		echo '<div id="connections-contact-info-control-'.self::$index.'" class="wscontrol connections-contact-info-control">';
		
		if( !empty($title) )
		{
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		echo $this->model->get_contact_information();
		
		echo '</div>';
		echo $args['after_widget'];		
	}
	
}
endif;

