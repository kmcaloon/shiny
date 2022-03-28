<?php 
namespace Shiny\Woo;

//\Shiny\Extras\include_from_current_and_subs( __DIR__ );


/* ------ = Assets. = --------------------------------------------------------------------- */


/**
 * Disable assets on non-woo pages.
 */
add_action( 'wp_enqueue_scripts', function() {

	if( ! function_exists( 'is_woocommerce' ) ) {
    return;
  }

  wp_dequeue_style( 'select2' );
  wp_dequeue_script( 'select2');
  wp_dequeue_script( 'selectWoo' );
 
  // Dequeue if not woocommerce.
  if( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) { 	

    wp_dequeue_style( 'woocommerce-layout' ); 
    wp_dequeue_style( 'woocommerce-general' ); 
    wp_dequeue_style( 'woocommerce-smallscreen' );
    wp_dequeue_style( 'wc-block-style' ); 	

    wp_dequeue_script( 'wc-cart-fragments' );
    wp_dequeue_script( 'woocommerce') ; 
    wp_dequeue_script( 'wc-add-to-cart' ); 
    wp_deregister_script( 'js-cookie' );
    wp_dequeue_script( 'js-cookie' );

  }
  // Enqueue if woocommerce
  else {

    wp_enqueue_script( 'jquery', wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js', false, '3.6.0' ) );

    $dist_url = \Shiny\Settings\dir( 'dist' );
    $dist_path = \Shiny\Settings\dir( 'dist', 'path' );
    $woo_js = "{$dist_path}/js/woo.js";

    if( file_exists( $woo_js ) ) {

      $full_script_url = "{$dist_url}/js/woo.js?ver=" . filemtime( $woo_js );

      wp_enqueue_script( 'woo.js', "{$dist_url}/js/woo.js", [ 'jquery' ], filemtime( $woo_js ), true );
      
      \Shiny\Assets\preload_asset( $full_script_url, 'script' );

    }
  }


}, 999 );

/**
 *  Custom header & footer.
 */
add_action( 'wp', function() {
  global $post;
  global $header_component;
  global $footer_component;
  
  if( ! is_cart() && ! is_checkout() ) {
    return;
  }

  // Set custom header/footer here...

} );

/* ------ = Schedule/Cron. = --------------------------------------------------------------------- */