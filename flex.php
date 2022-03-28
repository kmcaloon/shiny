<?php
use Shiny\Components as c;
$name = $layout['name'];
$component_fields = [];
$component = null;


foreach( $layout['sub_fields'] as $field ) {
  $component_fields[$field['name']] = get_sub_field( $field['name'] );
}

// Customizations.
if( $name == 'MiscLogos' ) {
  $name = 'Logos';
  //debug( $component_fields );
}

// Default.
if( class_exists( "Shiny\\Components\\$name" ) ) {

  $component_class = "Shiny\\Components\\$name";

  $component = new $component_class( [
    'is_preview' => $is_preview,
    'fields'  => $component_fields
  ] );

  $component->render();

}
else {

  if( \Shiny\Settings\in_devmode() && ! class_exists( $component_class ) ) {
    debug( $component_class . $name );
  }
  else if( ! class_exists( $component_class ) ) {
    return;
  }

 
}






