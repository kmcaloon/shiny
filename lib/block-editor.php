<?php
namespace Shiny\Editor;


/**
 * Set up post types.
 */
add_filter( 'use_block_editor_for_post_type', function( $enabled, $post_type ) {

  $enabled = false;
  $enabled_post_types = [

  ];

  if( in_array( $post_type, $disabled_post_types ) ) {
    $enabled = true;
  }

  return $enabled;

}, 10, 2 );


/**
 * Allowed block types.
 */
add_filter( 'allowed_block_types', function( $blocks, $post ) {

  return $blocks;

}, 10, 2 );

/**
 * Make sure all blocks are in preview mode after save
 */
add_action( 'wp_insert_post_data', function( $data, $array ) {

  if( empty( $data['post_content'] ) || ! str_contains( $data['post_content'], '<!-- wp:' ) ) {
    return $data;
  }

  $data['post_content'] = str_replace( '\"mode\": \"edit\"', '\"mode\": \"preview\"', $data['post_content'] );

  return $data;

}, 20, 2 );


/* ------ = Misc functions. = --------------------------------------------------------------------- */


/**
 * Check if currently in block editor.
 *
 * @return bool
 */
function in_editor() {

  if( ! function_exists( 'get_current_screen' ) ) {
    return false;
  }

  $screen = get_current_screen();
  return $screen->is_block_editor;

}

/**
 * Show block's preview image.
 * 
 * @param string  $dir 
 * @return html
 */
function block_preview_image( $dir ) {

  $path = explode( '/src', $dir );
  $url = get_stylesheet_directory_uri() . "/src/{$path[1]}/preview.jpg";
  ?>
    <img src="<?php echo $url; ?>" />

  <?php
}

/**
 *  Default image placeholder.
 * 
 * @return  string
 */
function img_placeholder() {
  return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC';
}

/**
 * Get post id from block.
 * 
 * @return int
 */
function get_post_id() {

  $post_id = get_the_ID() ? get_the_ID() : $_POST['post_id'];
  return $post_id;
  
}

/**
 * Get block content from a post.
 * 
 * @link https://florianbrinkmann.com/en/display-specific-gutenberg-blocks-of-a-post-outside-of-the-post-content-in-the-theme-5620/
 * 
 * @param   int|WP_Post
 * @return  html
 */
function get_block_content( $post ) {

  $content = get_the_content( null, null, $post );
  $priority = has_filter( 'the_content', 'wpautop' );

	if ( false !== $priority && doing_filter( 'the_content' ) && has_blocks( $content ) ) {
		remove_filter( 'the_content', 'wpautop', $priority );
		add_filter( 'the_content', '_restore_wpautop_hook', $priority + 1 );
	}

	$blocks = parse_blocks( $content );
	$output = '';

	foreach ( $blocks as $block ) {
		$output .= render_block( $block );
	}

	return $output;
  
}

/**
 * Get block fields.
 * 
 * Adapted from https://gist.github.com/jenssogaard/54a1927ecf51c3238bd3eff1dac73114
 * Should only be used within block loops.
 *
 * @param   object   $block.
 * @return  array
 */
function get_block_fields( $block ) {

  $block_id = $block['attrs']['id'];

  acf_setup_meta( $block['attrs']['data'], $block_id, true );
  $fields = get_fields();
  acf_reset_meta( $block_id );

  return $fields;

}




/* ------ = Register blocks. = --------------------------------------------------------------------- */
add_action( 'acf/init', function() {

  if( ! function_exists( 'acf_register_block_type' ) ) {
    return;
  }


} );