<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}

/**
 * Handles the admin part of the forms
 *
 * @since 1.0.0
 *
 */
class Frontend_Forms_UI
{
    /**
     * Adds a form key to a form if one doesn't exist
     * 
     * @since 1.0.0
     *
     */
    function save_post( $post_id, $post )
    {
        // do not save if this is an auto save routine
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return $post_id;
        }
        // bail early if not acff form
        if ( $post->post_type !== 'admin_form' ) {
            return $post_id;
        }
        
        if ( empty($post->post_name) ) {
            $form_key = 'form_' . uniqid();
            remove_action( 'save_post', array( $this, 'save_post' ) );
            wp_update_post( array(
                'ID'        => $post_id,
                'post_name' => $form_key,
            ) );
            add_action(
                'save_post',
                array( $this, 'save_post' ),
                10,
                2
            );
        } else {
            // only save once! WordPress save's a revision as well.
            if ( wp_is_post_revision( $post_id ) ) {
                return $post_id;
            }
            // verify nonce
            if ( !acf_verify_nonce( 'admin_form' ) ) {
                return $post_id;
            }
            // disable filters to ensure ACF loads raw data from DB
            acf_disable_filters();
            // save fields
            if ( !empty($_POST['acf_fields']) ) {
                // loop
                foreach ( $_POST['acf_fields'] as $field ) {
                    // vars
                    $specific = false;
                    $save = acf_extract_var( $field, 'save' );
                    // only saved field if has changed
                    if ( $save == 'meta' ) {
                        $specific = array( 'menu_order', 'post_parent' );
                    }
                    // set parent
                    if ( !$field['parent'] ) {
                        $field['parent'] = $post_id;
                    }
                    // save field
                    acf_update_field( $field, $specific );
                }
            }
            // delete fields
            
            if ( !empty($_POST['_acf_delete_fields']) ) {
                // clean
                $ids = explode( '|', $_POST['_acf_delete_fields'] );
                $ids = array_map( 'intval', $ids );
                // loop
                foreach ( $ids as $id ) {
                    // bai early if no id
                    if ( !$id ) {
                        continue;
                    }
                    // delete
                    acf_delete_field( $id );
                }
            }
            
            
            if ( !empty($_POST['form']) ) {
                $_POST['form']['ID'] = $post_id;
                $_POST['form']['title'] = $_POST['post_title'];
                $_POST['form']['key'] = $post->post_name;
                $this->update_form_post( $_POST['form'] );
            }
        
        }
    
    }
    
    public function update_form_post( $data = array() )
    {
        unset( $data['emails_to_send'][0] );
        // may have been posted. Remove slashes
        $data = wp_unslash( $data );
        // parse types (converts string '0' to int 0)
        $data = acf_parse_types( $data );
        // extract some args
        $extract = acf_extract_vars( $data, array(
            'ID',
            'key',
            'title',
            'menu_order',
            'fields',
            'active',
            '_valid'
        ) );
        // vars
        $data = maybe_serialize( $data );
        // save
        $save = array(
            'ID'           => $extract['ID'],
            'post_status'  => 'publish',
            'post_title'   => $extract['title'],
            'post_name'    => $extract['key'],
            'post_type'    => 'admin_form',
            'post_excerpt' => sanitize_title( $extract['title'] ),
            'post_content' => $data,
            'menu_order'   => $extract['menu_order'],
        );
        // slash data
        // - WP expects all data to be slashed and will unslash it (fixes '\' character issues)
        $save = wp_slash( $save );
        // update the field group and update the ID
        
        if ( !empty($data['ID']) ) {
            wp_update_post( $save );
        } else {
            $form_id = wp_insert_post( $save );
        }
        
        // return
        return $data;
    }
    
    /**
     * Displays the form key after the title
     *
     * @since 1.0.0
     *
     */
    function display_shortcode()
    {
        global  $post ;
        if ( 'admin_form' == $post->post_type ) {
            
            if ( !empty($post->post_name) ) {
                echo  '<div id="edit-slug-box">' ;
                // Show shortcode
                $form_shortcode = "[" . FEA_PREFIX . " form=\"" . $post->post_name . "\"]";
                echo  sprintf( '%s: <code>%s</code> ', __( 'Shortcode', FEA_NS ), $form_shortcode ) ;
                //Save icon location
                $icon_path = '<span class="dashicons dashicons-admin-page"></span>';
                // Add the button to the page.
                echo  '<button type="button" data-prefix="' . FEA_PREFIX . '" data-form="' . $post->post_name . '" class="copy-shortcode"> ', $icon_path, ' Copy code</button>
		</div>' ;
            }
        
        }
    }
    
    /**
     * Displays the form key after the title
     *
     * @since 1.0.0
     *
     */
    function post_type_form_data()
    {
        global  $post ;
        if ( 'admin_form' != $post->post_type ) {
            return;
        }
        // render post data
        acf_form_data( array(
            'screen'        => 'admin_form',
            'post_id'       => $post->ID,
            'delete_fields' => 0,
            'validation'    => 0,
        ) );
        $form_type = get_post_meta( $post->ID, 'admin_form_type', true );
        if ( !$form_type ) {
            $form_type = 'general';
        }
        $sub_tabs = array(
            'fields'      => __( "Fields", 'acf' ),
            'actions'     => __( 'Actions', FEA_NS ),
            'submissions' => __( 'Submissions', FEA_NS ),
            'permissions' => __( 'Permissions', FEA_NS ),
            'modal'       => __( 'Modal Window', FEA_NS ),
            'post'        => __( 'Post', FEA_NS ),
            'user'        => __( 'User', FEA_NS ),
            'term'        => __( 'Term', FEA_NS ),
        );
        ?> <div class="acf-fields frontend-form-fields"> <?php 
        fea_instance()->form_display->render_field_wrap( array(
            'name'             => 'admin_form_tabs',
            'key'              => 'admin_form_tabs',
            'value'            => 'fields',
            'field_label_hide' => 1,
            'type'             => 'button_group',
            'choices'          => $sub_tabs,
            'layout'           => 'horizontal',
        ) );
        foreach ( $sub_tabs as $type => $label ) {
            $this->show_fields( $type );
        }
        ?> </div> <?php 
    }
    
    /**
     * Adds custom columns to the listings page
     *
     * @since 1.0.0
     *
     */
    function manage_columns( $columns )
    {
        $new_columns = array(
            'shortcode' => __( 'Shortcode', FEA_NS ),
        );
        // Remove date column
        unset( $columns['date'] );
        return array_merge( array_splice( $columns, 0, 2 ), $new_columns, $columns );
    }
    
    /**
     * Outputs the content for the custom columns
     *
     * @since 1.0.0
     *
     */
    function columns_content( $column, $post_id )
    {
        //$form = fea_instance()->form_display->get_form( $post_id );
        
        if ( 'shortcode' == $column ) {
            // Show shortcode
            echo  "<code>[" . FEA_PREFIX . " form=\"" . get_post_field( 'post_name', $post_id ) . "\"]</code>" ;
            //Save icon location
            $icon_path = '<span class="dashicons dashicons-admin-page"></span>';
            // Add the button to the page.
            // The form code is saved into the button html for the JS above
            echo  '<button type="button" class="copy-shortcode" data-prefix="' . FEA_PREFIX . '" data-form="' . get_post_field( 'post_name', $post_id ) . '">', $icon_path, ' Copy code</button>' ;
        }
    
    }
    
    /**
     * Hides the months filter on the forms listing page.
     *
     * @since 1.6.5
     *
     */
    function disable_months_dropdown( $disabled, $post_type )
    {
        if ( 'admin_form' != $post_type ) {
            return $disabled;
        }
        return true;
    }
    
    /*  mb_post
     *
     *  This function will render the HTML for the medtabox 'Post'
     *
     */
    function show_fields( $type )
    {
        global  $form ;
        
        if ( isset( fea_instance()->local_actions[$type] ) ) {
            $fields = fea_instance()->local_actions[$type]->get_form_builder_options( $form );
        } else {
            $fields = (require_once __DIR__ . "/sections/{$type}.php");
        }
        
        $this->render_fields( $fields, $form, $type );
    }
    
    function get_view( $path = '', $args = array() )
    {
        // allow view file name shortcut
        if ( substr( $path, -4 ) !== '.php' ) {
            $path = __DIR__ . "/views/{$path}.php";
        }
        // include
        
        if ( file_exists( $path ) ) {
            extract( $args );
            include $path;
        }
    
    }
    
    function render_fields( $fields, $form, $type )
    {
        foreach ( $fields as $field ) {
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            if ( empty($field['conditional_logic']) ) {
                $field['conditional_logic'] = 0;
            }
            $field['wrapper']['data-form-tab'] = $type;
            
            if ( isset( $form[$field['key']] ) ) {
                if ( empty($field['value']) ) {
                    $field['value'] = $form[$field['key']];
                }
            } elseif ( isset( $field['default_value'] ) ) {
                $field['value'] = $field['default_value'];
            }
            
            fea_instance()->form_display->render_field_wrap( $field );
        }
    }
    
    function admin_head()
    {
        // global
        global  $post, $form ;
        if ( empty($post->ID) ) {
            return;
        }
        // set global var
        $form = $this->get_form_data( $post );
    }
    
    public function get_form_data( $post )
    {
        if ( is_int( $post ) ) {
            $post = get_post( $post );
        }
        $form = maybe_unserialize( $post->post_content );
        if ( !$form ) {
            $form = array();
        }
        $form_type = get_post_meta( $post->ID, 'admin_form_type', true );
        
        if ( !$form_type || $form_type == 'general' ) {
            $custom_fields_save = 'post';
        } else {
            $custom_fields_save = str_replace( array( 'new_', 'edit_', '_duplicate' ), '', $form_type );
        }
        
        $form = acf_frontend_parse_args( $form, array(
            'redirect'            => 'current',
            'custom_url'          => '',
            'show_update_message' => 1,
            'update_message'      => __( 'Your post has been updated successfully.', FEA_NS ),
            'custom_fields_save'  => $custom_fields_save,
            'by_role'             => array( 'administrator' ),
            'admin_form_type'     => $form_type,
            'save_to_post'        => 'edit_post',
            'save_to_user'        => 'edit_user',
            'save_to_term'        => 'edit_term',
            'save_to_product'     => 'edit_product',
            'modal_button_text'   => __( 'Click Here', FEA_NS ),
        ) );
        return $form;
    }
    
    function admin_enqueue_scripts()
    {
        // no autosave
        wp_dequeue_script( 'autosave' );
        // custom scripts
        wp_enqueue_style( 'acf-field-group' );
        wp_enqueue_script( 'acf-field-group' );
        // localize text
        acf_localize_text( array(
            'The string "field_" may not be used at the start of a field name' => __( 'The string "field_" may not be used at the start of a field name', 'acf' ),
            'This field cannot be moved until its changes have been saved'     => __( 'This field cannot be moved until its changes have been saved', 'acf' ),
            'Field group title is required'                                    => __( 'Form title is required', FEA_NS ),
            'Move to trash. Are you sure?'                                     => __( 'Move to trash. Are you sure?', 'acf' ),
            'No toggle fields available'                                       => __( 'No toggle fields available', 'acf' ),
            'Move Custom Field'                                                => __( 'Move Custom Field', 'acf' ),
            'Checked'                                                          => __( 'Checked', 'acf' ),
            '(no label)'                                                       => __( '(no label)', 'acf' ),
            '(this field)'                                                     => __( '(this field)', 'acf' ),
            'copy'                                                             => __( 'copy', 'acf' ),
            'or'                                                               => __( 'or', 'acf' ),
            'Null'                                                             => __( 'Null', 'acf' ),
        ) );
        // localize data
        acf_localize_data( array(
            'fieldTypes' => acf_get_field_types_info(),
        ) );
        // 3rd party hook
        do_action( 'acf/field_group/admin_enqueue_scripts' );
    }
    
    function current_screen()
    {
        // validate screen
        $current_screen = get_current_screen();
        if ( 'admin_form' != $current_screen->post_type ) {
            return;
        }
        // disable filters to ensure ACF loads raw data from DB
        acf_disable_filters();
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        // enqueue scripts
        acf_enqueue_scripts();
        $this->enqueue_admin_scripts();
        add_action( 'acf/input/admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
        add_action( 'acf/input/admin_head', array( $this, 'admin_head' ) );
        add_action( 'admin_footer', array( $this, 'admin_footer' ) );
    }
    
    function before_posts_query()
    {
        $fields = array( array(
            'key'        => 'select_post',
            'prefix'     => 'form',
            'type'       => 'post_object',
            'post_type'  => '',
            'taxonomy'   => '',
            'allow_null' => 0,
            'multiple'   => 0,
            'ui'         => 1,
        ), array(
            'key'        => 'select_product',
            'prefix'     => 'form',
            'type'       => 'post_object',
            'post_type'  => 'product',
            'taxonomy'   => '',
            'allow_null' => 0,
            'multiple'   => 0,
            'ui'         => 1,
        ) );
        foreach ( $fields as $field ) {
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            acf_add_local_field( $field );
        }
    }
    
    function before_users_query()
    {
        $fields = array( array(
            'key'           => 'by_user_id',
            'label'         => __( 'Select By User', FEA_NS ),
            'type'          => 'user',
            'instructions'  => '',
            'allow_null'    => 0,
            'multiple'      => 1,
            'ajax'          => 1,
            'ui'            => 1,
            'return_format' => 'id',
        ) );
        foreach ( $fields as $field ) {
            $field['prefix'] = 'form';
            $field['name'] = $field['key'];
            acf_add_local_field( $field );
        }
    }
    
    function enqueue_admin_scripts()
    {
        $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min' );
        wp_enqueue_style(
            'fea-form-builder',
            FEA_URL . 'assets/css/admin' . $min . '.css',
            array(),
            FEA_VERSION
        );
        wp_enqueue_script(
            'fea-form-builder',
            FEA_URL . 'assets/js/admin' . $min . '.js',
            array( 'jquery', 'acf-field-group', 'acf-input' ),
            FEA_VERSION,
            true
        );
        $text = array(
            'form' => array(
            'label'   => __( 'Form', FEA_NS ),
            'options' => array(
            'all_fields'     => __( 'All Fields', FEA_NS ),
            'acf:field_name' => __( 'ACF Field', FEA_NS ),
        ),
        ),
            'post' => array(
            'label'   => __( 'Post', FEA_NS ),
            'options' => array(
            'post:id'             => __( 'Post ID', FEA_NS ),
            'post:title'          => __( 'Title', FEA_NS ),
            'post:content'        => __( 'Content', FEA_NS ),
            'post:featured_image' => __( 'Featured Image', FEA_NS ),
        ),
        ),
            'user' => array(
            'label'   => __( 'User', FEA_NS ),
            'options' => array(
            'user:id'         => __( 'User ID', FEA_NS ),
            'user:username'   => __( 'Username', FEA_NS ),
            'user:email'      => __( 'Email', FEA_NS ),
            'user:first_name' => __( 'First Name', FEA_NS ),
            'user:last_name'  => __( 'Last Name', FEA_NS ),
            'user:role'       => __( 'Role', FEA_NS ),
        ),
        ),
        );
        if ( $text ) {
            wp_localize_script( 'fea-form-builder', 'acffdv', $text );
        }
        wp_enqueue_style( 'fea-modal' );
    }
    
    function admin_footer()
    {
        fea_instance()->form_display->render_form( array(
            'post_id'            => 'add_post',
            'save_to_post'       => 'new_post',
            'custom_fields_save' => 'post',
            'new_post_type'      => 'admin_form',
            'new_post_status'    => 'draft',
            'fields'             => array( 'admin_form_types', 'frontend_admin_title' ),
            'return'             => admin_url( 'post.php?post=%post_id%&action=edit' ),
            'honeypot'           => false,
            'no_record'          => 1,
            'submit_value'       => __( 'Create Form', FEA_NS ),
            'show_in_modal'      => 1,
            'modal_button_text'  => __( 'Add New Form', FEA_NS ),
            'modal_width'        => 600,
        ) );
    }
    
    function __construct()
    {
        require_once __DIR__ . '/post-types.php';
        add_action(
            'edit_form_top',
            array( $this, 'display_shortcode' ),
            12,
            0
        );
        add_action(
            'edit_form_after_title',
            array( $this, 'post_type_form_data' ),
            11,
            0
        );
        add_action(
            'save_post',
            array( $this, 'save_post' ),
            10,
            2
        );
        add_action( 'current_screen', array( $this, 'current_screen' ) );
        add_filter(
            'manage_admin_form_posts_columns',
            array( $this, 'manage_columns' ),
            10,
            1
        );
        add_action(
            'manage_admin_form_posts_custom_column',
            array( $this, 'columns_content' ),
            10,
            2
        );
        add_filter(
            'disable_months_dropdown',
            array( $this, 'disable_months_dropdown' ),
            10,
            2
        );
        add_action( 'wp_ajax_acf/fields/post_object/query', array( $this, 'before_posts_query' ), 4 );
        add_action( 'wp_ajax_nopriv_acf/fields/post_object/query', array( $this, 'before_posts_query' ), 4 );
        add_action( 'wp_ajax_acf/fields/user/query', array( $this, 'before_users_query' ), 4 );
        add_action( 'wp_ajax_nopriv_acf/fields/user/query', array( $this, 'before_users_query' ), 4 );
        //add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ), 10, 0 );
        add_action(
            'acf/prepare_field',
            array( $this, 'dynamic_value_insert' ),
            15,
            1
        );
        add_action(
            'media_buttons',
            array( $this, 'add_dynamic_value_button' ),
            15,
            1
        );
        add_action( 'init', array( $this, 'change_forms_post_type' ) );
    }
    
    function change_forms_post_type()
    {
        $args = array(
            'post_type'      => array( 'frontend_admin_form', 'acf_frontend_form', 'frontend_form' ),
            'posts_per_page' => '-1',
            'post_status'    => 'any',
        );
        $forms = get_posts( $args );
        if ( $forms ) {
            foreach ( $forms as $form ) {
                wp_update_post( array(
                    'ID'        => $form->ID,
                    'post_type' => 'admin_form',
                ) );
            }
        }
    }
    
    function dynamic_value_insert( $field )
    {
        if ( empty($field['dynamic_value_choices']) ) {
            return $field;
        }
        $field['wrapper']['data-dynamic_values'] = '1';
        
        if ( $field['type'] == 'text' ) {
            $field['type'] = 'text_input';
            $field['no_autocomplete'] = 1;
        }
        
        return $field;
    }
    
    function add_dynamic_value_button( $editor )
    {
        global  $post ;
        if ( empty($post->post_type) || $post->post_type != 'admin_form' ) {
            return;
        }
        if ( is_string( $editor ) && 'acf-editor' == substr( $editor, 0, 10 ) ) {
            echo  '<a class="dynamic-value-options button">' . __( 'Dynamic Value', FEA_NS ) . '</a>' ;
        }
    }
    
    function render_shortcode_option( $field, $parents = array() )
    {
        $insert_value = '';
        
        if ( empty($parents) ) {
            $insert_value = sprintf( '[form:%s]', $field['name'] );
        } else {
            $hierarchy = array_merge( $parents, array( $field['name'] ) );
            $top_level_name = array_shift( $hierarchy );
            $insert_value = sprintf( '[form:%s[%s]]', $top_level_name, join( '][', $hierarchy ) );
        }
        
        $label = wp_strip_all_tags( $field['label'] );
        $type = acf_get_field_type_label( $field['type'] );
        echo  sprintf( '<div class="field-option" data-insert-value="%s" role="button">', $insert_value ) ;
        echo  sprintf( '<span class="field-name">%s</span><span class="field-type">%s</span>', $label, $type ) ;
        echo  '</div>' ;
        // Append options for sub fields if they exist (and we are dealing with a group or clone field)
        $parent_field_types = array( 'group', 'clone' );
        
        if ( in_array( $field['type'], $parent_field_types ) && isset( $field['sub_fields'] ) ) {
            array_push( $parents, $field['name'] );
            echo  '<div class="sub-fields-wrapper">' ;
            foreach ( $field['sub_fields'] as $sub_field ) {
                $this->render_shortcode_option( $sub_field, $parents );
            }
            echo  '</div>' ;
        }
    
    }

}
fea_instance()->form_builder = new Frontend_Forms_UI();