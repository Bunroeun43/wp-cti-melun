<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
   
function frontend_admin_is_plugin_installed( $slug ) {
  if ( ! function_exists( 'get_plugins' ) ) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
  }
  $all_plugins = get_plugins();
  if ( !empty( $all_plugins[$slug] ) ) {
    return true;
  } else {
    return false;
  }
}
 
function frontend_admin_install_plugin( $plugin_zip ) {
  include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
  wp_cache_flush();
   
  $upgrader = new Plugin_Upgrader();
  $installed = $upgrader->install( $plugin_zip );
 
  return $installed;
}
 