<?php
use GuzzleHttp\Promise;


/**
 * Load add on.
 */
( function() {

  if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
    return;
  }
  
  \GFForms::include_addon_framework();
  
  require_once( 'TrackingAddOn.php' );
  
  \GFAddOn::register( 'Shiny\Forms\TrackingAddOn' );
  
  return \Shiny\Forms\TrackingAddOn::get_instance();
  
} )();

/**
 * Predefined choices.
 */
// add_filter( 'gform_predefined_choices', function( $choices ) {


//   return array_merge( $custom, $choices );


// }, 20 );


/**
 * Enable hidden label option
 */
add_filter( 'gform_enable_field_label_visibility_settings', '__return_true' );


/**
 * Custom gravity forms shortcode output.
 * 
 * @param   string        $output.
 * @param   string        $tag.
 * @param   array|string  $attr.
 * @return  string ?      
 */
add_filter( 'do_shortcode_tag', function( $output, $tag, $attrs ) {

  if( $tag !== 'gravityform' ) {
    return $output;
  }

  $form = new \Shiny\Components\Form( array_merge( [ 'gform_id' => $attrs['id'] ], $attrs ) );

  ob_start();

  $form->render();

  $output = ob_get_clean();

  return $output;

}, 20, 3 );


/**
 * Disable frontend assets.
 */
add_action( 'gform_enqueue_scripts', function() {
  if( is_admin() ) {
    return;
  }

  wp_dequeue_style( 'gforms_reset_css' );
  wp_dequeue_style( 'gforms_datepicker_css' );
  wp_dequeue_style( 'gforms_formsmain_css' );
  wp_dequeue_style( 'gforms_ready_class_css' );
  wp_dequeue_style( 'gforms_browsers_css' );
  wp_dequeue_script( 'gform_conditional_logic' );
  wp_dequeue_script( 'gform_datepicker_init' );
  wp_dequeue_script( 'gform_gravityforms' );
  wp_dequeue_script( 'gform_json' );
  wp_dequeue_script( 'gform_masked_input' );
  wp_dequeue_script( 'gform_placeholder' );

}, 20 );

add_filter( 'gform_init_scripts_footer', function() {
  return false;
} );
add_filter( 'gform_get_form_filter', function() {
  return false;
} );
add_filter( 'gform_disable_print_form_scripts', '__return_true' );
