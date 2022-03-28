<?php
namespace RA;



/**
* Component
*
* This parent class contains model and view functions to help
* create components to render in our templates.
*/
class Component {

  /**
   * Handle.
   *
   * @access protected
   * @var string
   */
  protected $handle;

  /**
   * Path.
   *
   * @access protected
   * @var string
   */
  protected $path;

  /**
   * Args
   *
   * @access protected
   * @var array
   */
  protected $args;

  /**
   * Path to stylesheet.
   *
   * @access protected
   * @var array
   */
  protected $style;

  /**
   * Path to js script.
   *
   * @access protected
   * @var array
   */
  protected $script;

  /**
   * Whether or not we are in preview in admin
   * 
   * @var bool
   */
  protected $is_preview;

  /**
   * Construct.
   *
   * @access  public
   * @param   string  $handle
   * @param   string  $dir
   * @param   array   $args     Optional.
   */
  public function __construct( $handle, $dir, $args = [] ) {

    $css_handle = "{$handle}.css";
    $js_handle =  "{$handle}.js";

    $this->handle = $handle;
    $this->path = $dir;
    $this->args = $args;
    $this->style = [
      'filename' => $css_handle,
      'url'      => get_stylesheet_directory_uri() . "/dist/css/{$css_handle}",
      'path'     => get_stylesheet_directory() . "/dist/css/${css_handle}",
    ];
    $this->script = [
      'filename' => $js_handle,
      'url'      => get_stylesheet_directory_uri() . "/dist/js/{$js_handle}",
      'path'     => get_stylesheet_directory() . "/dist/js/${js_handle}",
    ];
    $this->is_preview = $args['is_preview'] ?? false;

    $this->register_assets();

    // Prep the data.
    if( $args['block'] ?? false ) {
      $args['fields'] = \Shiny\Editor\get_block_fields( $args['block'] );
    }
    if( $args['fields'] ?? false ) {

      foreach( $args['fields'] as $property => $value ) {
        $this->{$property} = $value;
      }
    }

  }


  /**
   * Register assets.
   *
   * @access public
   *
   */
  public function register_assets() {


    if( file_exists( $this->style['path'] ) && ! wp_style_is( $this->style['filename'], 'done' ) ) {
      wp_register_style( $this->style['filename'], $this->style['url'], [], filemtime( $this->style['path'])  );
    }
    if( file_exists( $this->script['path'] ) && ! wp_script_is( $this->script['filename'], 'done' ) ) {
      wp_register_script( $this->script['filename'], $this->script['url'], [], filemtime( $this->script['path'] ) );
    }

  }

  /**
   * Print assets.
   *
   * @access public
   */
  public function print_assets() {

    if( wp_style_is( $this->style['filename'], 'registered' ) ) {
      wp_print_styles( $this->style['filename'] );
    }
    if( wp_style_is( $this->style['filename'], 'registered' ) ) {
      wp_print_styles( $this->style['filename'] );
    }

  }

  /**
   * Print styles.
   *
   * @access public
   */
  public function print_styles() {


    if( empty( $this->style ) ) {
      return;
    }

    global $printed_styles;

    if( ! $printed_styles ) {
      $printed_styles = [];
    }

      
    if( in_array( $this->style['filename'], $printed_styles ) || ! file_exists( $this->style['path'] ) ) {
      return;
    }

    if( ( ! $this->is_preview && ! \Shiny\Editor\in_editor() ) && ( \Shiny\Settings\in_devmode() && wp_style_is( $this->style['filename'], 'registered' ) ) ) {
      return wp_print_styles( $this->style['filename'] );
    }

    $styles = file_get_contents( $this->style['path'] );
    ?>
      <style>
        <?php echo $styles; ?>
      </style>
    <?php

 
    

  }

  /**
   * Print scripts.
   *
   * @access  public
   * @param   bool    $footer
   */
  public function print_scripts( $footer = true ) {

    if( ! $footer || \Shiny\Editor\in_editor() ) {
      echo '<script>' . file_get_contents( $this->script['path'] ) . '</script>';
      return;
    }

    if( wp_script_is( $this->script['filename'], 'registered' ) ) {

      add_action( 'wp_footer', function() {

        wp_print_scripts( $this->script['filename'] );

      } );

    }

  }

}
?>