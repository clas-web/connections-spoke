<?php

/**
 * ConnectionsSpoke_Api
 * 
 * This is the main control class for the Connections Spoke API.
 * 
 * @package    connection-spoke
 * @author     Crystal Barton <atrus1701@gmail.com>
 */
if( !class_exists('ConnectionsSpoke_Api') ):
class ConnectionsSpoke_Api
{

	private $model = null;
	private $status;   // true or false
	private $message;  // error message
	private $output;   // array of to output for Android app to parse into JSONArray
	
	
	/**
	 * Creates an ConnectionsSpoke_Api object.
	 */	
	public function __construct()
	{
		$this->model	= ConnectionsSpoke_Model::get_instance();
		$this->status	= true;
		$this->message	= '';
		$this->output	= array();
	}
	
	
	/**
	 * Process the Connections Spoke API action and save the Connection Hub site data, 
	 * if present.
	 */	
	public function process()
	{
		if( !isset($_GET['connections-spoke-api']) )
		{
			$this->status = false;
			$this->message = 'The connections-spoke-api key is required.';
		}
		
		switch( $_GET['connections-spoke-api'] )
		{
			case( 'get-connections-data' ):
				$this->get_connections_data();
				break;
				
			default:
				$this->status = false;
				$this->message = 'connections-spoke-api='.$_GET['connections-spoke-api'].' is an invalid action.';
				return;
		}
		
		if( isset($_GET['connections-hub']) )
		{
			list( $connections_hub_name, $connections_hub_url ) = explode( '|', $_GET['connections-hub'], 2 );
			$options = $this->model->get_options();
			
			if( $connections_hub_name && $connections_hub_url )
			{
				$options['connections_hub_sites'][$connections_hub_name] = $connections_hub_url;
				$this->model->set_options( $options );
			}
		}
	}
	
	
	/**
	 * Get the Connections data and store in the output class member.
	 */
	public function get_connections_data()
	{
		$post_data = $this->model->get_post_data();
		list( $contact_information, $filter ) = $this->model->get_contact_information();
		
		$this->output = array();
		
		if( $post_data !== null )
		{
			foreach( $post_data as $key => $value )
			{
				$this->output[$key] = $value;
			}
		}
		else
		{
			$this->status = false;
			$this->message = 'No valid post could be found.';
		}
		
		$this->output['contact-info'] = $contact_information;
		$this->output['contact-info-filter'] = $filter;
	}
	
	
	/**
	 * Output the Connections data.
	 */	
	public function output()
	{
		$output = array(
			'status'	=> $this->status,
			'message'	=> $this->message,
			'output'	=> $this->output,
		);
		
		if( isset($_GET['output']) && ($_GET['output'] === 'html') )
		{
			apl_print( $output );
		}
		else
		{
			echo json_encode( $output );
		}
	}

} // class ConnectionsSpoke_Api
endif; // if( !class_exists('ConnectionsSpoke_Api') ):

