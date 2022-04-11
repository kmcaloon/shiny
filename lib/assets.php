<?php
namespace Shiny\Assets;

use MatthiasMullie\Minify;
use Padaliyajay\PHPAutoprefixer\Autoprefixer;

/* ------ = Actions. = --------------------------------------------------------------------- */
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\override_defaults', 999  );
add_action( 'wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_frontend_assets' );
add_filter( 'wp_resource_hints', __NAMESPACE__ . '\\disable_emojis_remove_dns_prefetch', 10, 2 );
add_filter( 'wp_check_filetype_and_ext', __NAMESPACE__ . '\\add_mime_types', 10, 4 );
add_filter( 'clean_url', __NAMESPACE__ . '\\extend_asset_urls', 11, 1 );

/* ------ = Functions. = --------------------------------------------------------------------- */

/**
 * Load fonts.
 */
function load_fonts() {

  $fontdir = \Shiny\Settings\dir( 'fonts' );

  // Examples.
  $fonts = [
    // 'proxima-nova'  => [
    //   'fallbacks' => 'system-ui, -apple-system, "Segoe UI", Roboto, Arial, sans-serif',
    //   'weights' => [
    //     'light'   => 300,
    //     'regular' => 400,
    //     'bold'    => 700,
    //   ],
    //   'italic'  => [
    //     'light'
    //   ]
    // ],
  ];
  ?>

  <?php foreach( $fonts as $family => $data ) : ?>

    <?php if( ! $data ) : ?>

      <link rel="preload" as="font" href="<?php echo "$fontdir/$family/webfont.woff2" ;?>" crossorigin="anonymous"></link>

    <?php else : ?>
    
      <?php if( isset( $data['weights'] ) ) : foreach( $data['weights'] as $name => $data ) : ?>
        <link rel="preload" as="font" href="<?php echo "$fontdir/$family/$name/webfont.woff2" ;?>" crossorigin="anonymous"></link>
      <?php endforeach; endif; ?>

      <?php if( isset( $data['italic'] ) ) : foreach( $data['italic'] as $name ) : ?>
        <link rel="preload" as="font" href="<?php echo "$fontdir/$family/$name-italic/webfont.woff2" ;?>" crossorigin="anonymous"></link>
      <?php endforeach; endif; ?>

    <?php endif; ?>
  
  <?php endforeach; ?>

  <style>
   <?php foreach( $fonts as $family => $data ) : ?>

      <?php foreach( $data['weights'] as $name => $weight ) : ?>
        @font-face {
          font-family: <?php echo $family; ?>;
          font-weight: <?php echo $weight; ?>;
          font-style: normal;
          font-display: swap;
          src:  url( <?php echo "$fontdir/$family/$name/webfont.woff2" ;?> ),
                url( <?php echo "$fontdir/$family/$name/webfont.woff"; ?> );
        }
      <?php endforeach; ?>

      <?php foreach( $data['italic'] as $name ) : ?>
        @font-face {
          font-family: <?php echo $family; ?>;
          font-weight: <?php echo $data['weights'][$name]; ?>;
          font-style: italic;
          font-display: swap;
          src:  url( <?php echo "$fontdir/$family/$name-italic/webfont.woff2" ;?> ),
                url( <?php echo "$fontdir/$family/$name-italic/webfont.woff"; ?> );
        }
      <?php endforeach; ?>

    <?php endforeach; ?>
  </style>

<?php
}

/**
 * MIME types.
 * @link http://codepen.io/chriscoyier/post/wordpress-4-7-1-svg-upload
 * 
 * @param   array   $data
 * @param   string  $file
 * @param   string  $filename
 * @param   array   $mimes
 * @return  array
 */
function add_mime_types( $data, $file, $filename, $mimes ) {

  global $wp_version;

  if( $wp_version == '4.7' || ( (float) $wp_version < 4.7 ) ) {
    return $data;
  }

  $filetype = wp_check_filetype( $filename, $mimes );

  return [
    'ext'             => $filetype['ext'],
    'type'            => $filetype['type'],
    'proper_filename' => $data['proper_filename']
  ];

}

/**
 * Extend asset urls.
 * @link https://ikreativ.com/async-with-wordpress-enqueue/
 * 
 * @param   string    $url
 * @return  string
 */
function extend_asset_urls( $url ) {

	if( strpos( $url, '#') === false ) {
    return $url;
	}
  else if( strpos( $url, '#async' ) ) {
    return str_replace( '#async', '', $url ) . "' async='async";
	}
  else if( strpos( $url, '#defer' ) ) {
    return str_replace( '#defer', '', $url ) . "' defer='defer";
  }

  return $url;

}

/**
 * Preload asset.
 *
 * @param string  $url
 * @param string  $type
 */
function preload_asset( $url, $type ) {

  add_action( 'wp_head', function() use ( $url, $type ) {
    ?>
      <link rel="preload" href="<?php echo $url; ?>" as="<?php echo $type; ?>" />
    <?php
  } );

}

/**
 *  Print page styles.
 * 
 * @param string  $name
 * @return html
 */
function print_page_styles( $name ) {

  $dist_url = \Shiny\Settings\dir( 'dist' );
  $dist_path = \Shiny\Settings\dir( 'dist', 'path' );
  $url = "$dist_url/css/$name.css";
  $file =  "$dist_path/css/$name.css";
  $version = filemtime( $file );
  
  if( \Shiny\Settings\in_devmode() ) {
    echo '<link rel="stylesheet" href="' . $url . '" />';
  }
  else {
  ?>
    <style>
      <?php include( $file ); ?>
    </style>

  <?php
  }

}

/**
 *  Print page scripts
 * 
 * @param string  $name
 * @return html
 */
function print_page_scripts( $name ) {

  $dist_url = \Shiny\Settings\dir( 'dist' );
  $dist_path = \Shiny\Settings\dir( 'dist', 'path' );
  $url = "$dist_url/js/$name.js";
  $file =  "$dist_path/js/$name.js";
  $version = filemtime( $file );
  
  if( \Shiny\Settings\in_devmode() ) {
    echo '<script src="' . $url . '"></script>';
  }
  else {
  ?>
    <script>
      <?php include( $file ); ?>
    </script>

  <?php
  }

}

/**
 * Enqueue global assets.
 */
function enqueue_frontend_assets() {

  if( is_admin() ) {
    return;
  }

  $dist_url = \Shiny\Settings\dir( 'dist' );
  $dist_path = \Shiny\Settings\dir( 'dist', 'path' );

  $global_stylesheet = "{$dist_path}/css/global.css";
  $global_js = "{$dist_path}/js/global.js";
  $head_js = "{$dist_path}/js/head-scripts.js";

  // Global stylesheet.
  if( file_exists( $global_stylesheet ) ) {
    wp_enqueue_style( 'global.css', "{$dist_url}/css/global.css", [], filemtime( $global_stylesheet ) );

  }
  // Header scripts.
  if( file_exists( $head_js ) ) {

    wp_enqueue_script( 'head-scripts.js', "$dist_url}/js/head-scripts.js", [], filemtime( $head_js ), false);

    // $full_script_url = "{$dist_url}/js/global.js?ver=" . filemtime( $global_js );
    //
    // preload_asset( $full_script_url, 'script' );

  }
  // Global js.
  if( file_exists( $global_js ) ) {

    wp_enqueue_script( 'global.js', "$dist_url}/js/global.js", [], filemtime( $global_js ), true );

    $full_script_url = "{$dist_url}/js/global.js?ver=" . filemtime( $global_js );

    preload_asset( $full_script_url, 'script' );

  }


}

/**
 * Override WP defaults.
 */


function override_defaults() {

  if( is_admin() || is_cart() || is_checkout() ) {
    return;
  }

  global $wp_filter;


  $enqueues = $wp_filter['wp_enqueue_scripts'][10];
  $updated_filter = $wp_filter;

  foreach( $enqueues as $key => $args ) {

    if( 
      str_contains( $key, 'possiblyEnqueueScripts' ) ||
      str_contains( $key, 'register_plugin_styles' ) ||
      str_contains( $key, 'add_frontend' ) ||
      str_contains( $key, 'enqueue_styles' ) ||
      str_contains( $key, 'load_scripts' )
    ) {
      //unset( $updated_filter['wp_enqueue_scripts'][10][$key] );
      remove_action( 'wp_enqueue_scripts', $key, 10 );
    }
  }

  remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles', 10 );
  remove_action( 'wp_enqueue_scripts', 'wp_common_block_scripts_and_styles', 10 );

  wp_dequeue_style( 'wp-block-library' );
  wp_dequeue_style( 'wp-block-library-theme' );
  wp_dequeue_style( 'wc-blocks-style' );
  wp_deregister_style( 'wp-block-library' );
  wp_deregister_style( 'wp-block-library-theme' );
  wp_deregister_style( 'wc-blocks-style' );
  wp_dequeue_script( 'duplicate-post' );
  wp_deregister_script( 'duplicate-post' );

  wp_dequeue_style( 'activecampaign-for-woocommerce' );
  wp_dequeue_style( 'woocommerce-nyp' );


  if( \Shiny\KILL_JQUERY ) {
    wp_deregister_script( 'jquery' );
    wp_dequeue_script( 'jquery' );
  }


  wp_deregister_script( 'wp-embed' );

  remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
  remove_action( 'wp_print_styles', 'print_emoji_styles' );
  remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
  remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
  
  wp_dequeue_script( 'regenerator-runtime' );
  wp_deregister_script( 'regenerator-runtime' );
  wp_dequeue_script( 'wp-a11y' );
  wp_deregister_script( 'wp-a11y' );
  wp_dequeue_script( 'wp-polyfill' );
  wp_deregister_script( 'wp-polyfill' );
  wp_dequeue_script( 'regenerator-runtime' );
  wp_deregister_script( 'regenerator-runtime' );

}


/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param array $urls             URLs to print for resource hints.
 * @param string $relation_type   The relation type the URLs are printed for.
 * @return array Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch( $urls, $relation_type ) {

  if( is_admin() ) {
    return $urls;
  }

  $emoji_svg_url = null;

  if( 'dns-prefetch' == $relation_type ) {
    /** This filter is documented in wp-includes/formatting.php */
    $emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );
  }

  $urls = array_diff( $urls, array( $emoji_svg_url ) );

  return $urls;

}

/**
 * Display svg icon.
 *
 * @param   string  $name
 * @param   string  $class
 * @return  html
 */
function icon( $name, $class = '' ) {
  
  $is_svg = false;

  if( str_contains( $name, '.svg' ) ) {

    $is_svg = true;
    if( stripos( $name, 'icon' ) === false ) {
      $file = "icon-$name";
    }
  }
  else {

    if( str_contains( $name, 'icon-' ) ) {
      $name = str_replace( 'icon-', '', $name );
    }
    $class .= " i-$name";

  }
  ?>

    <i 
    class="<?php echo $class; ?>"
    role="presentation"
    >
      <?php if( $is_svg ) { echo svg( $file ); } ?>
    </i>
    
  <?php
}

/**
 * Display svg.
 *
 * @param   string  $file
 * @return  html
 */
function svg( $file ) {

  if( stripos( $file, '.svg' ) === false ) {
    $file = "$file.svg";
  }

  $path = \Shiny\Settings\dir( 'img' ) . "/$file";

  if( file_exists( $path ) ) {
    return file_get_contents( $path );
  }

}

/**
 *  Add data-bg attribute to element.
 * 
 * @param  string $file
 * @return string
 */
function element_bg( $file ) {

  $url = \Shiny\Settings\dir( 'img' ) . "/$file";
  return "data-bg='$url' data-background-image='$url'";

}

/**
 *  Local image.
 * 
 * @param  string $file
 * @return string
 */
function local_image( $file ) {

  $url = \Shiny\Settings\dir( 'img' ) . "/$file";
  return $url;

}

/**
 * Minify html.
 *
 * @param string    $output
 * @return string
 */
function minify_html( $output ) {

  $search = array(
    '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
    '/[^\S ]+\</s',     // strip whitespaces before tags, except space
    '/(\s)+/s',         // shorten multiple whitespace sequences
    //'/<!--(.|\s)*?-->/' // Remove HTML comments
  );

  $replace = array(
    '>',
    '<',
    '\\1',
    ''
  );

  return preg_replace( $search, $replace, $output );

}

/**
 * Process inline styles.
 *
 * @param string    $output
 * @return string
 */
function extract_inline_styles( $output ) {

  // Find all inline css.
  preg_match_all( '/_css{(.+?)}/s', $output, $inline_matches );

  if( !! $inline_matches[1] ){
    $i = 0;
    foreach( $inline_matches[1] as $match ) {
      debug( $inline_matches[0][$i] );
      $i++;


    }
  }
  // Add prefixes.


  // Minify.


}
