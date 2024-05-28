<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'acf_frontend_relationship_field' ) ) {
    class acf_frontend_relationship_field
    {
        public function add_edit_field( $field )
        {
            $users = get_users();
            $label = __( 'Dynamic', FEA_NS );
            $user_choices = [
                $label => [
                'current_user' => __( 'Current User', FEA_NS ),
            ],
            ];
            // Append.
            
            if ( $users ) {
                $user_label = __( 'Users', FEA_NS );
                $user_choices[$user_label] = [];
                foreach ( $users as $user ) {
                    $user_text = $user->user_login;
                    // Add name.
                    
                    if ( $user->first_name && $user->last_name ) {
                        $user_text .= " ({$user->first_name} {$user->last_name})";
                    } elseif ( $user->first_name ) {
                        $user_text .= " ({$user->first_name})";
                    }
                    
                    $user_choices[$user_label][$user->ID] = $user_text;
                }
            }
            
            acf_render_field_setting( $field, array(
                'label'        => __( 'Filter by Post Author', FEA_NS ),
                'instructions' => '',
                'type'         => 'select',
                'name'         => 'post_author',
                'choices'      => $user_choices,
                'multiple'     => 1,
                'ui'           => 1,
                'allow_null'   => 1,
                'placeholder'  => __( "All Users", FEA_NS ),
            ) );
            acf_render_field_setting( $field, array(
                'label'        => __( 'Add and Edit Posts' ),
                'instructions' => __( 'Allow posts to be created and edited whilst editing', FEA_NS ),
                'name'         => 'add_edit_post',
                'type'         => 'true_false',
                'ui'           => 1,
            ) );
            acf_render_field_setting( $field, array(
                'label'         => __( 'Add Post Button' ),
                'name'          => 'add_post_button',
                'type'          => 'text',
                'default_value' => __( 'Add Post' ),
                'placeholder'   => __( 'Add Post' ),
                'conditions'    => [ [
                'field'    => 'add_edit_post',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ) );
            acf_render_field_setting( $field, array(
                'label'         => __( 'Form Container Width' ),
                'name'          => 'form_width',
                'type'          => 'number',
                'prepend'       => 'px',
                'default_value' => 600,
                'placeholder'   => 600,
                'conditions'    => [ [
                'field'    => 'add_edit_post',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ) );
            $templates_options = [
                'none'    => __( 'Default', FEA_NS ),
                'current' => __( 'Current Form/Field Group', FEA_NS ),
            ];
            $form_templates = get_posts( array(
                'post_type'   => 'admin_form',
                'numberposts' => '-1',
            ) );
            
            if ( $form_templates ) {
                $form_title = __( 'Forms', FEA_NS );
                foreach ( $form_templates as $template ) {
                    $templates_options[$form_title][$template->ID] = esc_html( $template->post_title );
                }
            }
            
            $field_group_templates = get_posts( array(
                'post_type'   => 'acf-field-group',
                'numberposts' => '-1',
            ) );
            
            if ( $field_group_templates ) {
                $field_group_title = __( 'Field Groups', FEA_NS );
                foreach ( $field_group_templates as $template ) {
                    $templates_options[$field_group_title][$template->ID] = esc_html( $template->post_title );
                }
            }
            
            acf_render_field_setting( $field, array(
                'label'        => __( 'Forms/Field Groups' ),
                'name'         => 'post_form_template',
                'instructions' => '<div>' . __( 'Select a existing field group or form or the current field group or form', FEA_NS ) . '</div>',
                'type'         => 'select',
                'choices'      => $templates_options,
                'ui'           => 1,
                'conditions'   => [ [
                'field'    => 'add_edit_post',
                'operator' => '==',
                'value'    => '1',
            ] ],
            ) );
        }
        
        public function load_relationship_field( $field )
        {
            if ( !isset( $field['add_edit_post'] ) ) {
                return $field;
            }
            if ( isset( $field['form_width'] ) ) {
                $field['wrapper']['data-form_width'] = $field['form_width'];
            }
            return $field;
        }
        
        public function edit_post_button(
            $title,
            $post,
            $field,
            $post_id
        )
        {
            if ( isset( $field['add_edit_post'] ) && $field['add_edit_post'] == 1 ) {
                $title .= '<a href="#" class="acf-icon -pencil small dark edit-rel-post render-form" data-name="edit_item"></a>';
            }
            return $title;
        }
        
        public function add_post_button( $field )
        {
            
            if ( isset( $field['add_edit_post'] ) && $field['add_edit_post'] == 1 ) {
                $post_types = acf_get_pretty_post_types();
                $add_post_button = ( $field['add_post_button'] ? $field['add_post_button'] : __( 'Add Post', FEA_NS ) );
                ?>
				<div class="margin-top-10 acf-actions">
					<a class="add-rel-post acf-button button button-primary render-form" href="#" data-name="add_item"><?php 
                echo  $add_post_button ;
                ?></a>
				</div>
				
			<?php 
            }
        
        }
        
        public function relationship_query( $args, $field, $post_id )
        {
            if ( !isset( $field['post_author'] ) ) {
                return $args;
            }
            $post_author = acf_get_array( $field['post_author'] );
            
            if ( in_array( 'current_user', $post_author ) ) {
                $key = array_search( 'current_user', $post_author );
                $post_author[$key] = get_current_user_id();
            }
            
            $args['author__in'] = $post_author;
            return $args;
        }
        
        public function __construct()
        {
            $fields = array( 'relationship' );
            foreach ( $fields as $field ) {
                add_filter( "acf/load_field/type={$field}", [ $this, 'load_relationship_field' ] );
                add_action( "acf/render_field_settings/type={$field}", [ $this, 'add_edit_field' ] );
                add_action( "acf/render_field/type={$field}", [ $this, 'add_post_button' ], 10 );
                add_filter(
                    "acf/fields/{$field}/result",
                    [ $this, 'edit_post_button' ],
                    10,
                    4
                );
                add_filter(
                    "acf/fields/{$field}/query",
                    [ $this, 'relationship_query' ],
                    10,
                    3
                );
            }
        }
    
    }
    new acf_frontend_relationship_field();
}
