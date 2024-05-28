<?php
namespace Frontend_WP\Classes;


use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Box_Shadow;
use ElementorPro\Modules\QueryControl\Module as Query_Module;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class PermissionsTab{
    
    public function register_permissions_section( $widget, $step = false ){
        $widget->start_controls_section( 'permissions_section', [
            'label' => __( 'Permissions', FEA_NS ),
            'tab'   => Controls_Manager::TAB_CONTENT,
        ] );
		$condition = [];
        if ( $step ) {
            $condition = [
                'field_type'                     => 'step',
                'overwrite_permissions_settings' => 'true',
            ];
        }
        $widget->add_control( 'not_allowed', [
            'label'       => __( 'No Permissions Message', FEA_NS ),
            'type'        => Controls_Manager::SELECT,
            'label_block' => true,
            'default'     => 'show_nothing',
            'options'     => [
				'show_nothing'   => __( 'None', FEA_NS ),
				'show_message'   => __( 'Message', FEA_NS ),
				'custom_content' => __( 'Custom Content', FEA_NS ),
			],
        ] );
        $condition['not_allowed'] = 'show_message';
        $widget->add_control( 'not_allowed_message', [
            'label'       => __( 'Message', FEA_NS ),
            'type'        => Controls_Manager::TEXTAREA,
            'label_block' => true,
            'rows'        => 4,
            'default'     => __( 'You do not have the proper permissions to view this form', FEA_NS ),
            'placeholder' => __( 'You do not have the proper permissions to view this form', FEA_NS ),
            'condition'   => $condition,
        ] );
        $condition['not_allowed'] = 'custom_content';
        $widget->add_control( 'not_allowed_content', [
            'label'       => __( 'Content', FEA_NS ),
            'type'        => Controls_Manager::WYSIWYG,
            'label_block' => true,
            'render_type' => 'none',
            'condition'   => $condition,
        ] );
        unset( $condition['not_allowed'] );
        $who_can_see = array(
            'logged_in'  => __( 'Only Logged In Users', FEA_NS ),
            'logged_out' => __( 'Only Logged Out', FEA_NS ),
            'all'        => __( 'All Users', FEA_NS ),
        );
        //get all user role choices
        $user_roles = acf_frontend_get_user_roles( array(), true );
        $user_caps = acf_frontend_get_user_caps( array(), true );

        $widget->add_control( 'who_can_see', [
            'label'       => __( 'Who Can See This...', FEA_NS ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'default'     => 'logged_in',
            'options'     => $who_can_see,
            'condition'   => $condition,
        ] );
        $condition['who_can_see'] = 'logged_in';
        $widget->add_control( 'by_role', [
            'label'       => __( 'Select By Role', FEA_NS ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'multiple'    => true,
            'default'     => ['administrator'],
            'options'     => $user_roles,
            'condition'   => $condition,
        ] );
        $widget->add_control( 'by_cap', [
            'label'       => __( 'Select By Capabilities', FEA_NS ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'multiple'    => true,
            'options'     => $user_caps,
            'condition'   => $condition,
        ] );
        if ( !class_exists( 'ElementorPro\\Modules\\QueryControl\\Module' ) ) {
            $widget->add_control( 'by_user_id', [
                'label'       => __( 'Select By User', FEA_NS ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( '18, 12, 11', FEA_NS ),
                'description' => __( 'Enter the a comma-seperated list of user ids', FEA_NS ),
                'condition'   => $condition,
            ] );
        } else {
            $widget->add_control( 'by_user_id', [
                'label'        => __( 'Select By User', FEA_NS ),
                'label_block'  => true,
                'type'         => Query_Module::QUERY_CONTROL_ID,
                'autocomplete' => [
                'object'  => Query_Module::QUERY_OBJECT_USER,
                'display' => 'detailed',
            ],
                'multiple'     => true,
                'condition'    => $condition,
            ] );
        }
        
        $condition['save_to_post'] = ['edit_post','duplicate_post','delete_post'];
        $widget->add_control( 'dynamic', [
            'label'       => __( 'Dynamic Permissions', FEA_NS ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'description' => 'Use a dynamic acf user field that returns a user ID to filter the form for that user dynamically. You may also select the post\'s author',
            'options'     => acf_frontend_user_id_fields(),
            'condition'   => $condition,
        ] );
        $condition['save_to_user'] = 'edit_user';
        $widget->add_control( 'dynamic_manager', [
            'label'       => __( 'Dynamic Permissions', FEA_NS ),
            'type'        => Controls_Manager::SELECT2,
            'label_block' => true,
            'options'     => [
            'manager' => __( 'User Manager', FEA_NS ),
        ],
            'condition'   => $condition,
        ] );
        $widget->add_control( 'wp_uploader', [
            'label'        => __( 'WP Media Library', FEA_NS ),
            'type'         => Controls_Manager::SWITCHER,
            'description'  => 'Whether to use the WordPress media library for file fields or just a basic upload button',
            'label_on'     => __( 'Yes', FEA_NS ),
            'label_off'    => __( 'No', FEA_NS ),
            'default'      => 'true',
            'return_value' => 'true',
        ] );
        $widget->add_control( 'media_privacy_note', [
            'label'           => __( '<h3>Media Privacy</h3>', FEA_NS ),
            'type'            => Controls_Manager::RAW_HTML,
            'raw'             => __( '<p align="left">Click <a target="_blank" href="' . admin_url( '?page='.FEA_PRE.'-settings&tab=uploads-privacy' ) . '">here</a> to limit the files displayed in the media library to the user who uploaded them.</p>', FEA_NS ),
            'content_classes' => 'media-privacy-note',
        ] );
        $widget->end_controls_section();
    }

	public function show_form( $settings ){
        global $post;

        $active_user = wp_get_current_user();
        if ( 'all' == $settings['who_can_see'] ) {
            $settings['display'] = true;
            return $settings;
        }
        if ( 'logged_out' == $settings['who_can_see'] ) {
            $settings['display'] = !is_user_logged_in();
            return $settings;
        }
        if ( 'logged_in' == $settings['who_can_see'] ) {            
            if ( !is_user_logged_in() ) {
                $settings['display'] = false;
                return $settings;
            } else {
                $by_role = $by_cap = $specific_user = $dynamic = false;
                $user_roles = $settings['by_role'];
                
               if ( $user_roles ) {
                    if ( is_array( $settings['by_role'] ) ) {
                        if ( count( array_intersect( $settings['by_role'], (array) $active_user->roles ) ) != false || in_array( 'all', $settings['by_role'] ) ) {
                            $by_role = true;
                        }
                    }
                } 

                if( ! empty( $settings['by_cap'] ) ){
                    foreach( $settings['by_cap'] as $cap ){
                        if( current_user_can( $cap ) ) $by_cap = true;
                    }
                }
                                
                if ( ! empty( $settings['by_user_id'] ) ) {
                    $user_ids = $settings['by_user_id'];
                    if ( ! is_array( $user_ids ) ) {
                        $user_ids = explode( ',', $user_ids );
                    }
                     if ( is_array( $user_ids ) ) {
                        if ( in_array( $active_user->ID, $user_ids ) ) {
                            $specific_user = true;
                        }
                    } 
                } 

                $save = isset( $settings['save_to_post'] ) ? $settings['save_to_post'] : '';
                if( $save == 'edit_post' || $save == 'delete_post' || $save == 'duplicate_post' ) $post_action = true;

                if( isset( $settings['dynamic'] ) && isset( $post_action ) ) {
                    $post_id = get_the_ID();
                    if( $settings['post_to_edit'] == 'select_post' && ! empty( $settings['post_select'] ) ){
                        $post_id = $settings['post_select'];
                    }elseif( $settings['post_to_edit'] == 'url_query' && isset( $_GET[ $settings['url_query_post'] ] ) ){
                        $post_id = $_GET[ $settings['url_query_post'] ];
                    }
                    
                    if( $settings['dynamic'] ) {                        
                        if( '[author]' == $settings['dynamic'] ) {
                            $author_id = get_post_field( 'post_author', $post_id );
                        }else{
                            $author_id = get_post_meta( $post_id, $settings['dynamic'], true );
                        }

                        if( ! is_numeric( $author_id ) ){
                            $authors = acf_decode_choices( $author_id );
                            if( in_array( $active_user->ID, $authors ) ) $dynamic = true;
                        }else{
                            if( $author_id == $active_user->ID ) $dynamic = true;
                        }
                    }                
                }
                $save = isset( $settings['save_to_user'] ) ? $settings['save_to_user'] : '';
                if( $save == 'edit_user' || $save == 'delete_user' ) $user_action = true;
                if( isset( $settings['dynamic_manager'] ) && isset( $user_action ) ){
                    if( $settings['user_to_edit'] == 'current_user' ){
                        $user_id = $active_user->ID; 
                    }elseif( $settings['user_to_edit'] == 'select_user' ){
                        $user_id = $settings['user_select'];
                    }elseif( $settings['user_to_edit'] == 'url_query' && isset( $_GET[ $settings['url_query_user'] ] ) ){
                        $user_id = $_GET[ $settings['url_query_user'] ];
                    }
        
                    if( $settings['dynamic_manager'] && isset( $user_id[1] ) ){
                        $manager_id = false;
                        
                        if( 'manager' == $settings['dynamic_manager'] ) {
                            $manager_id = get_user_meta( $user_id, 'frontend_admin_manager', true );
                        }else{
                            $manager_id = get_user_meta( $user_id, $settings['dynamic_manager'], true );
                        }
                        
                        if( $manager_id == $active_user->ID ) {
                            $dynamic = true;
                        }
                    }
                }
                                
                if ( $by_role || $by_cap || $specific_user || $dynamic ){
                    if( isset( $settings['email_verification'] ) && $settings['email_verification'] != 'all' ){
                        $required = $settings['email_verification'] == 'verified' ? 1 : 0;
                        $email_verified = get_user_meta( $active_user, 'frontend_admin_email_verified', true );
    
                        if( ( $email_verified == $required )  ){
                            $settings['display'] = true;
                        }else{
                            $settings['display'] = false;
                        }
                    }else{
                        $settings['display'] = true;
                    }
                    return $settings;
                }

                $settings['display'] = false;
                 
            
            }
        
        }
        return $settings;
    }
    
	public function __construct() {
		add_action( FEA_PREFIX.'/permissions_section', [ $this, 'register_permissions_section'] );
		add_filter( FEA_PREFIX.'/show_form', [ $this, 'show_form'], 10 );		
	}

}

new PermissionsTab();
