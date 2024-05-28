<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

return array(	
    array(
        'key' => 'no_kses',
        'label' => __( 'Allow Unfiltered HTML', FEA_NS ),
        'type' => 'true_false',
        'instructions' => '',
        'required' => 0,
        'ui' => 1,
        'wrapper' => array(
            'width' => '50',
            'class' =>'',
            'id' => ''
        )
    ),
    array(
        'key' => 'wp_uploader',
        'label' => __( 'WP Media Library', FEA_NS ),
        'type' => 'true_false',
        'instructions' => __( 'Whether to use the WordPress media library for file fields or just a basic upload button', FEA_NS ),
        'required' => 0,
        'ui' => 1,
        'default_value' => 1,
        'wrapper' => array(
            'width' => '50',
            'class' =>'',
            'id' => ''
        )
    ),
    array(
        'key' => 'not_allowed',
        'label' => __( 'No Permissions Message', FEA_NS ),
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'choices' => array(
            'show_nothing'   => __( 'None', FEA_NS ),
			'show_message'   => __( 'Message', FEA_NS ),
			'custom_content' => __( 'Custom Content', FEA_NS ),
        ),
    ),	
    array(
        'key' => 'not_allowed_message',
        'label' => __( 'Message', FEA_NS ),
        'type' => 'textarea',
        'instructions' => '',
        'required' => 0,
        'rows' => 3,
        'placeholder' => __( 'You do not have the proper permissions to view this form', FEA_NS ),
        'default_value' => __( 'You do not have the proper permissions to view this form', FEA_NS ),
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'not_allowed',
                    'operator' => '==',
                    'value' => 'show_message',
                ),
            ),
        ),
    ),	
    array(
        'key' => 'not_allowed_content',
        'label' => __( 'Content', FEA_NS ),
        'type' => 'wysiwyg',
        'instructions' => '',
        'required' => 0,
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'not_allowed',
                    'operator' => '==',
                    'value' => 'custom_content',
                ),
            ),
        ),
    ),	
    array(
        'key' => 'who_can_see',
        'label' => __( 'Who Can See This...', FEA_NS ),
        'type' => 'select',
        'instructions' => '',
        'required' => 0,
        'choices' => array(
            'logged_in'  => __( 'Only Logged In Users', FEA_NS ),
            'logged_out' => __( 'Only Logged Out', FEA_NS ),
            'all'        => __( 'All Users', FEA_NS ),
        ),
    ),
    array(
        'key' => 'email_verification',
        'label' => __( 'Email Address', FEA_NS ),
        'type'  => 'select',
        'required' => 0,
        'choices' => array(
            'all'        => __( 'All', FEA_NS ),
            'verified'  => __( 'Verified', FEA_NS ),
            'unverified' => __( 'Unverified', FEA_NS ),
        ),
        'instructions' => 'Only show to users who verified their email address or only to those who haven\'t.',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
    ),
    array(
        'key' => 'by_role',
        'label' => __( 'Select By Role', FEA_NS ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'default_value' => array( 'administrator' ),
        'multiple' => 1,
        'ui' => 1,
        'choices' => acf_frontend_get_user_roles( array(), true ),
    ),
    array(
        'key' => 'by_user_id',
        'label' => __( 'Select By User', FEA_NS ),
        'type' => 'user',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'allow_null' => 0,
        'multiple' => 1,
        'ajax' => 1,
        'ui' => 1,
        'return_format' => 'id',
    ), 
    array(
        'key' => 'dynamic',
        'label' => __( 'Dynamic Permissions', FEA_NS ),
        'type' => 'select',
        'instructions' => '',
        'conditional_logic' => array(
            array(
                array(
                    'field' => 'who_can_see',
                    'operator' => '==',
                    'value' => 'logged_in',
                ),
            ),
        ),
        'choices' => acf_frontend_user_id_fields(),
        'allow_null' => 1,
    ),
);
