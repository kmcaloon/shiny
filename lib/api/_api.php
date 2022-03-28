<?php
namespace Shiny\API;


/* ------------------------- = Constants = ------------------------- */

/**
* Namespace within wp-json
*/
define( __NAMESPACE__ . '\\REST_NAMESPACE', 'api/v1' );


/* ------------------------- = Init = ------------------------- */

/**
 * Always issue new oauth refresh token.
 */
add_filter( 'wp_always_issue_new_refresh_token','__return_true' );


/**
 * Register routes defined within ./API.php
 */
add_action( 'rest_api_init', function() {

  $api = new Shiny_API();
  $api->register_routes();

} );


/* ------------------------- = Misc = ------------------------- */
