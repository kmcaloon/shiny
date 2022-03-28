<?php
/**
 * General Site Settings
 */
namespace Shiny\Settings;

define( __NAMESPACE__ . '\FORCE_DEVMODE', false );
define( __NAMESPACE__ . '\CACHE_IN_DEVMODE', false );



/* ------ = Actions. = --------------------------------------------------------------------- */
add_action( 'wp_head', __NAMESPACE__ . '\\settings_to_js' );
add_action( 'admin_footer', __NAMESPACE__ . '\\settings_to_js' );

/**
 * General Settings page.
 */
add_action( 'acf/init', function() {

  if( ! function_exists( 'acf_add_options_page' ) ) {
    return;
  }

  acf_add_options_page(array(
		'page_title' 	=> 'General Setttings',
		'menu_title'	=> 'General Settings',
		'menu_slug' 	=> 'general-settings',
		'capability'	=> 'edit_posts',
		'redirect'		=> false
	) );

} );


/* ------ = Directories. = --------------------------------------------------------------------- */

/**
 * Get the paths to our various directories.
 *
 * @param   string  $directory
 * @param   string  $output     Optional. Default 'url'
 * @return  string  either url or path.
 */
function dir( $directory, $output = 'url' ) {

	$theme = $output == 'url' ? get_stylesheet_directory_uri() : get_stylesheet_directory();
	$dist = '/dist';

	switch ( $directory ) :

		case 'theme' :
			return $theme;
			break;

		case 'dist' :
			return "{$theme}{$dist}";
			break;

		default :
			return "{$theme}{$dist}/{$directory}";
			break;

	endswitch;

}

/* ------ = Global settings. = --------------------------------------------------------------------- */


/**
 * Check if we are in dev mode
 */
function in_devmode () {

	if( function_exists( 'is_wpe_snapshot' ) ) {

		if( is_wpe_snapshot() ) {
			return true;
		}
		else {
			return false;
		}
	}
	else if( strpos( $_SERVER['SERVER_NAME'], 'localhost' ) !== false || FORCE_DEVMODE ) {
		return true;
	}

  return false;

}

/**
 * Check to see if caching is on
 */
function caching_is_on() {

	if( in_devmode() && ! CACHE_IN_DEVMODE ) {
		return false;
	}
	else {
		return true;
	}
}

/**
 * Pass settings to frontend js.
 */
function settings_to_js() {

  $in_dev = in_devmode();
	$dir = dir( 'theme', 'path' );
	$config = json_decode( file_get_contents( "{$dir}/config.json" ), true );
  if( ! $in_dev ) {
    unset( $config['LOCAL_DIST_URL'] );
    unset( $config['DEV_DIST_URL'] );
  }
	$other_settings =  [
		'HOME_URL' 		  => home_url(),
    'IN_DEVMODE' 	  => $in_dev,
    'API'    => get_rest_url() . 'api/v1',
	];
  
	$settings = array_merge( $config, $other_settings );

  // Admin.
  if( is_user_logged_in() ) {
    $settings = array_merge( $settings, [
      'REST_NONCE' => wp_create_nonce( 'wp_rest' ),
    ] );
  }
	$settings_json = json_encode( $settings );
  ?>

	<script>
		var WP_SETTINGS = <?php echo $settings_json; ?>;
	</script>

<?php
}

