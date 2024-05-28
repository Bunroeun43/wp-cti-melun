<?php

namespace Frontend_WP;


if ( !defined( 'ABSPATH' ) ) {
    exit;
    // Exit if accessed directly
}


if ( !class_exists( 'Frontend_WP_Admin' ) ) {
    class Frontend_WP_Admin
    {
        private  $tabs = array() ;
        public function plugin_page()
        {
            global  $frontend_admin_settings ;
            $frontend_admin_settings = add_menu_page(
                FEA_TITLE,
                FEA_TITLE,
                'manage_options',
                FEA_PRE . '-settings',
                [ $this, 'admin_settings_page' ],
                'dashicons-feedback',
                '87.87778'
            );
            add_submenu_page(
                FEA_PRE . '-settings',
                __( 'Settings', FEA_NS ),
                __( 'Settings', FEA_NS ),
                'manage_options',
                FEA_PRE . '-settings',
                '',
                0
            );
        }
        
        function admin_settings_page()
        {
            global  $frontend_admin_active_tab ;
            $frontend_admin_active_tab = ( isset( $_GET['tab'] ) ? $_GET['tab'] : 'welcome' );
            ?>

			<h2 class="nav-tab-wrapper">
			<?php 
            do_action( 'frontend_admin_settings_tabs' );
            ?>
			</h2>
			<?php 
            do_action( 'frontend_admin_settings_content' );
        }
        
        public function add_tabs()
        {
            add_action( 'frontend_admin_settings_tabs', [ $this, 'settings_tabs' ], 1 );
            add_action( 'frontend_admin_settings_content', [ $this, 'settings_render_options_page' ] );
        }
        
        public function settings_tabs()
        {
            global  $frontend_admin_active_tab ;
            foreach ( $this->tabs as $name => $label ) {
                ?>
				<a class="nav-tab <?php 
                echo  ( $frontend_admin_active_tab == $name || '' ? 'nav-tab-active' : '' ) ;
                ?>" href="<?php 
                echo  admin_url( '?page=' . FEA_PRE . '-settings&tab=' . $name ) ;
                ?>"><?php 
                _e( $label, FEA_NS );
                ?> </a>
			<?php 
            }
        }
        
        public function settings_render_options_page()
        {
            global  $frontend_admin_active_tab ;
            
            if ( '' || 'welcome' == $frontend_admin_active_tab ) {
                ?>
			<style>p.frontend-admin-text{font-size:20px}</style>
			<h3><?php 
                _e( 'Hello and welcome', FEA_NS );
                ?></h3>
			<p class="frontend-admin-text"><?php 
                printf( __( 'If this is your first time using %s, please read this quick tutorial to help get you started.', FEA_NS ), FEA_TITLE );
                ?></p>
			<?php 
                
                if ( FEA_NS == 'frontend-admin' ) {
                    $support_email = 'support@frontendadmin.com';
                    ?>
				<iframe width="560" height="315" src="https://www.youtube.com/embed/ZR7UAegiljQ" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			<?php 
                } else {
                    $support_email = 'support@frontendadmin.com';
                    ?>
				<iframe width="560" height="315" src="https://www.youtube-nocookie.com/embed/7vrW8hx5jlE" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
			<?php 
                }
                
                ?>
			<br>
			<p class="frontend-admin-text"><?php 
                _e( 'If you have any questions at all please feel welcome to email support at', FEA_NS );
                ?> <a href="mailto:<?php 
                echo  $support_email ;
                ?>"><?php 
                echo  $support_email ;
                ?></a> <?php 
                _e( 'or on whatsapp', FEA_NS );
                ?> <a href="https://api.whatsapp.com/send?phone=972532323950">+972-53-232-3950</a></p>
			<?php 
            } else {
                foreach ( $this->tabs as $form_tab => $label ) {
                    
                    if ( $form_tab == $frontend_admin_active_tab ) {
                        $admin_fields = apply_filters( FEA_PREFIX . '/' . $form_tab . '_fields', array() );
                        
                        if ( $admin_fields ) {
                            foreach ( $admin_fields as $key => $field ) {
                                $field['key'] = $key;
                                $field['name'] = $key;
                                $field['value'] = get_option( $key );
                                $field['prefix'] = 'fea[admin_options]';
                                $admin_fields[$key] = $field;
                            }
                            fea_instance()->form_display->render_form( [
                                'admin_options'  => 1,
                                'hidden_fields'  => [
                                'admin_page' => $frontend_admin_active_tab,
                                'screen_id'  => 'options',
                            ],
                                'field_objects'  => $admin_fields,
                                'submit_value'   => __( 'Save Settings', FEA_NS ),
                                'update_message' => __( 'Settings Saved', FEA_NS ),
                                'redirect'       => 'custom_url',
                                'kses'           => 0,
                                'honeypot'       => 0,
                                'no_record'      => 1,
                                'custom_url'     => admin_url( '?page=' . FEA_PRE . '-settings&tab=' . $_GET['tab'] ),
                            ] );
                        } else {
                            if ( $form_tab == 'payments' ) {
                                
                                if ( isset( $_POST['action'] ) && $_POST['action'] == 'frontend_admin_install_plugin' ) {
                                    $this->install_payments_addon();
                                } else {
                                    $this->addon_form();
                                }
                            
                            }
                        }
                    
                    }
                
                }
            }
        
        }
        
        public function addon_form()
        {
            $addon_slug = 'frontend-payments/frontend-payments.php';
            ?>
				<form class="frontend-admin-addon-form" method="post" action="">
			<?php 
            
            if ( frontend_admin_is_plugin_installed( $addon_slug ) ) {
                echo  '<input type="hidden" name="action" value="frontend_admin_activate_plugin"/>' ;
                $submit_value = 'Activate the payments addon';
            } else {
                echo  '<input type="hidden" name="action" value="frontend_admin_install_plugin"/>' ;
                $submit_value = 'Install and activate the payments addon';
            }
            
            printf( __( '<button type="submit" class="button">%s</button>', FEA_NS ), $submit_value );
            ?>
				<input type="hidden" name="addon" value="payments"/>
				<input type="hidden" name="nonce" value="<?php 
            echo  wp_create_nonce( 'frontend-admin-addon' ) ;
            ?>" />
				</form>
			<?php 
        }
        
        public function configs()
        {
            
            if ( !get_option( 'frontend_admin_hide_wp_dashboard' ) ) {
                add_option( 'frontend_admin_hide_wp_dashboard', true );
                add_option( 'frontend_admin_hide_by', array_map( 'strval', [
                    0 => 'user',
                ] ) );
            }
            
            require_once __DIR__ . '/admin-pages/forms/custom-fields.php';
        }
        
        public function install_payments_addon()
        {
            $args = acf_frontend_parse_args( $_POST, array(
                'nonce' => '',
                'addon' => '',
            ) );
            if ( !wp_verify_nonce( $args['nonce'], 'frontend-admin-addon' ) ) {
                echo  __( 'Nonce error', FEA_NS ) ;
            }
            
            if ( $args['addon'] == 'payments' ) {
                $addon_slug = 'frontend-payments/frontend-payments.php';
                $addon_zip = 'https://stage.frontendform.com/wp-content/uploads/updater/frontend-payments.zip';
                $installed = frontend_admin_install_plugin( $addon_zip );
                if ( $installed ) {
                    $this->addon_form();
                }
            }
        
        }
        
        public function activate_payments_addon()
        {
            if ( empty($_POST['action']) || $_POST['action'] != 'frontend_admin_activate_plugin' ) {
                return;
            }
            $args = acf_frontend_parse_args( $_POST, array(
                'nonce' => '',
                'addon' => '',
            ) );
            if ( !wp_verify_nonce( $args['nonce'], 'frontend-admin-addon' ) ) {
                echo  __( 'Nonce error', FEA_NS ) ;
            }
            
            if ( $args['addon'] == 'payments' ) {
                $addon_slug = 'frontend-payments/frontend-payments.php';
                activate_plugin( $addon_slug );
            }
            
            wp_redirect( add_query_arg( array(
                'page' => FEA_PRE . '-settings',
                'tab'  => 'payments',
            ), admin_url() ) );
        }
        
        public function settings_sections()
        {
            require_once __DIR__ . '/admin-pages/submissions/crud.php';
            require_once __DIR__ . '/admin-pages/main/local_avatar.php';
            require_once __DIR__ . '/admin-pages/main/uploads_privacy.php';
            require_once __DIR__ . '/admin-pages/main/hide_admin.php';
            require_once __DIR__ . '/admin-pages/main/apis.php';
            if ( FEA_PREFIX == 'frontend_admin' ) {
                require_once __DIR__ . '/admin-pages/main/acf.php';
            }
            require_once __DIR__ . '/admin-pages/forms/settings.php';
            do_action( FEA_PREFIX . '/admin_pages' );
        }
        
        public function validate_save_post()
        {
            
            if ( isset( $_POST['_acf_admin_page'] ) ) {
                $page_slug = $_POST['_acf_admin_page'];
                apply_filters( FEA_PREFIX . '/' . $page_slug . '_fields', [] );
            }
        
        }
        
        public function scripts()
        {
            $min = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '-min' );
            wp_register_style(
                'fea-modal',
                FEA_URL . 'assets/css/modal-min.css',
                array(),
                FEA_VERSION
            );
            wp_register_style(
                'fea-public',
                FEA_URL . 'assets/css/frontend-admin' . $min . '.css',
                array(),
                FEA_VERSION
            );
            wp_register_script(
                'fea-modal',
                FEA_URL . 'assets/js/modal.min.js',
                array( 'jquery' ),
                FEA_VERSION
            );
            wp_register_script(
                'fea-public',
                FEA_URL . 'assets/js/frontend-admin' . $min . '.js',
                array( 'jquery', 'acf', 'acf-input' ),
                FEA_VERSION,
                true
            );
            wp_register_script(
                'fea-password-strength',
                FEA_URL . 'assets/js/password-strength.min.js',
                array( 'password-strength-meter' ),
                FEA_VERSION,
                true
            );
            add_action( 'admin_init', array( $this, 'activate_payments_addon' ) );
        }
        
        public function __construct()
        {
            $this->tabs = array(
                'welcome'         => 'Welcome',
                'local_avatar'    => 'Local Avatar',
                'uploads_privacy' => 'Uploads Privacy',
                'hide_admin'      => 'Hide WP Dashboard',
                'apis'            => 'APIs',
            );
            if ( FEA_PREFIX == 'frontend_admin' ) {
                $this->tabs['acf'] = 'ACF';
            }
            $this->tabs = apply_filters( FEA_PREFIX . '/admin_tabs', $this->tabs );
            $this->settings_sections();
            add_action( 'wp_loaded', array( $this, 'scripts' ) );
            add_action( 'init', array( $this, 'configs' ) );
            add_action( 'admin_menu', array( $this, 'plugin_page' ), 15 );
            add_action( 'acf/validate_save_post', array( $this, 'validate_save_post' ) );
            $this->add_tabs();
        }
    
    }
    fea_instance()->admin_settings = new Frontend_WP_Admin();
}
