<?php
namespace Shiny\Components;

/**
 * Lazy loading responsive image.
 */
class Image extends \Shiny\Component {

  /**
   * Alt text.
   *
   * @access protected
   * @var string
   */
  protected $alt;

  /**
   * Array of additional attribute values for img element.
   *
   * @access protected
   * @var array
   */
  protected $attrs;

  /**
   * Property for storing the src to be lazy loaded.
   *
   * @access protected
   * @var string
   */
  protected $data_src;

  /**
   * Lazy option.
   *
   * @access protected
   * @var bool
   */
  protected $lazy;

  /**
   * Placeholder.
   *
   * @access protected
   * @var string
   */
  protected $placeholder;

  /**
   * Src attribute value.
   *
   * @access protected
   * @var string
   */
  protected $src;

  /**
   * Style.
   *
   * @access protected
   * @var string
   */
  protected $style;

  /**
   * Url.
   *
   * @access protected
   * @var string
   */
  protected $url;


  /**
   * All other standard image props
   *
   * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img#attr-sizes
   */

  /**
   * Construct.
   *
   * @access  public
   * @param   array|int   $args                   Array of args or the image ID.
   * @param   string      $arg['url']
   * @param   string      $args['placeholder']
   * @param   bool        $args['preloadhint']
   * @param   bool        $args['lazy'].          Default true.
   * @param   bool        $lazy
   * @see the rest of the standard img parameters https://developer.mozilla.org/en-US/docs/Web/HTML/Element/img
   */
  public function __construct( $args, $lazy = true ) {

    if( ! $args ) {
      return;
    }

    if( 
      ! is_array( $args ) || 
      ( isset( $args['id'] ) && ! isset( $args['sizes'] ) ) 
    ) {
      if( ! is_array( $args ) ) {
        $args = \Shiny\Extras\wp_image( $args );
      }
      else {
        $args = array_merge( $args, \Shiny\Extras\wp_image( $args['id'] ) );
      }
      
    }

    parent::__construct( 'Image', __DIR__, $args  );

    $this->lazy = $lazy;
    if( is_array( $args ) && array_key_exists( 'lazy', $args ) && ! $args['lazy'] ) {
      $this->lazy = false;
    }
    $this->url = $args['url'] ?? $args['src'];
    $this->placeholder = $args['placeholder'] ?? false;
    $this->alt = $args['alt'] ?? null;

    if( empty( $this->url ) ) {
      return;
    }

    $svg = str_contains( $this->url, '.svg' );

    // Convert acf sizes to responsive sizes.
    if( ! $svg && isset( $args['sizes'] ) && is_array( $args['sizes'] ) ) {

      $keys = array_keys( $args['sizes'] );
      $args['srcset'] = '';
      $i = -1;

      foreach( $args['sizes'] as $key => $value ) {
        $i++;

        if(
          $key === 'thumbnail' ||
          $key === 'medium' ||
          $key === 'large' ||
          stripos( $key, 'width' ) !== false ||
          stripos( $key, 'height' ) ) {
          continue;
        }

        $width = $args['sizes'][$keys[$i+1]];
        $args['srcset'] .= "$value {$width}w, ";

      }

      // Might need to futureproof this.
      $largest_width = count( $args['sizes'] );
      end( $args['sizes'] );
      $max_width = prev( $args['sizes'] );
      //reset( $args['sizes'] )

      $args['sizes'] = "(max-width: {$max_width}px) 100vw, {$max_width}px )";

    }

    if( $this->lazy ) {
      $args['data-srcset'] = $args['srcset'];
      unset( $args['srcset'] );
    }

    $this->attrs = \Shiny\Extras\html_attrs( $args,  [
      'include' => [
        'crossorigin',
        'decoding',
        'intrinsicsize',
        'ismap',
        'loading',
        'referrerpolicy',
        'sizes',
        'style',
        'srcset',
        'data-srcset',
        'width',
        'usemap',
       ]
    ] );

    $this->data_src = ! empty( $this->lazy ) ? $this->url : null;
    $this->src = ! empty( $this->lazy ) ? $this->placeholder : $this->url;

  }

  /**
   * Render.
   *
   * @param {string}  $classes
   */
  public function render( $classes = null ) {

    //$this->print_styles();

    $classname = 'Img';
    if( $this->lazy ?? false ) {
      $classname .= ' lazy';
    }
    if( $classes ) {
      $classname .= " $classes";
    }
    ?>
    <!--<picture>-->
      <img
      class="<?php echo $classname; ?>"
      <?php if( $this->data_src ?? false ) : ?>
        data-src="<?php echo $this->data_src; ?>"
      <?php endif; ?>
      src="<?php echo $this->src ?? ''; ?>"
      alt="<?php echo $this->alt ?? ''; ?>"
      <?php if( $this->attrs ?? false ) { echo $this->attrs; } ?>
      />
    <!--</picture>-->

    <?php
    //$this->print_scripts();

  }

}