<?php
/**
 *
 */



/// 
/// 
/// 
class ConnectionsSpoke_MainAdminPage
{

	private static $error_messages;
	private static $notice_messages;

	
	/**
	 *
	 */	
	private function __construct() { }
	

	/**
	 *
	 */	
	public static function init()
	{
		self::$error_messages = array();
		self::$notice_messages = array();
	}


	/**
	 *
	 */	
	public static function display_messages()
	{
		foreach( self::$error_messages as $message )
		{
			?>
			<div class="error"><?php echo $message; ?></div>
			<?php
		}
		
		foreach( self::$notice_messages as $message )
		{
			?>
			<div class="updated"><?php echo $message; ?></div>
			<?php
		}
	}


	/**
	 *
	 */	
	public static function process_post()
	{
		if( empty($_POST['options']) ) return;
		update_option('csm_options', $_POST['options']);
		self::$notice_messages[] = 'Options updated.';
	}
	
	
	/**
	 *
	 */	
	public static function show_page()
	{
		global $wpdb;
		
		$options = get_option( 'csm_options', array() );
		
// 		var_dump($options);
		
		$pages = get_posts( array('post_type' => 'page') );
		$categories = get_categories();
		
		$contact_me_widget = ConnectionsSpoke_Main::get_contact_me_contents();
		
		$defaults = array(
			'synch' => 'home',
			'synch-page' => -1,
			'synch-category' => -1,
			'contact-type' => 'widget',
			'contact-entry' => array(
				'office' => '',
				'phone' => '',
				'email' => '',
			),
		);
		
		$options = array_replace_recursive($defaults, $options);
		
		if( $contact_me_widget == null )
			$options['contact-type'] = 'entry';
		?>

		<h2>Connections Spoke</h2>
		<h3>Main Page</h3>

		<div class="instructions">
			Some instruction go here
		</div>

		<div class="menu-selector clearfix">
		
		<form method="post">

		<h4>Post To Synchronize</h4>
		<input type="radio" name="options[synch]" value="home" <?php echo ($options['synch'] == 'home' ? 'checked' : ''); ?> />Home Page<br/>
		<input type="radio" name="options[synch]" value="page" <?php echo ($options['synch'] == 'page' ? 'checked' : ''); ?> />Page: 
			<select name="options[synch-page]">
				<?php foreach( $pages as $page ): ?>
					<option value="<?php echo $page->ID; ?>" <?php echo ($options['synch-page'] == $page->ID ? 'selected' : ''); ?>><?php echo $page->post_title; ?></option>
				<?php endforeach; ?>
			</select><br/>
		<input type="radio" name="options[synch]" value="category" <?php echo ($options['synch'] == 'category' ? 'checked' : ''); ?> />Category:
			<select name="options[synch-category]">
				<?php foreach( $categories as $category ): ?>
					<option value="<?php echo $category->cat_ID; ?>" <?php echo ($options['synch-category'] == $category->cat_ID ? 'selected' : ''); ?>><?php echo $category->name; ?></option>
				<?php endforeach; ?>
			</select><br/>
		
		
		Contact Info:<br/>
		<div class="contact-info">

			<input type="radio" name="options[contact-type]" value="widget" <?php echo ($options['contact-type'] == 'widget' ? 'checked' : '' ); echo ($contact_me_widget == null ? 'disabled' : ''); ?> />Contact Me Widget:<br/>
			<div class="widget_text">
				<?php echo ($contact_me_widget !== null ? $contact_me_widget : 'no contact widget found.'); ?>
			</div>
		
			<input type="radio" name="options[contact-type]" value="entry" <?php echo ($options['contact-type'] == 'entry' ? 'checked' : '' ); ?> />Enter Contact Data:<br/>
			<label>Office:</label>
			<input type="text" name="options[contact-entry][office]" value="<?php echo (isset($options['contact-entry']['office']) ? $options['contact-entry']['office'] : ''); ?>" /><br/>
			<label>Phone:</label>
			<input type="text" name="options[contact-entry][phone]" value="<?php echo (isset($options['contact-entry']['phone']) ? $options['contact-entry']['phone'] : ''); ?>" /><br/>
			<label>Email:</label>
			<input type="text" name="options[contact-entry][email]" value="<?php echo (isset($options['contact-entry']['email']) ? $options['contact-entry']['email'] : ''); ?>" /><br/>

		</div>
		<input type="submit" />
		</form>
		</div>
		
		<?php
	}

	
	/**
	 * 
	 */
	public static function enqueue_scripts()
	{
	}
	
	
	public static function add_head_script()
	{
		?>
		<style>
			div.updated, div.error { padding:5px 10px; }
			#admin-page-container { margin-right:15px; }
			.clearfix:before, .clearfix:after { content:""; display:table; clear:both }
			.clearfix { zoom:1; }
			.instructions { margin:10px 0px 10px 0px; padding-bottom:10px; font-size:1em; color:#999; }
			.menu-selector { width:96%; border:solid 1px #ccc; padding:2%; }
			.menu-selector #site-title { width:100%; margin-bottom:20px; }
			.menu-selector #site-title input { width:100%; }
			.menu-selector input[type=submit] { clear:both; float:right; font-size:1.2em; padding:5px 20px; border:solid 1px #ccc; background-color:#eee; box-shadow: 1px 1px 1px #ccc; }
			.menu-selector h4 { margin-top:0px; margin-bottom:10px; }
		</style>
		<?php
	}
	
}

