<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

$fields = array(	
    array(
        'key' => 'save_all_data',
        'label' =>  __( 'Submission Requirements', FEA_NS ),
        'type' => 'select',
        'instructions' => __( 'Data will not saved until these requirements are met.', FEA_NS ),
        'required' => 0,
        'conditional_logic' => 0,
        'choices' => array(
            'require_approval' => __( 'Admin Approval', FEA_NS ),
            'verify_email' => __( 'Email is Verified', FEA_NS ),	
        ),
        'allow_null' => 1,
        'multiple' => 1,
        'ui' => 1,
        'return_format' => 'value',
        'ajax' => 0,
        'placeholder' => __( 'None', FEA_NS ),
    ),	
);

return $fields;