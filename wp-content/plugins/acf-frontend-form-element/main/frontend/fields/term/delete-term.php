<?php

if( ! class_exists('acf_field_delete_term') ) :

class acf_field_delete_term extends acf_field {
	
	
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
		$this->name = 'delete_term';
		$this->label = __("Delete Term",FEA_NS);
		$this->category = __( 'Term', FEA_NS );
		$this->defaults = array(
			'button_text' 	=> __( 'Delete', FEA_NS ),
			'confirmation_text' => __( 'Are you sure you want to delete this term?', FEA_NS ),
            'field_label_hide'  => 1,
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
		$confirm = ! empty( $field['confirmation_text'] ) ? $field['confirmation_text'] : __( 'Are you sure you want to delete this term?', FEA_NS ); 		

		// vars
		$m = '<button type="button" class="fea-delete-button button button-primary" data-object="term" data-confirm="' .$confirm. '" data-state="delete">' .$field['button_text']. '</button>';
				
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
		if( empty( $GLOBALS['admin_form']['term_id'] ) || ! is_numeric( $GLOBALS['admin_form']['term_id'] ) ) return false;
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
acf_register_field_type( 'acf_field_delete_term' );

endif; // class_exists check

?>