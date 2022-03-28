<?php
namespace Shiny\Components;

/**
 * Modal
 */
class Modal extends \Shiny\Component {

  /**
   * Id.
   *
   * @access public
   * @var string
   */
  public $id;

  /**
   * Video.
   * 
   * @var bool
   */
  public $video;


  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    parent::__construct( 'Modal', __DIR__, $args  );

    $this->id = substr( str_shuffle( 'abcdefghijklmnopqrstuvwxyz' ), 0, 5 );

    if( $args ?? null ) {
      foreach( $args as $key => $value ) {
        $this->$key = $value;
      }
    }

  }

  /**
   * Render.
   */
  public function render( $children = null ) {

    $this->print_styles();
    ?>

    <div 
    class="Modal fixed top-0 left-0 w-100 h-100"
    data-ref="<?php echo $this->id; ?>"
    >

      <div class="Modal__ovlay ovlay bg-darkblue">
      </div>

      <div 
      class="Modal__body horz-centered mt-16 <?php echo empty( $this->video ) ? 'p-12 card' : ''; ?>"
      aria-modal="true"
      role="dialog"
      >
        <div class="rel">
          <button
          class="Modal__close abs white"
          data-shows="<?php echo $this->id; ?>"
          >
            <span class="srt">Close</span>
            <span
            aria-hidden="true"
            >
              <?php echo \Shiny\Assets\icon( 'close' ); ?>
            </span>
          </button>

          <?php 
          if( $children ) {
            $children();
          }
          ?>

        </div>
      </div>


    </div>


  <?php
  }

}