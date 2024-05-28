<?php

namespace Frontend_WP\Classes;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly.
}


if ( !class_exists( 'Frontend_WP\\Classes\\Display_Form' ) ) {
    class Display_Form
    {
        public function get_form_data( $form_args )
        {
            global  $post ;
            $active_user = wp_get_current_user();
            $objects = array();
            global  $frontend_admin_success_returned ;
            if ( isset( $frontend_admin_success_returned ) ) {
                if ( isset( $frontend_admin_success_returned['edit_data'] ) ) {
                    $objects = $frontend_admin_success_returned['edit_data'];
                }
            }
            /* 		if( 'new_comment' == $form_args['main_action'] ){
            				$form_args['post_id'] = 'new_comment';
            				if( $form_args['comment_parent_post'] == 'current_post' ){
            					$comment_parent_post = $post->ID;
            				}else{
            					$comment_parent_post = $form_args['select_parent_post'];
            				}
            				$form_args['html_after_fields'] .= '<input type="hidden" value="' . $comment_parent_post . '" name="frontend_admin_parent_post"/><input type="hidden" value="0" name="frontend_admin_parent_comment"/>';
            			} */
            global  $wp ;
            $form_args['hidden_fields'] = [
                'screen_id'   => home_url( $wp->request ),
                'referer_url' => wp_get_referer(),
                'element_id'  => $form_args['id'],
            ];
            //print_r
            $form_args['url_query'] = array();
            if ( !empty($form['options']) ) {
                return $form_args;
            }
            if ( !empty($form_args['save_to_post']) && empty($form_args['post_id']) ) {
                switch ( $form_args['save_to_post'] ) {
                    case 'new_post':
                        
                        if ( isset( $objects['post'] ) && frontend_admin_can_edit_post( $objects['post'], $form_args ) ) {
                            $form_args['post_id'] = $objects['post'];
                            $form_args['save_to_post'] = 'edit_post';
                        } else {
                            $form_args['post_id'] = 'add_post';
                        }
                        
                        
                        if ( !empty($form_args['new_post_terms']) ) {
                            if ( $form_args['new_post_terms'] == 'select_terms' ) {
                                $form_args['post_terms'] = $form_args['new_terms_select'];
                            }
                            if ( $form_args['new_post_terms'] == 'current_term' ) {
                                $form_args['post_terms'] = get_queried_object()->term_id;
                            }
                        }
                        
                        break;
                    case 'edit_post':
                    case 'duplicate_post':
                    case 'delete_post':
                        
                        if ( $form_args['post_to_edit'] == 'select_post' && empty($form_args['select_post']) ) {
                            $form_args['post_id'] = $form_args['post_select'];
                        } elseif ( $form_args['post_to_edit'] == 'url_query' ) {
                            
                            if ( isset( $_GET[$form_args['url_query_post']] ) ) {
                                $form_args['post_id'] = $_GET[$form_args['url_query_post']];
                                $form_args['url_query'][$form_args['url_query_post']] = $form_args['post_id'];
                            }
                        
                        } elseif ( isset( $post->ID ) ) {
                            $form_args['post_id'] = $post->ID;
                        }
                        
                        if ( empty($form_args['post_id']) ) {
                            $form_args['post_id'] = 'none';
                        }
                        break;
                }
            }
            if ( !empty($form_args['save_to_user']) && empty($form_args['user_id']) ) {
                switch ( $form_args['save_to_user'] ) {
                    case 'new_user':
                        
                        if ( isset( $objects['user'] ) && frontend_admin_can_edit_user( $objects['user'], $form_args ) ) {
                            $form_args['user_id'] = $objects['user'];
                            $form_args['save_to_user'] = 'edit_user';
                        } else {
                            $form_args['user_id'] = 'add_user';
                        }
                        
                        break;
                    case 'edit_user':
                    case 'delete_user':
                        
                        if ( $form_args['user_to_edit'] == 'current_author' ) {
                            
                            if ( is_author() ) {
                                $author_id = get_queried_object_id();
                            } else {
                                $author_id = get_the_author_meta( 'ID' );
                            }
                            
                            $form_args['user_id'] = $author_id;
                        }
                        
                        if ( $form_args['user_to_edit'] == 'select_user' ) {
                            $form_args['user_id'] = $form_args['user_select'];
                        }
                        if ( $form_args['user_to_edit'] == 'url_query' ) {
                            
                            if ( isset( $_GET[$form_args['url_query_user']] ) ) {
                                $form_args['user_id'] = $_GET[$form_args['url_query_user']];
                                $form_args['url_query'][$form_args['url_query_user']] = $form_args['user_id'];
                            }
                        
                        }
                        if ( $form_args['user_to_edit'] == 'current_user' ) {
                            $form_args['user_id'] = get_current_user_id();
                        }
                        if ( empty($form_args['user_id']) || !acf_frontend_user_exists( $form_args['user_id'] ) ) {
                            $form_args['user_id'] = 'none';
                        }
                        break;
                }
            }
            if ( !empty($form_args['save_to_product']) && empty($form_args['product_id']) ) {
                switch ( $form_args['save_to_product'] ) {
                    case 'new_product':
                        
                        if ( isset( $objects['product'] ) && frontend_admin_can_edit_post( $objects['product'], $form_args ) ) {
                            $form_args['product_id'] = $objects['product'];
                            $form_args['save_to_product'] = 'edit_product';
                        } else {
                            $status = 'no_change';
                            if ( isset( $form_args['new_product_status'] ) ) {
                                $status = $form_args['new_product_status'];
                            }
                            if ( $status == 'no_change' ) {
                                $status = 'publish';
                            }
                            $form_args['product_id'] = 'add_product';
                            
                            if ( !empty($form_args['new_product_terms']) ) {
                                if ( $form_args['new_product_terms'] == 'select_terms' ) {
                                    $form_args['product_terms'] = $form_args['new_product_terms_select'];
                                }
                                if ( $form_args['new_product_terms'] == 'current_term' ) {
                                    $form_args['product_terms'] = get_queried_object()->term_id;
                                }
                            }
                        
                        }
                        
                        break;
                    case 'edit_product':
                    case 'duplicate_product':
                    case 'delete_product':
                        
                        if ( $form_args['product_to_edit'] == 'select_product' ) {
                            $form_args['product_id'] = $form_args['product_select'];
                        } elseif ( $form_args['product_to_edit'] == 'url_query' ) {
                            
                            if ( isset( $_GET[$form_args['url_query_product']] ) ) {
                                $form_args['product_id'] = $_GET[$form_args['url_query_product']];
                                $form_args['url_query'][$form_args['url_query_product']] = $form_args['product_id'];
                            }
                        
                        } elseif ( get_post_type( $post ) == 'product' ) {
                            $form_args['product_id'] = $post->ID;
                        }
                        
                        if ( empty($form_args['product_id']) || !get_post_status( $form_args['product_id'] ) ) {
                            $form_args['product_id'] = 'none';
                        }
                        break;
                }
            }
            if ( !empty($form_args['save_to_term']) && empty($form_args['term_id']) ) {
                switch ( $form_args['save_to_term'] ) {
                    case 'new_term':
                        $form_args['term_id'] = 'add_term';
                        $form_args['hidden_fields']['taxonomy_type'] = $form_args['new_term_taxonomy'];
                        break;
                    case 'edit_term':
                    case 'delete_term':
                        
                        if ( $form_args['term_to_edit'] == 'select_term' ) {
                            $term_id = $form_args['term_select'];
                        } elseif ( $form_args['term_to_edit'] == 'url_query' ) {
                            
                            if ( isset( $_GET[$form_args['url_query_term']] ) ) {
                                $term_id = $_GET[$form_args['url_query_term']];
                                echo  term_exists( $term_id ) ;
                                $form_args['url_query'][$form_args['url_query_term']] = $term_id;
                            }
                        
                        } else {
                            $term_obj = get_queried_object();
                            
                            if ( !empty($term_obj->term_id) ) {
                                $term_id = $term_obj->term_id;
                            } else {
                                $term_id = 1;
                            }
                        
                        }
                        
                        
                        if ( empty($term_id) || !isset( get_term( $term_id )->term_id ) ) {
                            $form_args['term_id'] = 'none';
                        } else {
                            if ( !isset( $term_obj ) ) {
                                $term_obj = get_term( $term_id );
                            }
                            $form_args['hidden_fields']['taxonomy_type'] = $term_obj->taxonomy;
                            $form_args['term_id'] = $term_id;
                        }
                        
                        break;
                }
            }
            return $form_args;
        }
        
        public function get_form( $key )
        {
            
            if ( is_numeric( $key ) && get_post_type( $key ) == 'admin_form' ) {
                $form = get_post( $key );
                return $this->get_form_args( $form );
            }
            
            $args = array(
                'post_type'      => 'admin_form',
                'posts_per_page' => '1',
                'name'           => $key,
                'post_status'    => 'any',
            );
            $form = get_posts( $args );
            if ( $form ) {
                return $this->get_form_args( $form[0] );
            }
            return array();
        }
        
        public function get_form_args( $form )
        {
            // Get form object if $form is the ID
            if ( is_numeric( $form ) ) {
                $form = get_post( $form );
            }
            // Make sure we have a post and that it's a form
            if ( empty($form) || 'admin_form' != $form->post_type ) {
                return false;
            }
            $form_args = ( $form->post_content ? maybe_unserialize( $form->post_content ) : array() );
            $form_args['id'] = str_replace( 'form_', '', $form->post_name );
            $form_args['fields'] = $this->get_form_fields( $form->ID );
            return $form_args;
        }
        
        public function get_form_fields( $form )
        {
            $fields = array();
            $fields_args = array(
                'post_type'      => 'acf-field',
                'posts_per_page' => '-1',
                'post_parent'    => $form,
                'orderby'        => 'menu_order',
                'order'          => 'ASC',
            );
            foreach ( get_posts( $fields_args ) as $field ) {
                $fields[] = $field->post_name;
            }
            return $fields;
        }
        
        public function get_redirect_url( $form )
        {
            $redirect_url = '';
            $form['message_location'] = 'other';
            switch ( $form['redirect'] ) {
                case 'custom_url':
                    
                    if ( is_array( $form['custom_url'] ) ) {
                        $redirect_url = $form['custom_url']['url'];
                    } else {
                        $redirect_url = $form['custom_url'];
                    }
                    
                    unset( $form['custom_url'] );
                    break;
                case 'current':
                    global  $wp ;
                    $redirect_url = home_url( $wp->request );
                    //$form_args['reload_current'] = true;
                    $form['message_location'] = 'current';
                    break;
                case 'referer_url':
                    $redirect_url = home_url( add_query_arg( NULL, NULL ) );
                    if ( wp_get_referer() ) {
                        $redirect_url = wp_get_referer();
                    }
                    break;
                case 'post_url':
                    $redirect_url = '%post_url%';
                    break;
            }
            unset( $form['redirect'] );
            $form['return'] = $redirect_url;
            return $form;
        }
        
        public function validate_form( $form )
        {
            if ( !is_array( $form ) ) {
                $form = $this->get_form( $form );
            }
            /* if( empty( $form['no_cookies'] ) && empty( $form['no_record'] ) && ! frontend_admin_edit_mode() ){
            				$form = $this->get_record( $form );
            			}  */
            $form_class = ( empty($form['form_attributes']['class']) ? 'frontend-form -submit' : 'frontend-form -submit ' . $form['form_attributes']['class'] );
            // defaults
            $form = acf_frontend_parse_args( $form, array(
                'id'                    => 'acf-form',
                'parent_form'           => '',
                'main_action'           => '',
                'custom_fields_save'    => '',
                'fields'                => false,
                'field_objects'         => false,
                'form'                  => true,
                'form_title'            => '',
                'show_form_title'       => true,
                'form_attributes'       => array(
                'class'      => $form_class,
                'action'     => '',
                'method'     => 'post',
                'novalidate' => 'novalidate',
            ),
                'saved_drafts'          => array(),
                'saved_revisions'       => array(),
                'save_progress'         => '',
                'show_delete_button'    => false,
                'message_location'      => 'other',
                'hidden_fields'         => array(),
                'submit_value'          => __( "Update", FEA_NS ),
                'label_placement'       => 'top',
                'instruction_placement' => 'label',
                'field_el'              => 'div',
                'uploader'              => 'wp',
                'honeypot'              => true,
                'show_update_message'   => true,
                'update_message'        => __( "Post updated", FEA_NS ),
                'html_updated_message'  => '<div class="frontend-admin-message"><div class="acf-notice -success acf-success-message -dismiss"><p class="success-msg">%s</p><span class="frontend-admin-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div></div>',
                'error_message'         => __( 'There has been an error.', FEA_NS ),
                'kses'                  => ( isset( $form['no_kses'] ) ? !$form['no_kses'] : true ),
                'new_post_type'         => 'post',
                'new_post_status'       => 'publish',
                'redirect'              => 'current',
                'custom_url'            => '',
            ) );
            if ( empty($form['return']) ) {
                $form = $this->get_redirect_url( $form );
            }
            $form = $this->get_form_data( $form );
            
            if ( !empty($form['wp_uploader']) ) {
                $form['uploader'] = 'wp';
            } else {
                $form['uploader'] = 'basic';
            }
            
            // filter
            $form = apply_filters( 'acf_frontend/validate_form', $form );
            // return
            return $form;
        }
        
        public function render_submit_button( $form, $index, $hidden = false )
        {
            
            if ( isset( $form['steps'] ) ) {
                $submit_attrs = array(
                    'class'     => 'acf-field',
                    'data-step' => $index,
                );
                
                if ( $hidden ) {
                    $submit_attrs['class'] .= ' frontend-admin-hidden';
                } else {
                    $submit_attrs['data-current'] = 1;
                }
                
                ?>
				<div <?php 
                echo  acf_frontend_esc_attrs( $submit_attrs ) ;
                ?>>
			<?php 
                echo  $this->multi_step_buttons( $form, $index ) ;
                ?>
				</div>
			<?php 
            } else {
                echo  '<div class="fea-submit-buttons"><button type="submit" class="fea-submit-button button" data-state="publish">' . $form['submit_value'] . '</button></div>' ;
            }
        
        }
        
        public function multi_step_buttons( $args, $current_step = false )
        {
            if ( !$current_step ) {
                $current_step = $args['step_index'];
            }
            $prev_button = $buttons_class = '';
            $form_step = $args['steps'][$current_step];
            if ( $current_step > 1 ) {
                
                if ( $form_step['prev_button_text'] ) {
                    $prev_step = $current_step - 1;
                    $prev_button .= '<button type="button" name="prev_step" class="prev-button change-step fea-submit-button button" data-step="' . $prev_step . '">' . $form_step['prev_button_text'] . '</button> ';
                    $buttons_class = 'frontend-admin-multi-buttons-align';
                }
            
            }
            $next_button = '<button type="submit" class="fea-submit-button button" data-state="publish">' . $form_step['next_button_text'] . '</button>';
            $submit_button = '<div class="fea-submit-buttons ' . $buttons_class . '">' . $prev_button . $next_button . '</div>';
            return $submit_button;
        }
        
        public function step_tabs( $form, $current_step )
        {
            $total_steps = count( $form['steps'] );
            $editor = frontend_admin_edit_mode();
            $current_post = get_post();
            $active_user = wp_get_current_user();
            $screens = [ 'desktop', 'tablet', 'phone' ];
            $tabs_responsive = '';
            if ( isset( $form['steps_tabs_display'] ) ) {
                foreach ( $screens as $screen ) {
                    if ( !in_array( $screen, $form['steps_tabs_display'] ) ) {
                        $tabs_responsive .= 'frontend-admin-hidden-' . $screen . ' ';
                    }
                }
            }
            $counter_responsive = '';
            if ( isset( $form['steps_counter_display'] ) ) {
                foreach ( $screens as $screen => $label ) {
                    if ( !in_array( $screen, $form['steps_counter_display'] ) ) {
                        $counter_responsive .= 'frontend-admin-hidden-' . $label . ' ';
                    }
                }
            }
            if ( in_array( 'counter', $form['steps_display'] ) ) {
                echo  '<div class="' . $counter_responsive . 'step-count"><p>' . $form['counter_prefix'] . '<span class="current-step">' . $current_step . '</span>/' . $total_steps . $form['counter_suffix'] . '</p></div>' ;
            }
            
            if ( in_array( 'tabs', $form['steps_display'] ) ) {
                echo  '<div class="frontend-admin-tabs frontend-admin-tabs-view-' . $form['tabs_align'] . '"><div class="frontend-admin-tabs-wrapper ' . $tabs_responsive . '">' ;
                $steps = $form['steps'];
                foreach ( $steps as $step_count => $form_step ) {
                    $active = '';
                    if ( $step_count == $current_step ) {
                        $active = 'active';
                    }
                    $change_form = '';
                    if ( $editor || $form['tab_links'] ) {
                        $change_form = ' change-step';
                    }
                    $step_title = ( $form_step['form_title'] ? $form_step['form_title'] : $form['form_title'] );
                    if ( $form_step['step_tab_text'] ) {
                        $step_title = $form_step['step_tab_text'];
                    }
                    
                    if ( $step_title == '' ) {
                        $step_title = __( 'Step', FEA_NS ) . ' ' . $step_count;
                    } else {
                        if ( $form['step_number'] ) {
                            $step_title = $step_count . '. ' . $step_title;
                        }
                    }
                    
                    echo  '<a class="form-tab ' . $active . $change_form . '" data-step="' . $step_count . '"><p class="step-name">' . $step_title . '</p></a>' ;
                }
                echo  '</div>' ;
            }
            
            echo  '<div class="form-steps frontend-admin-content-wrapper">' ;
        }
        
        public function form_set_data( $form = array() )
        {
            // defaults
            $data = wp_parse_args( $form['hidden_fields'], array(
                'screen'     => 'fea_form',
                'nonce'      => '',
                'validation' => 1,
                'changed'    => 0,
                'status'     => '',
                'form'       => acf_encrypt( json_encode( $form ) ),
            ) );
            $data_types = array(
                'post',
                'user',
                'term',
                'product'
            );
            foreach ( $data_types as $type ) {
                if ( !empty($form[$type . '_id']) ) {
                    $data[$type] = $form[$type . '_id'];
                }
            }
            // crete nonce
            $data['nonce'] = wp_create_nonce( $data['screen'] );
            // return
            return $data;
        }
        
        public function form_render_data( $form = array() )
        {
            // set form data
            $data = $this->form_set_data( $form );
            $error_msg = '';
            if ( $form['error_message'] ) {
                $error_msg = 'data-error="' . $form['error_message'] . '"';
            }
            ?>
			<div <?php 
            echo  $error_msg ;
            ?> class="acf-form-data acf-hidden">
				<?php 
            // loop
            foreach ( $data as $name => $value ) {
                // input
                acf_hidden_input( array(
                    'name'  => '_acf_' . $name,
                    'value' => $value,
                ) );
            }
            // actions
            do_action( 'acf/form_data', $data );
            do_action( 'acf/input/form_data', $data );
            ?>
			</div>
			<?php 
        }
        
        public function render_field_setting( $field, $setting, $global = false )
        {
            // Validate field.
            $setting = acf_validate_field( $setting );
            // Add custom attributes to setting wrapper.
            $setting['wrapper']['data-key'] = $setting['name'];
            $setting['wrapper']['class'] .= ' acf-field-setting-' . $setting['name'];
            if ( !$global ) {
                $setting['wrapper']['data-setting'] = $field['type'];
            }
            // Copy across prefix.
            $setting['prefix'] = $field['prefix'];
            // Find setting value from field.
            if ( $setting['value'] === null ) {
                // Name.
                
                if ( isset( $field[$setting['name']] ) ) {
                    $setting['value'] = $field[$setting['name']];
                    // Default value.
                } elseif ( isset( $setting['default_value'] ) ) {
                    $setting['value'] = $setting['default_value'];
                }
            
            }
            // Add append attribute used by JS to join settings.
            if ( isset( $setting['_append'] ) ) {
                $setting['wrapper']['data-append'] = $setting['_append'];
            }
            // Render setting.
            $this->render_field_wrap( $setting, 'tr', 'label' );
        }
        
        public function render_field_wrap( $field, $element = 'div', $instruction = 'label' )
        {
            $field = acf_frontend_parse_args( $field, array(
                'prefix'       => '',
                'type'         => '',
                'required'     => 0,
                'instructions' => '',
                '_name'        => '',
                'wrapper'      => array(
                'class' => '',
                'id'    => '',
                'width' => '',
            ),
            ) );
            
            if ( empty($field['_prepare']) ) {
                // Ensure field is complete (adds all settings).
                if ( function_exists( 'acf_validate_field' ) ) {
                    $field = acf_validate_field( $field );
                }
                // Prepare field for input (modifies settings).
                if ( function_exists( 'acf_prepare_field' ) ) {
                    $field = acf_prepare_field( $field );
                }
            }
            
            // Allow filters to cancel render.
            if ( !$field ) {
                return;
            }
            // Determine wrapping element.
            $elements = array(
                'div' => 'div',
                'tr'  => 'td',
                'td'  => 'div',
                'ul'  => 'li',
                'ol'  => 'li',
                'dl'  => 'dt',
            );
            
            if ( isset( $elements[$element] ) ) {
                $inner_element = $elements[$element];
            } else {
                $element = $inner_element = 'div';
            }
            
            // Generate wrapper attributes.
            $wrapper = array(
                'id'        => '',
                'class'     => 'acf-field',
                'width'     => '',
                'style'     => '',
                'data-name' => $field['_name'],
                'data-type' => $field['type'],
                'data-key'  => $field['key'],
            );
            // Add field type attributes.
            $wrapper['class'] .= " acf-field-{$field['type']}";
            // add field key attributes
            if ( $field['key'] ) {
                $wrapper['class'] .= " acf-field-{$field['key']}";
            }
            // Add required attributes.
            // Todo: Remove data-required
            
            if ( $field['required'] ) {
                $wrapper['class'] .= ' is-required';
                $wrapper['data-required'] = 1;
            }
            
            // Clean up class attribute.
            $wrapper['class'] = str_replace( '_', '-', $wrapper['class'] );
            $wrapper['class'] = str_replace( 'field-field-', 'field-', $wrapper['class'] );
            // Merge in field 'wrapper' setting without destroying class and style.
            if ( $field['wrapper'] ) {
                $wrapper = acf_merge_attributes( $wrapper, $field['wrapper'] );
            }
            // Extract wrapper width and generate style.
            // Todo: Move from $wrapper out into $field.
            $width = acf_extract_var( $wrapper, 'width' );
            
            if ( $width ) {
                $width = acf_numval( $width );
                
                if ( $element !== 'tr' && $element !== 'td' ) {
                    $wrapper['data-width'] = $width;
                    $wrapper['style'] .= " width:{$width}%;";
                }
            
            }
            
            // Clean up all attributes.
            $wrapper = array_map( 'trim', $wrapper );
            $wrapper = array_filter( $wrapper );
            /**
             * Filters the $wrapper array before rendering.
             *
             * @date	21/1/19
             * @since	5.7.10
             *
             * @param	array $wrapper The wrapper attributes array.
             * @param	array $field The field array.
             */
            $wrapper = apply_filters( 'acf/field_wrapper_attributes', $wrapper, $field );
            // Append conditional logic attributes.
            if ( !empty($field['conditional_logic']) ) {
                $wrapper['data-conditions'] = $field['conditional_logic'];
            }
            if ( !empty($field['conditions']) ) {
                $wrapper['data-conditions'] = $field['conditions'];
            }
            // Vars for render.
            $attributes_html = acf_esc_attr( $wrapper );
            // Render HTML
            echo  "<{$element} {$attributes_html}>" . "\n" ;
            
            if ( $element !== 'td' && (!isset( $field['field_label_hide'] ) || !$field['field_label_hide']) ) {
                echo  "<{$inner_element} class=\"acf-label\">" . "\n" ;
                acf_render_field_label( $field );
                echo  "</{$inner_element}>" . "\n" ;
            }
            
            echo  "<{$inner_element} class=\"acf-input\">" . "\n" ;
            if ( $instruction == 'label' ) {
                acf_render_field_instructions( $field );
            }
            
            if ( !empty($field['frontend_admin_display_mode']) && $field['frontend_admin_display_mode'] == 'read_only' ) {
                echo  fea_instance()->dynamic_values->display_field( $field ) ;
            } else {
                
                if ( isset( $field['php_code'] ) ) {
                    echo  $field['message'] ;
                } else {
                    acf_render_field( $field );
                }
            
            }
            
            if ( $instruction == 'field' ) {
                acf_render_field_instructions( $field );
            }
            echo  "</{$inner_element}>" . "\n" ;
            echo  "</{$element}>" . "\n" ;
        }
        
        public function render_other_steps( $steps, $form, $element )
        {
            $cf_save = $form['custom_fields_save'];
            $prepend_types = array( 'user', 'term', 'comment' );
            if ( isset( $steps ) ) {
                foreach ( $steps as $index => $step ) {
                    ?>
					<?php 
                    
                    if ( !empty($form['show_form_title']) ) {
                        $step_title = ( $step['form_title'] ? $step['form_title'] : $form['form_title'] );
                        
                        if ( $step_title ) {
                            ?>
							<div class="acf-field frontend-admin-hidden" data-step="<?php 
                            echo  $index ;
                            ?>">
								<h2 class="frontend-form-title"><?php 
                            echo  $step_title ;
                            ?></h2>
							</div>
							<?php 
                        }
                    
                    }
                    
                    $fields = $this->get_fields_to_display( $form, $step['fields'] );
                    $fields = array_filter( $fields );
                    foreach ( $fields as $field ) {
                        $field['required'] = 0;
                        $data_type = fea_instance()->acf_extension->find_field_type_group( $field['type'] );
                        if ( !$data_type && $cf_save ) {
                            $data_type = $cf_save;
                        }
                        
                        if ( $data_type ) {
                            
                            if ( $data_type != 'options' && isset( $form["{$data_type}_id"] ) ) {
                                $data_id = $form["{$data_type}_id"];
                            } else {
                                $data_id = $data_type;
                            }
                            
                            if ( $data_id == 'none' ) {
                                continue;
                            }
                            $field['wrapper']['class'] .= ' frontend-admin-hidden';
                            $field['wrapper']['data-step'] = $index;
                            if ( in_array( $data_type, $prepend_types ) ) {
                                $data_id = $data_type . '_' . $data_id;
                            }
                            if ( $data_type == 'product' ) {
                                $data_type = 'woo_' . $data_type;
                            }
                            $field['prefix'] = 'acff[' . $data_type . ']';
                            if ( !isset( $field['value'] ) || $field['value'] === null ) {
                                $field['value'] = acf_get_value( $data_id, $field );
                            }
                        }
                        
                        // Render wrap.
                        $this->render_field_wrap( $field );
                    }
                    if ( empty($form['submit_button_field']) || isset( $form['approval'] ) ) {
                        $this->render_submit_button( $form, $index, true );
                    }
                    ?>
					<?php 
                }
            }
        }
        
        public function get_fields_to_display( $form, $current_fields )
        {
            $displayed_fields = array();
            
            if ( $form['field_objects'] ) {
                $fields = $form['field_objects'];
            } else {
                $fields = array();
                if ( $current_fields ) {
                    foreach ( $current_fields as $field_data ) {
                        $field_data = acf_maybe_get_field( $field_data, false, false );
                        if ( $field_data ) {
                            
                            if ( $field_data['type'] == 'fields_select' && $field_data['fields_select'] ) {
                                $fields_class = ( isset( $field_data['fields_class'] ) ? $field_data['fields_class'] : '' );
                                foreach ( $field_data['fields_select'] as $sub_field ) {
                                    
                                    if ( strpos( $sub_field, 'field_' ) !== false ) {
                                        $sub_field_data = acf_maybe_get_field( $sub_field, false, false );
                                        if ( !$sub_field_data ) {
                                            continue;
                                        }
                                        if ( $fields_class ) {
                                            $sub_field_data['wrapper']['class'] .= ' ' . $fields_class;
                                        }
                                        $fields[] = $sub_field_data;
                                        $displayed_fields[$sub_field_data['type']] = $sub_field_data['key'];
                                    } else {
                                        $selected_fields = acf_frontend_get_acf_field_choices( array(
                                            'groups' => array( $sub_field ),
                                        ), 'key' );
                                        foreach ( $selected_fields as $sub_field ) {
                                            $sub_field_data = acf_maybe_get_field( $sub_field, false, false );
                                            if ( !$sub_field_data ) {
                                                continue;
                                            }
                                            if ( $fields_class ) {
                                                $sub_field_data['wrapper']['class'] .= ' ' . $fields_class;
                                            }
                                            $fields[] = $sub_field_data;
                                            $displayed_fields[$sub_field_data['type']] = $sub_field_data['key'];
                                        }
                                    }
                                
                                }
                            } else {
                                $fields[] = $field_data;
                                $displayed_fields[$field_data['type']] = $field_data['key'];
                            }
                        
                        }
                    }
                }
            }
            
            if ( empty($fields) ) {
                return false;
            }
            $GLOBALS['form_fields'] = $displayed_fields;
            return $fields;
        }
        
        public function render_fields(
            $form,
            $current_step,
            $element,
            $hidden = true
        )
        {
            
            if ( isset( $form['steps'] ) ) {
                $current_fields = $form['steps'][$current_step]['fields'];
            } else {
                $current_fields = $form['fields'];
            }
            
            
            if ( isset( $form['template_id'] ) ) {
                $template_type = get_post_type( $form['template_id'] );
                if ( $template_type == 'admin_form' || $template_type == 'acf-field-group' ) {
                    $current_fields = $this->get_form_fields( $form['template_id'] );
                }
            }
            
            $fields = $this->get_fields_to_display( $form, $current_fields );
            $cf_save = $form['custom_fields_save'];
            $el = $form['field_el'];
            $instruction = $form['instruction_placement'];
            $prepend_types = array( 'user', 'term', 'comment' );
            // Filter our false results.
            $fields = array_filter( $fields );
            /**
             * Filters the $fields array before they are rendered.
             *
             * @date	12/02/2014
             * @since	5.0.0
             *
             * @param	array $fields An array of fields.
             * @param	array $form An array of all of the form data.
             */
            $fields = apply_filters( FEA_PREFIX . '/pre_render_fields', $fields, $form );
            $show_submit_button = 0;
            // Loop over and render fields.
            
            if ( $fields ) {
                $fields = array_merge( $this->hidden_default_fields( $form ), $fields );
                $open_columns = 0;
                foreach ( $fields as $field ) {
                    if ( isset( $field['_input'] ) ) {
                        $field['value'] = stripslashes( $field['_input'] );
                    }
                    
                    if ( isset( $field['render_content'] ) ) {
                        echo  $field['render_content'] ;
                    } elseif ( isset( $field['column'] ) ) {
                        
                        if ( $field['column'] == 'endpoint' ) {
                            if ( $open_columns ) {
                                echo  '</div>' ;
                            }
                            $open_columns--;
                        } else {
                            
                            if ( isset( $field['nested'] ) ) {
                                $open_columns++;
                            } else {
                                if ( $open_columns ) {
                                    while ( $open_columns > 0 ) {
                                        echo  '</div>' ;
                                        $open_columns--;
                                    }
                                }
                                $open_columns++;
                            }
                            
                            echo  '<div class="acf-column elementor-repeater-item-' . $field['column'] . '">' ;
                        }
                    
                    } else {
                        
                        if ( isset( $form['admin_options'] ) ) {
                            $field['prefix'] = 'acff[admin_options]';
                            $field['value'] = get_option( $field['key'] );
                        } else {
                            $data_type = fea_instance()->acf_extension->find_field_type_group( $field['type'] );
                            if ( !$data_type && $cf_save ) {
                                $data_type = $cf_save;
                            }
                            $field['wrapper']['data-step'] = $current_step;
                            $field['wrapper']['data-current'] = 1;
                            
                            if ( $data_type ) {
                                
                                if ( $data_type != 'options' && isset( $form["{$data_type}_id"] ) ) {
                                    $data_id = $form["{$data_type}_id"];
                                } else {
                                    $data_id = $data_type;
                                }
                                
                                if ( $data_id == 'none' ) {
                                    continue;
                                }
                                if ( in_array( $data_type, $prepend_types ) ) {
                                    $data_id = $data_type . '_' . $data_id;
                                }
                                if ( $data_type == 'product' ) {
                                    $data_type = 'woo_' . $data_type;
                                }
                                $field['prefix'] = 'acff[' . $data_type . ']';
                                if ( !isset( $field['value'] ) || $field['value'] === null ) {
                                    $field['value'] = acf_get_value( $data_id, $field );
                                }
                            }
                        
                        }
                        
                        if ( empty($field['no_data_collect']) ) {
                            $show_submit_button = 1;
                        }
                        if ( $field['type'] == 'submit_button' ) {
                            $form['submit_button_field'] = $field['key'];
                        }
                        // Render wrap.
                        $this->render_field_wrap( $field, $el, $instruction );
                    }
                
                }
                if ( $open_columns > 0 ) {
                    while ( $open_columns > 0 ) {
                        echo  '</div>' ;
                        $open_columns--;
                    }
                }
            }
            
            if ( !empty($open_accordion) ) {
                echo  '</div></div></div>' ;
            }
            
            if ( $show_submit_button && $hidden && (empty($form['submit_button_field']) || isset( $form['approval'] )) ) {
                ?>
					<?php 
                $this->render_submit_button( $form, $current_step );
                ?>
				<?php 
            }
            
            /**
             *  Fires after fields have been rendered.
             *
             *  @date	12/02/2014
             *  @since	5.0.0
             *
             * @param	array $fields An array of fields.
             * @param	array $form An array of all of the form data.
             */
            do_action( FEA_PREFIX . '/render_fields', $fields, $form );
            return $form;
        }
        
        public function delete_button( $args )
        {
            $args = $this->get_form_data( $args );
            $type = $args['data_type'];
            if ( empty($args[$type . '_id']) || $args[$type . '_id'] == 'none' ) {
                return;
            }
            if ( empty($args['error_message']) ) {
                $args['error_message'] = __( 'There has been an error.', FEA_NS );
            }
            $confirm_message = $args['delete_message'];
            $delete_button_icon = $args['delete_icon']['value'];
            $delete_button_text = $args['delete_text'];
            acf_enqueue_scripts();
            ?> 
			<form class="frontend-form -delete" action="" method="POST" >

			<?php 
            $args = $this->get_redirect_url( $args );
            $this->form_render_data( array_merge( $args, array(
                'element_id' => $args['id'],
            ) ) );
            ?>
			<div class="frontend-admin-delete-button-container">
			<button onclick="return confirm('<?php 
            echo  $confirm_message ;
            ?>')" type="submit" class="button frontend-admin-delete-button">
			<?php 
            
            if ( $delete_button_icon ) {
                ?>
				<i class="<?php 
                echo  $delete_button_icon ;
                ?>"></i>
			<?php 
            }
            
            ?>
			<?php 
            echo  $delete_button_text ;
            ?> </button>
				</div>
			</form>
			<?php 
        }
        
        public function saved_drafts( $args )
        {
            $element_id = $args['hidden_fields']['element_id'];
            $drafts_args = array(
                'posts_per_page' => -1,
                'post_status'    => 'draft',
                'post_type'      => $args['new_post_type'],
                'author'         => get_current_user_id(),
            );
            $form_submits = get_posts( $drafts_args );
            if ( !$form_submits ) {
                return;
            }
            ?>
			<div class="frontend-form-posts"><p class="drafts-heading"><?php 
            echo  $args['saved_drafts']['saved_drafts_label'] ;
            ?></p>
			
			<?php 
            $draft_choices = [
                'add_post' => $args['saved_drafts']['saved_drafts_new'],
            ];
            
            if ( frontend_admin_edit_mode() ) {
                for ( $x = 1 ;  $x < 4 ;  $x++ ) {
                    $draft_choices[$x] = 'Draft ' . $x . ' (' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . ')';
                }
                $select_class = 'preview-form-drafts';
            } else {
                foreach ( $form_submits as $submit ) {
                    $post_time = get_the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $submit->ID );
                    $draft_choices[$submit->ID] = $submit->post_title . ' (' . $post_time . ')';
                }
                $select_class = 'posts-select';
            }
            
            acf_select_input( array(
                'choices' => $draft_choices,
                'class'   => $select_class,
                'value'   => $args['post_id'],
            ) );
            ?>
			</div>
			<?php 
        }
        
        public function saved_revisions( $args )
        {
            $element_id = $args['hidden_fields']['element_id'];
            
            if ( get_post_type( $args['post_id'] ) == 'revision' ) {
                $parent_post = wp_get_post_parent_id( $args['post_id'] );
            } else {
                $parent_post = $args['post_id'];
            }
            
            $form_submits = wp_get_post_revisions( $parent_post );
            if ( !$form_submits ) {
                return;
            }
            ?>
			<br><div class="frontend-form-posts"><p class="revisions-heading"><?php 
            echo  $args['saved_revisions']['saved_revisions_label'] ;
            ?></p>
			
			<?php 
            $revision_choices = [
                $parent_post => $args['saved_revisions']['saved_revisions_edit_main'],
            ];
            
            if ( frontend_admin_edit_mode() ) {
                for ( $x = 1 ;  $x < 4 ;  $x++ ) {
                    $revision_choices[$x] = 'Revision ' . $x . ' (' . date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) . ')';
                }
                $select_class = 'preview-form-revisions';
            } else {
                $first = true;
                
                if ( is_array( $form_submits ) && count( $form_submits ) > 1 ) {
                    foreach ( $form_submits as $index => $submit ) {
                        
                        if ( $first ) {
                            $first = false;
                            continue;
                        }
                        
                        $post_time = get_the_time( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $index );
                        $revision_choices[$index] = $submit->post_title . ' (' . $post_time . ')';
                    }
                    $select_class = 'posts-select';
                }
            
            }
            
            acf_select_input( array(
                'choices' => $revision_choices,
                'class'   => $select_class,
                'value'   => $args['post_id'],
            ) );
            ?>
			</div>
			<?php 
        }
        
        public function get_record( $form )
        {
            if ( empty($form['id']) || !isset( $_COOKIE[$form['id']] ) ) {
                return $form;
            }
            $record = fea_instance()->submissions_handler->get_submission( $_COOKIE[$form['id']] );
            if ( empty($record->id) ) {
                return $form;
            }
            
            if ( $record->status == 'in_progress' ) {
                $form = json_decode( acf_decrypt( $record->fields ), true );
                $form['submission'] = $record->id;
                return $form;
            }
            
            return $form;
        }
        
        public function get_form_structure( $form )
        {
            if ( empty($form['fields_selection']) ) {
                return $form;
            }
            $wg_id = $form['id'];
            
            if ( !empty($form['multi']) ) {
                $form['multi'] = true;
            } else {
                $form['multi'] = false;
            }
            
            
            if ( $form['multi'] ) {
                $current_step = 1;
                $form['steps'][$current_step] = $form['first_step'][0];
                $form['steps'][$current_step]['fields'] = array();
            } else {
                $form['fields'] = array();
            }
            
            if ( isset( $form['fields_selection'] ) ) {
                foreach ( $form['fields_selection'] as $ind => $form_field ) {
                    $local_field = $acf_field_groups = $acf_fields = $fields = array();
                    
                    if ( $form['multi'] ) {
                        $fields = $form['steps'][$current_step]['fields'];
                    } else {
                        $fields = $form['fields'];
                    }
                    
                    switch ( $form_field['field_type'] ) {
                        case 'ACF_field_groups':
                            
                            if ( $form_field['dynamic_acf_fields'] ) {
                                $filters = $this->get_field_group_filters( $form );
                                $acf_field_groups = acf_frontend_get_acf_field_choices( $filters, 'key' );
                            } elseif ( $form_field['field_groups_select'] ) {
                                $acf_field_groups = acf_frontend_get_acf_field_choices( array(
                                    'groups' => $form_field['field_groups_select'],
                                ), 'key' );
                            }
                            
                            
                            if ( $acf_field_groups ) {
                                $fields_exclude = $form_field['fields_select_exclude'];
                                
                                if ( $fields_exclude ) {
                                    $acf_fields = array_diff( $acf_field_groups, $fields_exclude );
                                } else {
                                    $acf_fields = $acf_field_groups;
                                }
                            
                            }
                            
                            break;
                        case 'ACF_fields':
                            $acf_fields = $form_field['fields_select'];
                            break;
                        case 'step':
                            
                            if ( !$form['multi'] ) {
                                
                                if ( frontend_admin_edit_mode() && $current_step == 0 ) {
                                    echo  '<div class="acf-notice -error acf-error-message"><p>' . __( 'Note: You must turn on "Multi Step" for your steps to work.', FEA_NS ) . '</p></div>' ;
                                    $current_step++;
                                }
                            
                            } else {
                                $current_step++;
                                $form['steps'][$current_step] = $form_field;
                                $fields = [];
                            }
                            
                            break;
                        case 'column':
                            
                            if ( $form_field['endpoint'] == 'true' ) {
                                $fields[] = [
                                    'column' => 'endpoint',
                                ];
                            } else {
                                $column = [
                                    'column' => $form_field['_id'],
                                ];
                                if ( $form_field['nested'] ) {
                                    $column['nested'] = true;
                                }
                                $fields[] = $column;
                            }
                            
                            break;
                        case 'tab':
                            
                            if ( $form_field['endpoint'] == 'true' ) {
                                $fields[] = [
                                    'tab' => 'endpoint',
                                ];
                            } else {
                                $tab = [
                                    'tab' => $form_field['_id'],
                                ];
                                $fields[] = $tab;
                            }
                            
                            break;
                        case 'recaptcha':
                            $local_field = array(
                                'key'          => $wg_id . '_' . $form_field['field_type'] . '_' . $form_field['_id'],
                                'type'         => 'recaptcha',
                                'wrapper'      => [
                                'class' => '',
                                'id'    => '',
                                'width' => '',
                            ],
                                'required'     => 0,
                                'version'      => $form_field['recaptcha_version'],
                                'v2_theme'     => $form_field['recaptcha_theme'],
                                'v2_size'      => $form_field['recaptcha_size'],
                                'site_key'     => $form_field['recaptcha_site_key'],
                                'secret_key'   => $form_field['recaptcha_secret_key'],
                                'disabled'     => 0,
                                'readonly'     => 0,
                                'v3_hide_logo' => $form_field['recaptcha_hide_logo'],
                            );
                            break;
                        default:
                            if ( isset( $form_field['__dynamic__'] ) ) {
                                $form_field = $this->parse_tags( $form_field );
                            }
                            $default_value = $form_field['field_default_value'];
                            $local_field = array(
                                'label'         => '',
                                'wrapper'       => [
                                'class' => '',
                                'id'    => '',
                                'width' => '',
                            ],
                                'instructions'  => $form_field['field_instruction'],
                                'required'      => ( $form_field['field_required'] ? 1 : 0 ),
                                'placeholder'   => $form_field['field_placeholder'],
                                'default_value' => $default_value,
                                'disabled'      => $form_field['field_disabled'],
                                'readonly'      => $form_field['field_readonly'],
                                'min'           => $form_field['minimum'],
                                'max'           => $form_field['maximum'],
                                'prepend'       => $form_field['prepend'],
                                'append'        => $form_field['append'],
                            );
                            
                            if ( isset( $data_default ) ) {
                                $local_field['wrapper']['data-default'] = $data_default;
                                $local_field['wrapper']['data-dynamic_value'] = $default_value;
                            }
                            
                            if ( $form_field['field_hidden'] ) {
                                $local_field['wrapper']['class'] = 'acf-hidden';
                            }
                            
                            if ( $form_field['field_type'] == 'message' ) {
                                $local_field['type'] = 'message';
                                $local_field['message'] = $form_field['field_message'];
                                $local_field['name'] = $local_field['key'] = $wg_id . '_' . $form_field['_id'];
                            }
                            
                            break;
                    }
                    if ( $acf_fields ) {
                        $local_field = array(
                            'key'           => $wg_id . '_' . $form_field['field_type'] . '_' . $form_field['_id'],
                            'type'          => 'fields_select',
                            'fields_select' => $acf_fields,
                            'fields_class'  => 'elementor-repeater-item-' . $form_field['_id'],
                            'wrapper'       => array(
                            'class' => '',
                        ),
                        );
                    }
                    
                    if ( isset( $local_field ) ) {
                        $sub_fields = false;
                        
                        if ( $form_field['field_type'] == 'attributes' ) {
                            $sub_fields = $form['attribute_fields'];
                            unset( $form['attribute_fields'] );
                        }
                        
                        
                        if ( $form_field['field_type'] == 'variations' ) {
                            $sub_fields = $form['variable_fields'];
                            unset( $form['variable_fields'] );
                        }
                        
                        foreach ( acf_frontend_get_field_type_groups() as $name => $group ) {
                            
                            if ( in_array( $form_field['field_type'], array_keys( $group['options'] ) ) ) {
                                $action_name = explode( '_', $name )[0];
                                
                                if ( isset( fea_instance()->local_actions[$action_name] ) ) {
                                    $action = fea_instance()->local_actions[$action_name];
                                    $local_field = $action->get_fields_display(
                                        $form_field,
                                        $local_field,
                                        $wg_id,
                                        $sub_fields
                                    );
                                    
                                    if ( isset( $form_field['field_label_on'] ) ) {
                                        $field_label = ucwords( str_replace( '_', ' ', $form_field['field_type'] ) );
                                        $local_field['label'] = ( $form_field['field_label'] ? $form_field['field_label'] : $field_label );
                                    }
                                    
                                    
                                    if ( isset( $local_field['type'] ) ) {
                                        
                                        if ( $local_field['type'] == 'number' ) {
                                            $local_field['placeholder'] = $form_field['number_placeholder'];
                                            $local_field['default_value'] = $form_field['number_default_value'];
                                        }
                                        
                                        
                                        if ( $form_field['field_type'] == 'taxonomy' ) {
                                            $taxonomy = ( isset( $form_field['field_taxonomy'] ) ? $form_field['field_taxonomy'] : 'category' );
                                            $local_field['name'] = $wg_id . '_' . $taxonomy;
                                            $local_field['key'] = $wg_id . '_' . $taxonomy;
                                        } else {
                                            $local_field['name'] = $wg_id . '_' . $form_field['field_type'];
                                            $local_field['key'] = $wg_id . '_' . $form_field['field_type'];
                                        }
                                    
                                    }
                                    
                                    if ( !empty($form_field['default_terms']) ) {
                                        $local_field['default_terms'] = $form_field['default_terms'];
                                    }
                                }
                                
                                break;
                            }
                        
                        }
                    }
                    
                    if ( isset( $local_field['label'] ) ) {
                        
                        if ( !$form_field['field_label_on'] ) {
                            $local_field['field_label_hide'] = 1;
                        } else {
                            $local_field['field_label_hide'] = 0;
                        }
                    
                    }
                    if ( isset( $form_field['button_text'] ) && $form_field['button_text'] ) {
                        $local_field['button_text'] = $form_field['button_text'];
                    }
                    
                    if ( isset( $local_field['key'] ) ) {
                        $field_key = '';
                        $local_field['wrapper']['class'] .= ' elementor-repeater-item-' . $form_field['_id'];
                        
                        if ( frontend_admin_edit_mode() ) {
                            acf_add_local_field( $local_field );
                            $field_key = $local_field['key'];
                        } else {
                            $local_field['key'] = 'field_' . $form_field['_id'];
                            $field_obj = acf_get_field( $local_field['key'] );
                            if ( $field_obj ) {
                                //print("<pre>".print_r($field_obj,true)."</pre>");
                                $local_field = array_merge( $field_obj, $local_field );
                            }
                            acf_update_field( $local_field );
                            $field_key = $local_field['key'];
                        }
                        
                        $fields[] = $field_key;
                    }
                    
                    
                    if ( $form['multi'] ) {
                        $form['steps'][$current_step]['fields'] = $fields;
                    } else {
                        $form['fields'] = $fields;
                    }
                
                }
            }
            unset( $form['fields_selection'] );
            unset( $form['first_step'] );
            return $form;
        }
        
        public function parse_tags( $settings )
        {
            $dynamic_tags = $settings['__dynamic__'];
            foreach ( $dynamic_tags as $control_name => $tag ) {
                $settings[$control_name] = $tag;
            }
            return $settings;
        }
        
        public function get_field_group_filters( $form )
        {
            $filters = array(
                'post_id' => $form['post_id'],
            );
            
            if ( $form['save_to_post'] == 'new_post' ) {
                $filters = array(
                    'post_type' => $form['new_post_type'],
                );
            } else {
                $filters = array(
                    'post_id' => $form['post_id'],
                );
            }
            
            return $filters;
        }
        
        public function delete_record( $form )
        {
            
            if ( !empty($form['id']) && isset( $_COOKIE[$form['id']] ) ) {
                $expiration_time = time();
                setcookie(
                    $form['id'],
                    '0',
                    $expiration_time,
                    '/'
                );
            }
        
        }
        
        public function show_messages( $form )
        {
        }
        
        public function render_form( $form, $preview = false )
        {
            $editor = frontend_admin_edit_mode();
            if ( isset( $_GET['submit_id'] ) ) {
                
                if ( isset( $_GET['email_address'] ) ) {
                    $address = $_GET['email_address'];
                    if ( $GLOBAL[$address . '_verified'] ) {
                        echo  '<div class="frontend-admin-message"><div class="acf-notice -success acf-success-message -dismiss"><p class="success-msg">' . __( 'Email Verified Successfully' ) . '</p><span class="frontend-admin-dismiss close-msg acf-notice-dismiss acf-icon -cancel small"></span></div></div>' ;
                    }
                } else {
                    $form = fea_instance()->submissions_handler->get_submission_form( $_GET['submit_id'] );
                }
            
            }
            $form = $this->validate_form( $form );
            
            if ( isset( $form['who_can_see'] ) && empty($form['approval']) ) {
                $form = apply_filters( FEA_PREFIX . '/show_form', $form );
                
                if ( empty($form['display']) && !$preview ) {
                    
                    if ( !empty($form['message']) && $form['message'] !== 'NOTHING' ) {
                        echo  $form['message'] ;
                    } else {
                        switch ( $form['not_allowed'] ) {
                            case 'show_message':
                                echo  '<div class="acf-notice -error acf-error-message"><p>' . $form['not_allowed_message'] . '</p></div>' ;
                                break;
                            case 'custom_content':
                                echo  '<div class="not_allowed_message">' . $form['not_allowed_content'] . '</div>' ;
                                break;
                        }
                    }
                    
                    
                    if ( frontend_admin_edit_mode() ) {
                        echo  '<div class="preview-display">' ;
                    } else {
                        return;
                    }
                
                }
            
            }
            
            $this->show_messages( $form );
            acf_enqueue_scripts();
            acf_enqueue_uploader();
            
            if ( !empty($form['show_in_modal']) ) {
                $attrs = array(
                    'class'     => 'frontend-admin-edit-button render-form',
                    'data-name' => 'admin_form',
                    'data-key'  => $form['id'],
                );
                if ( isset( $form['modal_width'] ) ) {
                    $attrs['data-form_width'] = $form['modal_width'];
                }
                echo  '<button ' . acf_esc_attr( $attrs ) . '>' . $form['modal_button_text'] . '</button>' ;
                acf_hidden_input( array(
                    'name'  => 'form_' . $form['id'],
                    'value' => acf_encrypt( json_encode( $form ) ),
                ) );
                return;
            }
            
            if ( $preview ) {
                $form['preview_mode'] = true;
            }
            $form['form_attributes']['id'] = $form['id'];
            $element = $form['hidden_fields']['element_id'];
            $GLOBALS['admin_form'] = $form;
            $form = $this->get_form_structure( $form );
            $current_step = 1;
            
            if ( isset( $form['steps'] ) ) {
                $steps_count = count( $form['steps'] );
                
                if ( isset( $form['step_index'] ) ) {
                    $current_step = $form['step_index'];
                } else {
                    $form['step_index'] = $current_step;
                }
                
                $form['hidden_fields']['step'] = $current_step;
                if ( $current_step == $steps_count ) {
                    $form['last_step'] = true;
                }
                //if( $editor ){
                $other_steps = $form['steps'];
                unset( $other_steps[$current_step] );
                //array_splice( $other_steps, $current_step-1, 1 );
                /*} else{
                			if( $current_step > 1 ) $other_steps = array_slice( $form['steps'], 0, $current_step );
                		} */
                $form_title = $form['steps'][$current_step]['form_title'];
                if ( !$form_title ) {
                    $form_title = $form['form_title'];
                }
                $step_tabs = true;
                $form['form_attributes']['data-steps'] = $steps_count;
            } else {
                $form_title = $form['form_title'];
            }
            
            // Set uploader type.
            acf_update_setting( 'uploader', $form['uploader'] );
            ?>
			<form <?php 
            echo  acf_frontend_esc_attrs( $form['form_attributes'] ) ;
            ?>> 
			<?php 
            if ( isset( $step_tabs ) ) {
                $this->step_tabs( $form, $current_step );
            }
            global  $frontend_admin_success_returned ;
            
            if ( isset( $frontend_admin_success_returned ) ) {
                
                if ( isset( $form['step_index'] ) && $form['step_index'] > 1 ) {
                    $no_message = true;
                } else {
                    
                    if ( empty($frontend_admin_success_returned['frontend-form-nonce']) || !wp_verify_nonce( $frontend_admin_success_returned['frontend-form-nonce'], 'frontend-form' ) ) {
                        $user_id = get_current_user_id();
                        if ( empty($frontend_admin_success_returned['message_token']) || get_user_meta( $user_id, 'message_token', true ) !== $frontend_admin_success_returned['message_token'] ) {
                            $no_message = true;
                        }
                    }
                
                }
                
                if ( empty($no_message) ) {
                    if ( isset( $frontend_admin_success_returned['success_message'] ) && $frontend_admin_success_returned['location'] == 'current' && $frontend_admin_success_returned['form_element'] == $element ) {
                        printf( $form['html_updated_message'], wp_unslash( wp_kses( $frontend_admin_success_returned['success_message'], 'post' ) ) );
                    }
                }
            }
            
            $this->form_render_data( $form );
            ?>
			<div class="acf-fields acf-form-fields -<?php 
            echo  esc_attr( $form['label_placement'] ) ;
            ?>">
				<?php 
            
            if ( isset( $current_step ) ) {
                if ( isset( $other_steps ) ) {
                    $this->render_other_steps( $other_steps, $form, $element );
                }
                ?>

				<?php 
            }
            
            if ( !empty($form['show_form_title']) && $form_title ) {
                echo  '<div class="acf-field" data-current="1" data-step="' . $current_step . '"><h2 class="frontend-form-title">' . $form_title . '</h2></div>' ;
            }
            $form = $this->render_fields( $form, $current_step, $element );
            ?>
				
				 
			</div>
			<?php 
            if ( isset( $form['steps'] ) ) {
                echo  '</div></div>' ;
            }
            /* if( $form['save_progress'] ){ 
            				if( !empty( $form['save_progress']['text'] ) ) $form['save_progress'] = $form['save_progress']['text'];
            				$state = $form['post_id'] == 'add_post' ? 'draft' : 'revision';
            				?>
            				<div class="save-progress-buttons">
            				<input formnovalidate type="submit" class="save-progress-button acf-submit-button button" value="<?php echo $form['save_progress']; ?>" name="save_progress" data-state="<?php echo $state ?>" /></div>
            			<?php	
            			} */
            ?>
			</form>
			<?php 
            do_action( FEA_PREFIX . '/after_form', $form );
            if ( frontend_admin_edit_mode() ) {
                echo  '</div>' ;
            }
        }
        
        public function hidden_default_fields( $form )
        {
            $fields = array();
            
            if ( $form['honeypot'] ) {
                acf_add_local_field( array(
                    'prefix'          => 'acf',
                    'name'            => '_validate_email',
                    'key'             => '_validate_email',
                    'no_data_collect' => 1,
                    'type'            => 'text',
                    'value'           => '',
                    'no_save'         => 1,
                    'wrapper'         => array(
                    'style' => 'display:none !important',
                ),
                ) );
                $fields[] = acf_get_field( '_validate_email' );
            }
            
            $element_id = $form['hidden_fields']['element_id'];
            
            if ( !frontend_admin_edit_mode() && !empty($form['product_id']) ) {
                
                if ( empty($GLOBALS['form_fields']['product_types']) ) {
                    $field_key = $element_id . '_product_type';
                    acf_add_local_field( array(
                        'name'    => $field_key,
                        'key'     => $field_key,
                        'type'    => 'product_types',
                        'wrapper' => array(
                        'style' => 'display:none !important',
                    ),
                    ) );
                    $GLOBALS['form_fields']['product_types'] = $field_key;
                    $fields[] = acf_get_field( $field_key );
                } else {
                    acf_hidden_input( array(
                        'name'  => 'acff[woo_product][types]',
                        'value' => $GLOBALS['form_fields']['product_types'],
                    ) );
                }
                
                
                if ( empty($GLOBALS['form_fields']['manage_stock']) ) {
                    $field_key = $element_id . '_manage_stock';
                    acf_add_local_field( array(
                        'name'    => $field_key,
                        'key'     => $field_key,
                        'type'    => 'manage_stock',
                        'ui'      => 0,
                        'wrapper' => array(
                        'style' => 'display:none !important',
                    ),
                    ) );
                    $GLOBALS['form_fields']['manage_stock'] = $field_key;
                    $fields[] = acf_get_field( $field_key );
                }
            
            }
            
            return $fields;
        }
        
        public function change_form()
        {
            if ( empty($_REQUEST['form_data']) ) {
                wp_send_json_error();
            }
            $form = json_decode( acf_decrypt( $_REQUEST['form_data'] ), true );
            if ( !$form ) {
                wp_send_json_error();
            }
            
            if ( isset( $_REQUEST['draft'] ) ) {
                $form['post_id'] = $_REQUEST['draft'];
                
                if ( is_numeric( $_REQUEST['draft'] ) ) {
                    $form['save_to_post'] = 'edit_post';
                } else {
                    $form['save_to_post'] = 'new_post';
                }
            
            } else {
                
                if ( isset( $_REQUEST['step'] ) ) {
                    $form['step_index'] = $_REQUEST['step'];
                } else {
                    $form['step_index'] = $form['step_index'] - 1;
                }
                
                
                if ( $form['step_index'] == count( $form['steps'] ) ) {
                    $form['last_step'] = true;
                } else {
                    if ( isset( $form['last_step'] ) ) {
                        unset( $form['last_step'] );
                    }
                }
            
            }
            
            $GLOBALS['admin_form'] = $form;
            ob_start();
            $form['no_cookies'] = 1;
            $this->render_form( $form );
            $reload_form = ob_get_contents();
            ob_end_clean();
            fea_instance()->form_submit->save_record( $form );
            wp_send_json_success( [
                'reload_form' => $reload_form,
                'to_top'      => true,
            ] );
            die;
        }
        
        public function get_steps( $field )
        {
            if ( $field['field_type'] == 'step' ) {
                return true;
            }
            return false;
        }
        
        public function ajax_add_form()
        {
            // vars
            $args = wp_parse_args( $_POST, array(
                'nonce'       => '',
                'field_key'   => '',
                'parent_form' => '',
                'form_action' => '',
                'form_args'   => '',
            ) );
            // verify nonce
            if ( !acf_verify_ajax() ) {
                die;
            }
            
            if ( $args['form_action'] == 'admin_form' ) {
                $form_args = json_decode( acf_decrypt( $args['form_args'] ), true );
                $form_args['show_in_modal'] = 0;
                $this->render_form( $form_args );
                die;
            }
            
            // load field
            $field = acf_get_field( $args['field_key'] );
            if ( !$field ) {
                die;
            }
            $edit_post = is_numeric( $args['form_action'] );
            $hidden_fields = [
                'field_id' => $args['field_key'],
            ];
            if ( is_admin() ) {
                $hidden_fields['screen_id'] = 'admin';
            }
            $form_id = $args['field_key'];
            $form_args = array(
                'post_id'            => $args['form_action'],
                'post_fields'        => [
                'post_status' => 'publish',
            ],
                'id'                 => $form_id,
                'fields'             => [ 'frontend_admin_title' ],
                'form_attributes'    => array(
                'data-field' => $args['field_key'],
            ),
                'ajax_submit'        => true,
                'hidden_fields'      => $hidden_fields,
                'redirect_action'    => 'clear_form',
                'return'             => '',
                'parent_form'        => $args['parent_form'],
                'new_post_status'    => 'publish',
                'save_to_post'       => ( $edit_post ? 'edit_post' : 'new_post' ),
                'custom_fields_save' => 'post',
            );
            
            if ( !empty($field['post_form_template']) && $field['post_form_template'] != 'none' ) {
                $form_args['template_id'] = $field['post_form_template'];
                if ( $form_args['template_id'] == 'current' ) {
                    $form_args['template_id'] = $field['parent'];
                }
            } else {
                
                if ( is_numeric( $args['form_action'] ) ) {
                    $form_args['update_message'] = __( 'Post Updated Successfully!', FEA_NS );
                    $form_args['submit_value'] = __( 'Update', FEA_NS );
                } else {
                    $form_args['update_message'] = __( 'Post Added Successfully!', FEA_NS );
                    $form_args['submit_value'] = __( 'Publish', FEA_NS );
                    $form_args['post_fields'] = [
                        'post_status' => 'publish',
                    ];
                }
            
            }
            
            $all_post_types = acf_get_pretty_post_types();
            
            if ( $args['form_action'] == 'add_post' ) {
                
                if ( empty($field['post_type']) ) {
                    $form_args['new_post_type'] = 'post';
                    $post_type_choices = $all_post_types;
                } elseif ( count( $field['post_type'] ) > 1 ) {
                    $form_args['new_post_type'] = $field['post_type'][0];
                    $post_type_choices = [];
                    foreach ( $field['post_type'] as $post_type ) {
                        $post_type_choices[$post_type] = $all_post_types[$post_type];
                    }
                } else {
                    $form_args['new_post_type'] = $field['post_type'][0];
                }
                
                
                if ( isset( $post_type_choices ) ) {
                    acf_add_local_field( array(
                        'key'           => 'frontend_admin_post_type',
                        'label'         => __( 'Post Type', FEA_NS ),
                        'default_value' => current( $field['post_type'] ),
                        'name'          => 'frontend_admin_post_type',
                        'type'          => 'post_type',
                        'layout'        => 'vertical',
                        'choices'       => $post_type_choices,
                    ) );
                    $form_args['fields'][] = 'frontend_admin_post_type';
                }
            
            }
            
            $this->render_form( $form_args );
            die;
        }
        
        /**
         * Registers the shortcode advanced_form which renders the form specified by the "form" attribute
         *
         * @since 1.0.0
         *
         */
        public function form_shortcode( $atts )
        {
            
            if ( isset( $atts['form'] ) ) {
                $form_id = $atts['form'];
                unset( $atts['form'] );
                ob_start();
                $this->render_form( $form_id );
                $output = ob_get_clean();
                return $output;
            }
        
        }
        
        function success_message_cookie()
        {
            
            if ( isset( $_COOKIE['admin_form_success'] ) ) {
                global  $frontend_admin_success_returned ;
                $frontend_admin_success_returned = json_decode( stripslashes( $_COOKIE['admin_form_success'] ), true );
                
                if ( isset( $frontend_admin_success_returned['used'] ) ) {
                    $expiration_time = time() - 600;
                    setcookie(
                        'admin_form_success',
                        '',
                        $expiration_time,
                        '/'
                    );
                } else {
                    $frontend_admin_success_returned['used'] = 1;
                    $expiration_time = time() + 600;
                    setcookie(
                        'admin_form_success',
                        json_encode( $frontend_admin_success_returned ),
                        $expiration_time,
                        '/'
                    );
                }
            
            }
        
        }
        
        public function __construct()
        {
            add_shortcode( FEA_PREFIX, array( $this, 'form_shortcode' ) );
            add_action( 'init', array( $this, 'success_message_cookie' ) );
            add_action( 'wp_ajax_frontend_admin/forms/change_form', array( $this, 'change_form' ) );
            add_action( 'wp_ajax_nopriv_frontend_admin/forms/change_form', array( $this, 'change_form' ) );
            add_action( 'wp_ajax_frontend_admin/forms/add_form', array( $this, 'ajax_add_form' ) );
            add_action( 'wp_ajax_nopriv_frontend_admin/forms/add_form', array( $this, 'ajax_add_form' ) );
        }
    
    }
    fea_instance()->form_display = new Display_Form();
}
