<?php
namespace Shiny\Components;

/**
 * Button.
 */
class Button extends \Shiny\Component {

  /**
   * Button Id.
   *
   * @access public
   * @var string
   */
  public $id;

  /**
   * Href.
   *
   * @access public
   * @var string
   */
  public $href;

  /**
   * Text.
   *
   * @access public
   * @var string
   */
  public $text;

  /**
   * Target.
   *
   * @access public
   * @var string
   */
  public $target;

  /**
   * Size.
   *
   * @access public
   * @var string
   */
  public $size;


  /**
   * Type.
   *
   * @access public
   * @var string
   */
  public $type;

  /**
   * CTA data from ACF.
   *
   * @access public
   * @var array
   */
  public $cta;

  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    // if( ! $args['fields'] ) {
    //   $args['fields'] = get_fields();
    // }

    parent::__construct( 'Button', __DIR__, $args  );

    if( !! $args ) {
      foreach( $args as $key => $value ) {
        $this->$key = $value;
      }
    }
    if( !! $this->cta ) {
      $this->href = $this->cta['url'];
      $this->text = $this->cta['title'];
      $this->target = $this->cta['target'] ?? null;
    }

    $this->tag = !! $this->href ? 'a' : 'button';
    $this->size = $this->size ?? 'md';
    $this->color = $this->color ?? 'blue';


  }

  /**
   * Render.
   */
  public function render( $children = null ) {

    $this->print_styles();

    $classname = "Btn text-center is-{$this->size} is-{$this->color}";

    if( $this->fullwidth ?? false ) {
      $classname .= ' w-100';
    }
    if( $this->type ?? false ) {
      $classname .= " is-{$this->type}";
    }

    ?>

    <<?php echo $this->tag; ?>
    class="<?php echo $classname; ?>"
    <?php if( $this->href ?? false ) { echo "href='{$this->href}'"; } ?>
    <?php if( $this->type ?? false ) { echo "type='{$this->type}'"; } ?>
    <?php if( $this->target ?? false ) { echo "target='{$this->target}'"; } ?>
    <?php if( $this->attrs ?? false ) {
      foreach( $this->attrs as $key => $value ) {
        echo "$key='$value'";
      }
    } ?>
    >
      <?php
      if( $children ) {
        $children();
      }
      else if( $this->text ) {
        echo $this->text;
      }
      ?>
    </<?php echo $this->tag; ?>>



  <?php
  }

}