<?php
namespace Shiny\Components;

/**
 * Avatar.
 */
class Avatar extends \Shiny\Component {

  /**
   * Image.
   *
   * @access public
   * @var array
   */
  public $image;

  /**
   * Size.
   *
   * @access public
   * @var string
   */
  public $size;


  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    parent::__construct( 'Avatar', __DIR__, $args  );

    if( !! $args ) {
      foreach( $args as $key => $value ) {
        $this->$key = $value;
      }
    }

    // Defaults.
    $this->size = $this->size ?? 'md';

  }

  /**
   * Render.
   */
  public function render( $classes = null ) {

    $this->print_styles();

    $classname = "Avatar rel rounded-circle oflow-hidden is-{$this->size} $classes";
    ?>

    <div class="<?php echo $classname; ?>">
      <?php
      $image = new \Shiny\Components\Image( $this->image );
      $image->render( 'abs-centered' );
      ?>
    </div>


  <?php
  }

}