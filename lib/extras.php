<?php
/**
 * Extra functions for theme
 */
namespace Shiny\Extras;


/**
 * Add <body> classes
 *
 * @param  array  $classes.
 * @return array
 */
add_filter( 'body_class', function( $classes ) {

  // Add page slug if it doesn't exist
  if ( is_single() || is_page() && !is_front_page() ) {
    if ( !in_array( basename( get_permalink() ), $classes ) ) {
      $classes[] = basename( get_permalink() );
    }
  }

  return $classes;

} );

/**
 * Get content
 * 
 * @param   int $post_id
 * @return  string
 */
function get_content( $post_id ) {
  return apply_filters( 'the_content', get_post_field( 'post_content', $post_id ) );
}

/**
 * Get all meta.
 * 
 * @param   int     $id
 * @param   string  $type
 * @return  array
 */
function get_all_meta( $id, $type = 'post' ) {

  $data = $type == 'post' ? (array) get_post( $id ) : (array) get_term( $id );
  $meta = null;
  $meta_array = $type == 'post' ? get_post_meta( $id, null, true ) : get_term_meta( $id );
  if( $meta_array ?? false ) {
    foreach( $meta_array as $key => $array ) {
      $meta[$key] = $array[0];
    }
  }

  $permalink = get_permalink( $id );
  $meta['url'] = $permalink;

  return array_merge( $data, $meta );

}

/**
 * Include all indexes in subdirectories
 *
 * @param string $dir
 */
function include_all_indexes( $dir ) {

  foreach( glob( $dir . '/*/_*.php' ) as $filename ) {
    include_once( $filename );
  }
  foreach( glob( $dir . '/*/*/_*.php' ) as $filename ) {
    include_once( $filename );
  }

}

/**
 * Include all files from current and sub directories.
 *
 * @param string $dir
 */
function include_from_current_and_subs( $dir ) {

  include_from_current( $dir );
  include_from_subs( $dir );

}

/**
 * Include all files from sub directories.
 *
 * @param string $dir
 */
function include_from_subs( $dir ) {

  foreach( glob( $dir . '/*/*.php' ) as $filename ) {

    include_once( $filename );

  }
  foreach( glob( $dir . '/*/*/*.php' ) as $filename ) {

    include_once( $filename );

  }

}

/**
 * Include all files from current.
 *
 * @param string $dir
 */
function include_from_current( $dir ) {

  foreach( glob( $dir . '/*.php' ) as $filename ) {

    include_once( $filename );

  }

}

/**
 *  Generate unique id.
 * 
 * @param   int       $length.
 * @return  string
 */
function generate_id( $length = 5 ) {
  return substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz' ), 0, $length );
}

/**
 * Round rating.
 * 
 * @param   int     $rating
 * @param   bool    $force_float
 * @return  int
 */
function round_rating( $rating, $force_float = false ) {
  
  $rounded = round( $rating * 2 ) / 2;
  
  if( $force_float && floor( $rounded ) == $rounded ) {
    $rounded = number_format( $rounded, 1 );
  }

  return $rounded;

}


/**
 * Slugify strings.
 *
 * @param   string $string
 * @return  string
 */
function slugify( $string ) {

  if ( ! is_string( $string ) ) {
    return;
  }

  $string = sanitize_text_field( $string );
  $string = str_replace( ' ', '-', $string );
  $string = str_replace( '/', '-', $string );
  $string = str_replace( '(', '', $string );
  $string = str_replace( ')', '', $string );

  return strtolower( $string );

}

/**
 * Get string between two strings.
 *
 * @param string    $str
 * @param string    $start
 * @param string    $end.
 * @return string
 */
function string_between( $string, $start, $end ) {

  $string = ' ' . $string;
  $ini = strpos( $string, $start );
  if( $ini == 0 ){
    return '';
  } 

  $ini += strlen( $start );
  $len = strpos( $string, $end, $ini ) - $ini;

  return substr( $string, $ini, $len );

}


/**
 * Helper for custom post type labels
 *
 * @param string $singular  Singular label.
 * @param string $plural    Plural label.
 *
 * @return array  labels for the CPT.
 */
function custom_labels( $singular, $plural ) {

  return [
    'name'               => __( $plural ),
    'singular_name'      => __( $singular ),
    'menu_name'          => __( $plural ),
    'add_new'            => __( 'Add New ' .  $singular ),
    'add_new_item'       => __( 'Add New ' . $singular ),
    'new_item'           => __( 'New ' . $singular ),
    'edit_item'          => __( 'Edit ' . $singular ),
    'view_item'          => __( 'View ' . $singular ),
    'all_items'          => __( 'All ' . $plural ),
    'search_items'       => __( 'Search ' . $plural ),
    'parent_item_colon'  => __( 'Parent ' . $singular . ':' ),
    'not_found'          => __( 'No ' . $plural . ' found.' ),
    'not_found_in_trash' => __( 'No ' . $plural . ' found in Trash.' )
  ];

}

/**
 * Remove base from custom post type slugs.
 *
 * @param   string  $name
 * @return  string
 */
function remove_cpt_base( $name ) {

  // Customized post link.
  add_filter( 'post_type_link', function( $post_link, $post, $leavename ) use( &$name ) {

    if ( isset( $post->post_type ) && $post->post_type == $name ) {
      $post_link = home_url( $post->post_name );
    }

    return $post_link;

  }, 10, 3 );

}

/**
 * File data
 *
 * @param int     $id   Post id for the file
 * @return Object       File's attributes
 */
function wp_file( $id ) {

  $atts = get_post( $id );

  return $atts;

}

/**
 * Image urls.
 *
 * @param   string  $img_id
 * @param   string  $size
 * @return  object
 */
function wp_image( $img_id, $size = 'full' ) {

  $data = function_exists( 'acf_get_attachment' ) ? acf_get_attachment( $img_id ) : [];

  return $data;

}

/* ------ = HTML Helpers. = --------------------------------------------------------------------- */

/**
 * HTML atts.
 *
 * @param     array   $args
 * @param     array   $options
 * @param     array   $options['exclude']
 * @param     array   $options['include']
 * @return    string
 */
function html_attrs( $args, $options ) {

  $attrs = '';
  if( !! $args ) {
    foreach( $args as $key => $value ) {

      if(
        ! $value ||
        ( isset( $options['exclude'] ) && in_array( $key, $options['exclude'] ) )
      ) {
        continue;
      }
      else if( isset( $options['include'] ) ) {
        if( !! $value && in_array( $key, $options['include'] ) ) {
          if( is_array( $value ) ) {
            continue;
          }
          $attrs .= $key . '="' . htmlspecialchars( $value ) . '" ';
        }

      }
      else {
        $attrs .= $key . '="' . htmlspecialchars( $value ) . '" ';
      }

    }
  }

  return $attrs;

}


/* ------ = Performance. = --------------------------------------------------------------------- */

/**
 * Args for more efficient database queries
 * 
 * @link https://10up.github.io/Engineering-Best-Practices/php#efficient-database-queries
 *
 *
 * @param array   $args           Arguments for specific query
 * @param bool    $rows           Optional. When pagination is not needed. Default true (optimized).
 * @param bool    $meta_cache     Optional. When post meta will not be utilized. Default true (unoptimized).
 * @param bool    $term_cache     Optional. When taxonomy terms will not be utilized. Default true (unoptimized).
 * @param bool    $ids            Optional. When only the post IDs are needed. Default false (unoptimize).
 * @return array for WP_Query
 */
function optimize_query( $args, $rows = true, $meta_cache = true, $term_cache = true, $ids = false ) {

  $optimized_args = [
    'no_found_rows' => $rows,
    'update_post_meta_cache' => $meta_cache,
    'update_post_term_cache' => $term_cache,
    'fields' => $ids,
  ];

  $args = array_merge( $optimized_args, $args );

  return $args;

}

/**
 *  Cache portions of repetitive code
 *  @link https://css-tricks.com/wordpress-fragment-caching-revisited/
 * 
 * @param   string    $key
 * @param   string    $ttl
 * @param   function  $#function
 * @return  html
 */
function fragment_cache( $key, $ttl, $function ) {

  if( caching_is_on() ) {

    $key = apply_filters( 'fragment_cache_prefix', 'fragment_cache_' ) . $key;
    $output = get_transient( $key );

    if ( empty( $output ) ) {

      ob_start();
      call_user_func ($function );
      $output = ob_get_clean();
      set_transient( $key, $output, $ttl );
    }
  }
  else {

    ob_start();
    call_user_func( $function );
    $output = ob_get_clean();
  }

  echo $output;
}



/* ------ = Queries. = --------------------------------------------------------------------- */

/**
 * Extend get terms with post type parameter.
 * @link https://www.dfactory.eu/get_terms-post-type/
 *
 * @param string  $clauses
 * @param string  $taxonomy
 * @param array   $args
 * @return string
 */
add_filter( 'terms_clauses', function( $clauses, $taxonomy, $args ) {

  if ( isset( $args['post_type'] ) && ! empty( $args['post_type'] ) && $args['fields'] !== 'count' ) {

    global $wpdb;

    $post_types = array();

    if ( is_array( $args['post_type'] ) ) {
      foreach ( $args['post_type'] as $cpt ) {
        $post_types[] = "'" . $cpt . "'";
      }
    } else {
      $post_types[] = "'" . $args['post_type'] . "'";
    }

    if ( ! empty( $post_types ) ) {
      $clauses['fields'] = 'DISTINCT ' . str_replace( 'tt.*', 'tt.term_taxonomy_id, tt.taxonomy, tt.description, tt.parent', $clauses['fields'] ) . ', COUNT(p.post_type) AS count';
      $clauses['join'] .= ' LEFT JOIN ' . $wpdb->term_relationships . ' AS r ON r.term_taxonomy_id = tt.term_taxonomy_id LEFT JOIN ' . $wpdb->posts . ' AS p ON p.ID = r.object_id';
      $clauses['where'] .= ' AND (p.post_type IN (' . implode( ',', $post_types ) . ') OR p.post_type IS NULL)';
      $clauses['orderby'] = 'GROUP BY t.term_id ' . $clauses['orderby'];
    }
  }
  return $clauses;

}, 10, 3 );

/**
 * Get Adjacent Terms
 *
 * Retrieves prev and next terms for current taxonomy term
 * @link http://wordpress.stackexchange.com/questions/99513/how-to-get-next-previous-category-in-same-taxonomy
 *
 */
class Adjacent_Terms {

  public $sorted_taxonomies;

  /**
   * @param string Taxonomy name. Defaults to 'category'.
   * @param string Sort key. Defaults to 'id'.
   * @param boolean Whether to show empty (no posts) taxonomies.
   */
  public function __construct( $taxonomy = 'category', $order_by = 'id', $skip_empty = true ) {

    $this->sorted_taxonomies = get_terms(
      $taxonomy,
      array(
        'get'          => $skip_empty ? '' : 'all',
        'fields'       => 'ids',
        'hierarchical' => false,
        //'order'        => 'DESC',
        //'orderby'      => $order_by,
      )
    );
  }

  /**
   * @param int Taxonomy ID.
   * @return int|bool Next taxonomy ID or false if this ID is last one. False if this ID is not in the list.
   */
  public function next( $taxonomy_id ) {

    $current_index = array_search( $taxonomy_id, $this->sorted_taxonomies );

    if ( false !== $current_index && isset( $this->sorted_taxonomies[ $current_index + 1 ] ) )
      return $this->sorted_taxonomies[ $current_index + 1 ];

    return false;

  }

  /**
   * @param int Taxonomy ID.
   * @return int|bool Previous taxonomy ID or false if this ID is last one. False if this ID is not in the list.
   */
  public function previous( $taxonomy_id ) {

    $current_index = array_search( $taxonomy_id, $this->sorted_taxonomies );

    if ( false !== $current_index && isset( $this->sorted_taxonomies[ $current_index - 1 ] ) )
      return $this->sorted_taxonomies[ $current_index - 1 ];

    return false;

  }
}