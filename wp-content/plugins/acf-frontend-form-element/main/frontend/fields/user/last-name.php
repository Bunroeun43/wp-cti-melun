<?php

if( ! class_exists('acf_field_last_name') ) :

class acf_field_last_name extends acf_field_text {
	
	
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
		$this->name = 'last_name';
		$this->label = __("Last Name",FEA_NS);
        $this->category = 'User';
		$this->defaults = array(
			'default_value'	=> '',
			'maxlength'		=> '',
			'placeholder'	=> '',
			'prepend'		=> '',
			'append'		=> ''
		);
        add_filter( 'acf/load_field/type=text',  [ $this, 'load_last_name_field'] );
        add_filter( 'acf/update_value/type=' . $this->name,  [ $this, 'pre_update_value'], 9, 3 );      

    }
    
    function load_last_name_field( $field ){
        if( ! empty( $field['custom_last_name'] ) ){
            $field['type'] = 'last_name';
        }
        return $field;
    }

    public function load_value( $value, $post_id = false, $field = false ){
        $user = explode( 'user_', $post_id ); 
        if( empty( $user[1] ) ){
            return $value;
        }else{
            $user_id = $user[1]; 
            $edit_user = get_user_by( 'ID', $user_id );
            if( $edit_user instanceof WP_User ){
                $value = $edit_user->last_name;
            }        
        }
        return $value;
    }

    function load_field( $field ){
      $field['name'] = $field['type'];
      return $field;
}
function pre_update_value( $value, $post_id = false, $field = false ){
        if( $field['name'] == 'last_name' ) return $value;

        $user = explode( 'user_', $post_id ); 
        if( ! empty( $user[1] ) ){
            $user_id = $user[1]; 
            remove_action( 'acf/save_post', '_acf_do_save_post' );
            wp_update_user( array( 'ID' => $user_id, 'last_name' => $value ) );
            add_action( 'acf/save_post', '_acf_do_save_post' );
        }
        return null;
    }

    function render_field( $field ){
        $field['type'] = 'text';
        parent::render_field( $field );
    }

   
}

// initialize
acf_register_field_type( 'acf_field_last_name' );

endif;
	
?>