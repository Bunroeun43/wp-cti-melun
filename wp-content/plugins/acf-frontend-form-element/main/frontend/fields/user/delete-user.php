<?php

if( ! class_exists('acf_field_delete_user') ) :

class acf_field_delete_user extends acf_field {
	
	
	/*
	*  __construct
	*
	*  This function will setup the field type data
	*
	*  @type	function
	*  @date	5/03/2014
	*  @since	5.0.0
	*
	*  @param	n/a
	*  @return	n/a
	*/
	
	function initialize() {
		
		// vars
		$this->name = 'delete_user';
		$this->label = __("Delete User",FEA_NS);
		$this->category = __( 'User', FEA_NS );
		$this->defaults = array(
			'button_text' 	=> __( 'Delete', FEA_NS ),
			'confirmation_text' => __( 'Are you sure you want to delete this user?', FEA_NS ),
            'field_label_hide'  => 1,
			'reassign_posts' => 0,
		);

		add_action( 'wp_ajax_acf_frontend/fields/reassign_posts/query', array( $this, 'ajax_query' ) );

		
	}

		/*
	*  ajax_query
	*
	*  description
	*
	*  @type    function
	*  @date    17/06/2016
	*  @since   5.3.8
	*
	*  @param   $post_id (int)
	*  @return  $post_id (int)
	*/

	function ajax_query() {

		// validate
		if ( ! acf_verify_ajax() ) {
			die();
		}

		$results = array();

		$all_roles = wp_roles()->get_names();

		// Load all roles if none provided.
		if ( empty( $roles ) ) {
			$roles = array_keys( $all_roles );
		}

		// Loop over roles and populare labels.
		$lables = array();
		foreach ( $roles as $role ) {
			$users = acf_get_users( array( 'include' => array(), 'role' => $role ) );

			// bail early if no field groups
			if ( empty( $users ) ) continue;

			$data = array( 'text' => translate_user_role( $all_roles[ $role ] ) );
			
			foreach ( $users as $user ) {
				$text = $user->user_login;
	
				// Add name.
				if ( $user->first_name && $user->last_name ) {
					$text .= " ({$user->first_name} {$user->last_name})";
				} elseif ( $user->first_name ) {
					$text .= " ({$user->first_name})";
				}
				$data['children'][] = array(
					'id' => $user->ID,
					'text' => $text,
				);
			}

			$results[] = $data;
		}	
		
		$limit       = 20;	

		
		// return
		acf_send_ajax_results(
			array(
				'results' => $results,
				'limit'   => $limit,
			)
		);

	}


	
	
	/*
	*  render_field()
	*
	*  Create the HTML interface for your field
	*
	*  @param	$field - an array holding all the field's data
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*/
	
	function render_field( $field ) {		
		$confirm = ! empty( $field['confirmation_text'] ) ? $field['confirmation_text'] : __( 'Are you sure you want to delete this user?', FEA_NS ); 		

		// vars
		$m = '<button type="button" class="fea-delete-button button button-primary" data-object="user" data-confirm="' .$confirm. '" data-state="delete">' .$field['button_text']. '</button>';
				
		// wptexturize (improves "quotes")
		$m = wptexturize( $m );

		echo $m;
	}
	
	
	/*
	*  load_field()
	*
	*  This filter is appied to the $field after it is loaded from the database
	*
	*  @type	filter
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field - the field array holding all the field options
	*
	*  @return	$field - the field array holding all the field options
	*/
	function load_field( $field ) {		
		// remove name to avoid caching issue
		$field['name'] = '';
		
		// remove instructions
		$field['instructions'] = '';
		
		// remove required to avoid JS issues
		$field['required'] = 0;
		
		// set value other than 'null' to avoid ACF loading / caching issue
		$field['value'] = false;

		$field['field_label_hide'] = 1;

		$field['no_data_collect'] = 1;
		
		// return
		return $field;
	}

	function prepare_field( $field ){
		if( empty( $GLOBALS['admin_form']['user_id'] ) || ! is_numeric( $GLOBALS['admin_form']['user_id'] ) ) return false;
		return $field;
	}

	function render_field_settings( $field ){
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Button Text', FEA_NS ),
			'type'			=> 'text',
			'name'			=> 'button_text',
		));
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Confirmation Text', FEA_NS ),
			'type'			=> 'text',
			'name'			=> 'confirmation_text',
		));

		$choices = array();
		if( $field['reassign_posts'] ){
			$user = get_user_by( 'id', intval( $field['reassign_posts'] ) );

			if( isset( $user->ID ) ){
				echo $user->ID;
				$choices = array( $user->ID => $user->user_login );
			}
		}
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Reassign Posts', FEA_NS ),
			'type'			=> 'select',
			'ui'           => 1,
            'ajax'         => 1,
			'allow_null'   => 1,
			'choices'	   => $choices,
			'ajax_action'  => 'acf_frontend/fields/reassign_posts/query',
			'placeholder'  => __( 'Delete Posts', FEA_NS ),
			'name'			=> 'reassign_posts',
		));

		acf_render_field_setting( $field, array(
			'label'			=> __( 'Show Delete Message', FEA_NS ),
			'type'			=> 'true_false',
			'ui' 			=> 1,
			'name'			=> 'show_delete_message',
		));
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Delete Message', FEA_NS ),
			'type'			=> 'textarea',
			'name'			=> 'delete_message',
			'rows'			=> 3,
			'conditions' => array(
                array(
                    array(
                        'field'     => 'show_delete_message',
                        'operator'  => '==',
                        'value'     => 1,
                    ),
                ),
            )
		));
		acf_render_field_setting( $field, array(
			'label'			=> __('Redirect After Delete',FEA_NS),
			'instructions'	=> '',
			'type'			=> 'select',
			'name'			=> 'redirect',
            'multiple'      => 1,
            'ui'            => 1,
			'choices'		=> array(
				'current'  => __( 'Reload Current Url', FEA_NS ),
				'custom_url' => __( 'Custom Url', FEA_NS ),
				'referer_url' => __( 'Referer', FEA_NS ),
			),
		));
		acf_render_field_setting( $field, array(
			'label'			=> __( 'Custom Url', FEA_NS ),
			'type'			=> 'url',
			'name'			=> 'custom_url',
			'conditions' => array(
                array(
                    array(
                        'field'     => 'redirect',
                        'operator'  => '==',
                        'value'     => 'custom_url',
                    ),
                ),
            )
		));
	}
	
}


// initialize
acf_register_field_type( 'acf_field_delete_user' );

endif; // class_exists check

?>