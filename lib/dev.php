<?php
/**
 * View.
 *
 * @param   mixed   $variable.
 * @return  string  printed data about variable.
 */
if( ! function_exists( 'view' ) ) {

	function view( $variable ) {

		echo '<pre>';
		print_r( $variable );
		echo '</pre>';
	}
}

/**
 * Debug.
 *
 * @param   mixed   $variable.
 * @param   bool    $log. Optional.
 * @return  string  printed data about variable.
 */
if( ! function_exists( 'debug' ) ) {

  function debug( $variable, $log = false ) {

    if( $log ) {
      return error_log( print_r( $variable, true ) );
    }

    die( view( $variable ) );

  }

}

/**
 * Get widget ID.
 *
 * @param   WP_Widget   $widget_instance
 * @return  string      message | widget Id.
 */
add_action( 'in_widget_form', function( $widget_instance ) {

  // If widget isn't saved, show message.
  if( $widget_instance->number == "__i__" ){
    echo '<strong>Widget ID is</strong>: Please save the widget first!';
  }
  // Otherwise show widget ID.
  else {
    echo '<strong>Widget ID is: </strong>' . $widget_instance->id;
  }

} );
