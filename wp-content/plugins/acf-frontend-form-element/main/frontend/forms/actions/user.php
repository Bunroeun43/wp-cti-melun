<?php
namespace Frontend_WP\Actions;

use Frontend_WP\Plugin;
use Frontend_WP;
use Frontend_WP\Classes\ActionBase;
use Frontend_WP\Widgets;
use Elementor\Controls_Manager;


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
if( ! class_exists( 'ActionUser' ) ) :

class ActionUser extends ActionBase {
	
	public function get_name() {
		return 'user';
	}

	public function get_label() {
		return __( 'User', FEA_NS );
	}

	public function get_fields_display( $form_field, $local_field ){

		switch( $form_field['field_type'] ){
			case 'username':
				$local_field['type'] = 'text';
				$local_field['disabled'] = isset( $form_field['allow_edit'] ) ? ! $form_field['allow_edit'] : 1;
				$local_field['custom_username'] = true;
			break;
			case 'password':
				$local_field['type'] = 'user_password';
				$local_field['edit_password'] = isset( $form_field['edit_password'] ) ? $form_field['edit_password'] : 'Edit Password';
				$local_field['cancel_edit_password'] = isset( $form_field['cancel_edit_password'] ) ? $form_field['cancel_edit_password'] : 'Cancel';
				$local_field['force_edit'] = isset( $form_field['force_edit_password'] ) ? $form_field['force_edit_password'] : 0;
				$local_field['password_strength'] = isset( $form_field['password_strength'] ) ? $form_field['password_strength'] : 3;
			break;				
			case 'confirm_password':
				$local_field['type'] = 'user_password_confirm';
			break;			
			case 'email':
				$local_field['type'] = 'user_email';
				if( ! empty( $form_field['set_as_username'] ) ){
					$local_field['set_as_username'] = true;
				}else{
					$local_field['set_as_username'] = false;
				}
			break;	
			case 'bio':
				$local_field['type'] = 'user_bio';
			break;
			case 'role':
				$local_field['type'] = 'role';
				if( isset( $form_field['role_field_options'] ) ){
					$local_field['role_options'] = $form_field['role_field_options'];
				}
				$local_field['field_type'] = isset( $form_field['role_appearance'] ) ? $form_field['role_appearance'] : 'radio';
				$local_field['layout'] = isset( $form_field['role_radio_layout'] ) ? $form_field['role_radio_layout'] : 'vertical';
				$local_field['default_value'] = isset( $form_field['default_role'] ) ? $form_field['default_role'] : 'subscriber';
			break;
			default:
				$local_field['type'] = $form_field['field_type'];
			
		}
		return $local_field;
	}
	
	public function get_default_fields( $form ){
		$default_fields = array(
			'username', 'user_email', 'user_password', 'first_name', 'last_name',			
		);
		$this->get_valid_defaults( $default_fields, $form );	
	}

	public function get_form_builder_options( $form ){
		if( $form['admin_form_type'] != 'general' ){
			$save_to = $form['admin_form_type'];
		}else{
			$save_to = $form['save_to_user'];
		}

		return array(		
			array(
				'key' => 'save_to_user',
				'field_label_hide' => 0,
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'choices' => array(            
					'edit_user' => __( 'Edit User', FEA_NS ),
					'new_user' => __( 'New User', FEA_NS ),
				),
				'value' => $save_to,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),	
			array(
				'key' => 'user_to_edit',
				'label' => __( 'User to Edit', FEA_NS ),
				'type' => 'select',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
					),
				),
				'choices' => array(
					'current_user' => __( 'Current User', FEA_NS ),
					'current_author' => __( 'Current Author', FEA_NS ),
					'post_author' => __( 'Form Post Author', FEA_NS ),
					'url_query' => __( 'URL Query', FEA_NS ),
					'select_user' => __( 'Specific User', FEA_NS ),
				),
				'default_value' => false,
				'allow_null' => 0,
				'multiple' => 0,
				'ui' => 0,
				'return_format' => 'value',
				'ajax' => 0,
				'placeholder' => '',
			),
			array(
				'key' => 'url_query_user',
				'label' => __( 'URL Query Key', FEA_NS ),
				'type' => 'text',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
						array(
							'field' => 'user_to_edit',
							'operator' => '==',
							'value' => 'url_query',
						),
					),
				),
				'placeholder' => '',
			),
			array(
				'key' => 'select_user',
				'label' => __( 'Specific User', FEA_NS ),
				'name' => 'select_user',
				'prefix' => 'form',
				'type' => 'user',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => array(
					array(
						array(
							'field' => 'save_to_user',
							'operator' => '==',
							'value' => 'edit_user',
						),
						array(
							'field' => 'user_to_edit',
							'operator' => '==',
							'value' => 'select_user',
						),
					),
				),
				'role' => '',
				'allow_null' => 0,
				'multiple' => 0,
				'return_format' => 'object',
				'ui' => 1,
			),
		);
	}

	public function register_settings_section( $widget ) {
		
					
		$widget->start_controls_section(
			'section_edit_user',
			[
				'label' => $this->get_label(),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->action_controls( $widget );
				
		$widget->end_controls_section();
	}
	
	
	public function action_controls( $widget, $step = false, $type = '' ){
		if( ! empty( $widget->form_defaults['save_to_user'] ) ){
			$type = $widget->form_defaults['save_to_user'];
		}

		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
		}
		$args = [
			'label' => __( 'User', FEA_NS ),
            'type'      => Controls_Manager::SELECT,
            'options'   => [				
				'edit_user' => __( 'Edit User', FEA_NS ),
				'new_user' => __( 'New User', FEA_NS ),
			],
            'default'   => 'edit_user',
        ];
		if( $step ){
			$condition = [
				'field_type' => 'step',
				'overwrite_settings' => 'true',
			];
			$args['condition'] = $condition;
		}else{
			$condition = array();
		}
		if( $type ){
			$args = [
				'type' => Controls_Manager::HIDDEN,
				'default' => $type,
			];
		}
	
		$widget->add_control( 'save_to_user', $args );
		
		$condition['save_to_user'] = [ 'edit_user', 'delete_user'];

		$widget->add_control(
			'user_to_edit',
			[
				'label' => __( 'Specific User', FEA_NS ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'current_user',
				'options' => [
					'current_user'  => __( 'Current User', FEA_NS ),
					'current_author'  => __( 'Current Author', FEA_NS ),
					'url_query' => __( 'URL Query', FEA_NS ),
					'select_user' => __( 'Specific User', FEA_NS ),
				],
				'condition' => $condition,
			]
		);
		$condition['user_to_edit'] = 'url_query';
		$widget->add_control(
			'url_query_user',
			[
				'label' => __( 'URL Query', FEA_NS ),
				'type' => Controls_Manager::TEXT,
				'placeholder' => __( 'user_id', FEA_NS ),
				'default' => __( 'user_id', FEA_NS ),
				'description' => __( 'Enter the URL query parameter containing the id of the user you want to edit', FEA_NS ),
				'condition' => $condition,
			]
		);	
		$condition['user_to_edit'] = 'select_user';
			$widget->add_control(
				'user_select',
				[
					'label' => __( 'User', FEA_NS ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', FEA_NS ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', FEA_NS ),
					'condition' => $condition,
				]
			);		

		unset( $condition['user_to_edit'] );
		$condition['save_to_user'] = 'new_user';

		$widget->add_control(
			'new_user_role',
			[
				'label' => __( 'New User Role', FEA_NS ),
				'type' => \Elementor\Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => 'subscriber',
				'options' => acf_frontend_get_user_roles( ['administrator'] ),
				'condition' => $condition,
			]
		);
		
		$widget->add_control(
			'hide_admin_bar',
			[
				'label' => __( 'Hide WordPress Admin Area?', FEA_NS ),
				'type' => \Elementor\Controls_Manager::SWITCHER,
				'label_on' => __( 'Hide', FEA_NS ),
				'label_off' => __( 'Show',FEA_NS ),
				'return_value' => 'true',
				'condition' => $condition,
			]
		);
		if( ! $step ){

			$widget->add_control(
				'login_user',
				[
					'label' => __( 'Log in as new user?', FEA_NS ),
					'type' => Controls_Manager::SWITCHER,
					'label_on' => __( 'Yes', FEA_NS ),
					'label_off' => __( 'No',FEA_NS ),
					'return_value' => 'true',
					'condition' => $condition,			
				]
			);			
		}
		
		$condition['save_to_user'] = ['new_user', 'edit_user'];

		$widget->add_control(
			'user_manager',
			[
				'label' => __( 'Managing User', FEA_NS ),
				'type' => \Elementor\Controls_Manager::SELECT,
				'default' => 'none',
				'options' => [
					'none' => __( 'No Manager',FEA_NS ),
					'current_user' => __( 'Current User',FEA_NS ),
					//'current_author' => __( 'Current Post Author',FEA_NS ),
					'select_user' => __( 'Specific User',FEA_NS ),
				],
				'description' => __( 'Who will be in charge of editing this user\'s data?', FEA_NS ),
				'condition' => $condition,
			]
		);
		$condition['user_manager'] = 'select_user';
			$widget->add_control(
				'manager_select',
				[
					'label' => __( 'User', FEA_NS ),
					'type' => Controls_Manager::TEXT,
					'placeholder' => __( '18', FEA_NS ),
					'default' => get_current_user_id(),
					'description' => __( 'Enter user id', FEA_NS ),
					'condition' => $condition,
				]
			);		
		
	}
		
	public function run( $form, $step = false ){	
		$record = $form['record'];
		if( empty( $record['_acf_user'] )|| empty( $record['fields']['user'] ) ) return $form;

		$user_id = wp_kses( $record['_acf_user'], 'strip' );

		// allow for custom save
		$user_id = apply_filters('acf/pre_save_user', $user_id, $form);
		
		$username_generated = false;
		$user_to_insert = [];			 
		$metas = array();

		$element_id = isset( $record['_acf_element_id'] ) ? '_' . $record['_acf_element_id'] : '';

		$core_fields = array(
			'username', 'user_password', 'user_email', 'display_name',  
		);

		if( ! empty( $record['fields']['user'] ) ){
			foreach( $record['fields']['user'] as $name => $field ){
				if( ! is_array( $field ) ) continue;

				$field_type = $field['type'];
				$value = $field['_input'];
				
				if( ! in_array( $field_type, $core_fields ) ){
					$metas[$field['key']] = $field; 
					continue;
				}

				if( ! empty( $field['save_prepend'] ) ) $value = $field['prepend'] . $value;
				if( ! empty( $field['save_append'] ) ) $value .= $field['append'];
				if( ! empty( $field['set_as_username'] ) ) $user_to_insert[ 'user_login' ] = $value;

				if( is_string( $field['default_value'] ) && strpos( $field['default_value'], '[' ) !== false ){
					$dynamic_value = fea_instance()->dynamic_values->get_dynamic_values( $field['default_value'] ); 
					if( $dynamic_value ) $value = $dynamic_value;
				} 

				switch( $field_type ){
					case 'username':
						$user_to_insert['user_login'] = $value;
						if( empty( $user_to_insert['user_nicename'] ) ){
							$user_to_insert['user_nicename'] = mb_substr( $value, 0, 50 );
						}
					break;
					case 'user_password':
						if( ( is_numeric( $user_id ) && empty( $record['edit_user_password'] ) ) || empty( $value ) ) continue 2;
						$user_to_insert['user_pass'] = $value;
					break;
					default: 
						$user_to_insert[$field_type] = $value;
				}
			
			}
		}
		global $wpdb;
		
		if( $user_id == 'add_user' ){
			if( empty( $user_to_insert['user_login'] ) ){ 
				$prefix = sanitize_title( $form['username_prefix'] );
				$suffix = sanitize_title( $form['username_suffix'] );
				$user_to_insert['user_login'] = $this->generate_username( $prefix, $suffix );
			}

			$user_to_insert['user_registered'] = gmdate( 'Y-m-d H:i:s' );

			if( empty( $user_to_insert['user_pass'] ) ){ 
				$user_to_insert['user_pass'] = wp_generate_password();
			}

			$wpdb->insert( $wpdb->users, $user_to_insert );
			$user_id = $wpdb->insert_id; 

			if ( is_wp_error( $user_id ) ) return $form;
			update_user_meta( $user_id, 'hide_admin_area', $form['hide_admin_bar'] );
			if( empty( $user_to_insert['role'] ) ){ 
				$user = get_user_by( 'id', $user_id );
				$user->set_role( $form['new_user_role'] );
			}	
			do_action( 'user_register', $user_id, $user_to_insert );
		}else{
			$old_user_data = get_userdata( $user_id );

			if( isset( $user_to_insert['user_pass'] ) && get_current_user_id() == $user_id ){
                $_POST['log_back_in'] = array( $user_id, $value );
			}
			
			$wpdb->update( $wpdb->users, $user_to_insert, array( 'ID' => $user_id ) );
			
			if( isset( $user_to_insert['user_pass'] ) ){
				clean_user_cache( $user_id );
			}
			do_action( 'profile_update', $user_id, $old_user_data, $user_to_insert );

		}			

		if( isset( $form['user_manager'] ) ){
			update_user_meta( $user_id, 'frontend_admin_manager', $form['user_manager'] );
		}
	
		if( isset( $form['hide_admin_bar'] ) ){
			update_user_meta( $user_id, 'show_admin_bar_front', ! $form['hide_admin_bar'] );
		}

		if( ! empty( $metas ) ){
			foreach( $metas as $meta ){
				acf_update_value( $meta['_input'], 'user_'.$user_id, $meta );
			}
		}

		$form['record']['user'] = $user_id;

		if( ! empty( $form['login_user'] ) ){
			$user = get_user_by( 'id', $user_id );
			if( !empty( $user->user_login ) ){
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
			}
		}

		do_action( FEA_PREFIX.'/save_user', $form, $user_id );

		return $form;
	}  
	
	public function generate_username( $prefix = '', $suffix = '' ) {	
		static $i;
		if ( null === $i ) {
			$i = 1;
		} else {
			$i ++;
		}
		$new_username = sprintf( '%s%s%s', $prefix, $i, $suffix );
		if ( ! username_exists( $new_username ) ) {
			return $new_username;
		} else {
			return $this->generate_username( $prefix, $suffix );
		}
	}
	

	public function __construct(){
		add_filter( 'acf_frontend/save_form', array( $this, 'save_form' ), 4 );
	}
}
fea_instance()->local_actions['user'] = new ActionUser();

endif;	