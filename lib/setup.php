<?php
/**
 * Theme setup, sidebars, etc
*/
namespace Shiny\Setup;


/* ------ = Actions/Filters. = --------------------------------------------------------------------- */


/**
 * Add custom headers.
 */
add_action( 'send_headers', function () {

	//header( 'Access-Control-Allow-Origin: *' );
  header( 'X-Frame-Options: "sameorigin"' );

}, 20 );

/**
 * Allow style tags in html rendering
 */
add_filter( 'wp_kses_allowed_html', function( $tags, $context ) {


  // Hmm. For now this is how we are allowing alpine within ACF message fields.
  if( $context == 'acf' ) {
    $tags['style'] = [];
    $tags['div']['x-*'] = true;
    $tags['button'][':class'] = true;
    $tags['button']['@click'] = true;
  }

  return $tags;
  
}, 99, 2 );

/**
 * Allow for breaks inside WYSIWYG
 */
add_filter( 'the_content', function( $content ) {
  
  $content = str_replace( '<br/>', '<br clear="none" />', $content  );
  $content = str_replace( '<br>', '<br clear="none" />', $content  );

  return $content;

} );
add_filter( 'acf/format_value/type=wysiwyg', function( $content ) {
  
  $content = str_replace( '<br/>', '<br clear="none" />', $content  );
  $content = str_replace( '<br>', '<br clear="none" />', $content  );

  return $content;
  
}, 10, 1 );

/**
 * Add div around iframes.
 */
add_filter( 'embed_oembed_html', function( $html, $url, $attr ) {

  $youtube = str_contains( $html, 'youtube' );
  ob_start();
  ?>
    <div class="iframe-wrap rel <?php echo $youtube ? 'is-youtube' : ''; ?>">
      <?php echo $html; ?>
    </div>

  <?php

  return ob_get_clean();
  
}, 10, 3 );

/**
 *  Remove the h1 tag from the WordPress editor.
 *
 *  @param   array  $settings  The array of editor settings
 *  @return  array             The modified edit settings
 */
add_filter( 'tiny_mce_before_init', function( $settings ) {

  $settings['block_formats'] = 'Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre;';
  $settings['formats'] = str_replace( 'h1,', '', $in['formats'] );
  $settings['wp_shortcut_labels'] = str_replace( '"Heading 1":"access1",', '', $in['wp_shortcut_labels'] );

  return $settings;

} );

/**
 *  Use ACF to set site title and tagline
 */
add_filter( 'acf/update_value/name=site_tagline', function( $value ) {
  
  update_option( 'blogdescription', str_replace( '\\', '', sanitize_text_field( $value ) ));

  return $value;

}, 10, 1 );

/**
 * Add custom cron schedules
 */
add_filter( 'cron_schedules', function( $schedules ) {

  // Weekly.
  $schedules['weekly'] = [
    'interval'  => 60 * 60 * 24 * 7,
    'display'   => 'Weekly',
  ];
  return $schedules;
  
} );


/**
 * Export/save admin menu changes
 */
add_action( 'update_option_ws_menu_editor', function( $old, $new ) {

  $value = get_option( 'ws_menu_editor' );

  file_put_contents( get_stylesheet_directory() . '/admin-menu.json', json_encode( $value ) );

}, 10 , 2 ) ;

/**
 * Add admin menu importer
 */
add_action( 'admin_bar_menu', function( $admin_bar ) {

  $admin_bar->add_node( [
    'id'      => 'sync-adminmenu',
    'parent'  => 'top-secondary',
    'title'   => '
      <div 
      x-data="alpineFetchBtn()"
      @click="runFetch( {
        route: `/admin`,
        method: `POST`,
        errorMessage: `Oops, we have a problem.`,
        showSpinner: true,
      } )"
      >
        Sync Admin
      </div>',
    'href'    => '#',
  ] );

}, 100 );

/*
* Set up image upload sizes.
*/
add_filter( 'big_image_size_threshold', function( $threshold ) {
  return 3000;
}, 999, 1 );

add_filter( 'intermediate_image_sizes_advanced', function( $sizes ) {

  unset( $sizes['medium'] );
  unset( $sizes['large'] );
  unset( $sizes['2048x2048'] );

  return $sizes;

} );

/**
 * Customize the template hierarchy.
 *
 * @param 	string 	$template  The full path to the template.
 * @return 	string 	The finalized path.
 */
// add_filter( 'template_include', function( $template ) {

//   if( ! \Shiny\CUSTOM_TEMPLATE_HIERARCHY ) {
//     return $template;
//   }

// }

/* ------ = Theme support. = --------------------------------------------------------------------- */

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
add_action( 'after_theme_setup', function() {

	/*
	* Make theme available for translation.
	* Translations can be filed in the /languages/ directory.
	*/
	load_theme_textdomain( 'shiny', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
	* Let WordPress manage the document title.
	* By adding theme support, we declare that this theme does not use a
	* hard-coded <title> tag in the document head, and expect WordPress to
	* provide it for us.
	*/
	add_theme_support( 'title-tag' );

	/*
	* Enable support for Post Thumbnails on posts and pages.
	*
	* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
	*/
	add_theme_support( 'post-thumbnails' );

  /**
   * Responsive embeds.
   */
  add_theme_support( 'responsive-embeds' );

	/**
	* Menus
	*/
	// register_nav_menus( array(
	// 	'menu_id'    => 'Menu Name'
	// ) );


	/*
	* Switch default core markup for search form, comment form, and comments
	* to output valid HTML5.
	*/
	add_theme_support( 'html5', [
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
  ] );

  /**
   *  Woocommerce.
   */
  //add_theme_support( 'woocommerce' );

	/*
	* Enable support for Post Formats.
	* See https://developer.wordpress.org/themes/functionality/post-formats/
	*/
	// add_theme_support( 'post-formats', array(
	// 	'aside',
	// 	'image',
	// 	'video',
	// 	'quote',
	// 	'link',
	// ) );

} );