<?php

if( ! class_exists('acf_field_role') ) :

class acf_field_role extends acf_field {
	
	
	/*
	*  initialize
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
		$this->name = 'role';
		$this->label = __("Role",FEA_NS);
        $this->category = 'User';
		$this->defaults = array(
			'multiple' 		=> 0,
			'allow_null' 	=> 0,
			'choices'		=> array(),
			'default_value'	=> '',
			'ui'			=> 0,
			'ajax'			=> 0,
			'placeholder'	=> '',
			'return_format'	=> 'value',
            'field_type'    => 'radio',
            'layout'        => 'vertical',
            'other_choice'  => 0,
		);
        add_filter( 'acf/load_field/type=select',  [ $this, 'load_role_field'], 2 );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      
		
	}

    function load_role_field( $field ){
        if( ! empty( $field['custom_user_role'] ) ){
            $field['type'] = 'role';
        }
        return $field;
    }


    
    function prepare_field( $field ){
        $all_roles = acf_frontend_get_user_roles();
        if( ! empty( $field['role_options'] ) ){
            foreach( $field['role_options'] as $slug ){
                $field['choices'][ $slug ] = $all_roles[ $slug ];
            }
        }else{
            $field['choices'] = $all_roles;
        }

        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        $user_id = explode( '_', $post_id );
        if( $user_id[0] == 'user' && ! empty( $user_id[1] ) ){
            $edit_user = get_userdata( $user_id[1] );
            if( isset( $edit_user->roles ) ){
                $value = $edit_user->roles[0];
            }
        }
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        $user_id = explode( '_', $post_id );
        if( $user_id[0] == 'user' && ! empty( $user_id[1] ) ){
            remove_action( 'acf/save_post', '_acf_do_save_post' );
            wp_update_user( ['ID' => $user_id[1], 'role' => $value ] );	
            add_action( 'acf/save_post', '_acf_do_save_post' );
        }
        return null;
    }

    public function update_value( $value, $post_id = false, $field = false ){
        return null;
    }

    function render_field( $field ){
        $field['type'] = $field['field_type'];
        acf_render_field( $field );

    }

    	/*
	*  render_field_settings()
	*
	*  Create extra options for your field. This is rendered when editing a field.
	*  The value of $field['name'] can be used (like bellow) to save extra data to the $field
	*
	*  @type	action
	*  @since	3.6
	*  @date	23/01/13
	*
	*  @param	$field	- an array holding all the field's data
	*/
	
	function render_field_settings( $field ) {	
		
		// field_type
		acf_render_field_setting( $field, array(
			'label'			=> __('Appearance',FEA_NS),
			'instructions'	=> __('Select the appearance of this field',FEA_NS),
			'type'			=> 'select',
			'name'			=> 'field_type',
			'optgroup'		=> true,
			'choices'		=> array(
                'radio' => __('Radio Buttons', FEA_NS),
                'select' => _x('Select', 'noun', FEA_NS)
			)
		));
    }

}

// initialize
acf_register_field_type( 'acf_field_role' );

endif;
	
?>