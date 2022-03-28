<?php
namespace Shiny\Admin;


/**
 * Add user roles to body.
 *
 * @param   string  $classes.
 * @return  string
 */
add_filter( 'admin_body_class', function( $classes ) {

  $current_user = new \WP_User( get_current_user_id() );
  $user_role = array_shift( $current_user->roles );
  $user_username = $current_user->user_login;
  $classes .= ' user-' . $user_username . ' role-'. $user_role;

  return $classes;

} );


/**
 * Enqueue admin assets
 */
add_action( 'admin_enqueue_scripts', function() {

  $dist_url = \Shiny\Settings\dir( 'dist' );
  $dist_path = \Shiny\Settings\dir( 'dist', 'path' );

  $admin_stylesheet = "{$dist_path}/css/admin.css";
  $global_stylesheet = "{$dist_path}/css/global.css";
  $head_js = "{$dist_path}/js/admin-head.js";
  $admin_js = "{$dist_path}/js/admin.js";
  $global_js = "{$dist_path}/js/global.js";

  if( file_exists( $admin_stylesheet ) ) {
    wp_enqueue_style( 'admin.css', "{$dist_url}/css/admin.css", [], filemtime( $admin_stylesheet ) );
  };

  if( file_exists( $head_js) ) {
    wp_enqueue_script( 'admin-head.js', "{$dist_url}/js/admin-head.js", [], filemtime( $head_js ), false);
  }
  if( file_exists( $admin_js ) ) {
    wp_enqueue_script( 'admin.js', "{$dist_url}/js/admin.js", [], filemtime( $admin_js ), true );
  }

  if( file_exists( $global_js ) ) {

    wp_enqueue_script( 'head-scripts.js', "{$dist_url}/js/head-scripts.js", [], filemtime( $head_js ), false );
    wp_enqueue_script( 'global.js', "{$dist_url}/js/global.js", [], filemtime( $global_js ), true );

  }

  // Alpine.
  wp_enqueue_script( 'alpine.js', "https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.8.0/dist/alpine.min.js#async#defer", [], null, false);
  
  //wp_enqueue_script( 'jquery-ui', "https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js", [], null, false);
  
} );

/**
 * Add fonts to admin.
 */
add_action( 'admin_head', function() {

  \Shiny\Assets\load_fonts();
  
} );

