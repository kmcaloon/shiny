<?php
namespace Shiny\Components;

/**
 * Popup
 */
class Popup extends \Shiny\Component {
  

  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    if( ! isset( $args['fields'] ) ) {
      $args['fields'] = get_fields();
    }

    parent::__construct( 'Popup', __DIR__, $args  );

    if( empty( $this->cta_type ) ) {
      $this->cta_type == 'form';
    }


    // Defaults.
    if( \Shiny\Editor\in_editor() ) {
      $this->image = $this->image ?: [
        'src' => \Shiny\Editor\img_placeholder()
      ];
      $this->text = $this->text ?: 'Enter some text here';
    }

  }

  /**
   * Render.
   */
  public function render() {


    //$this->print_styles();

    //debug( $this );

    if( $this->image_ratio ) {
      $ratio = $this->image_ratio ? $this->image_ratio['height'] / $this->image_ratio['width'] : 0.5;
    }

    $image_padding = $ratio * 100;
    ?>

    <?php if( \Shiny\Editor\in_editor() ) : ?>

      <div 
      class="Popup__content bg-white text-center <?php echo $this->cta_type == 'button' ? 'has-button' : ''; ?> "
      aria-modal="true"
      role="dialog"
      >

        <div class="Popup__img rel oflow-hidden"
        style="padding-top: <?php echo $image_padding; ?>%"
        >
          <?php
          $image = new \Shiny\Components\Image( $this->image );
          $image->render( 'ovlay' );
          ?>
        </div>

        <div class="p-8">

          <?php if( $this->cta_type == 'form' ) : ?>
            <h3 class="hd-sm mb-2">
              <?php echo $this->text; ?>
            </h3>
          <?php endif; ?>

          <?php if( $this->cta_type == 'form' ) : ?>
            <?php if( $this->form ?? null ) { 
              $form = new \Shiny\Components\Form( $this->form );
              $form->render();
            }
            ?>
          <?php endif; ?>

          <?php 
          if( $this->cta_type == 'button' ) {

            $cta = new \Shiny\Components\Button( [
              'cta'       => $this->cta,
              'fullwidth' => true,
              'size'      => 'lg'
            ] );
            $cta->render();

          }
          ?>
        

          <?php if( $this->footnote ?? false ) : ?>
            <div class="subhd italic mt-4"><?php echo $this->footnote; ?></div>
          <?php endif; ?>
        </div>

      </div>


    <?php else : ?>

      <div
      class="Popup ovlay fixed <?php echo $this->cta_type == 'button' ? 'has-button' : ''; ?>"
      data-popup
      >

          <div 
          class="Popup__content bg-white text-center horz-centered rounded rel"
          aria-modal="true"
          role="dialog"
          >


            <button
            class="Popup__close abs white"
            data-close
            >
              <span class="srt">Close</span>
              <span
              aria-hidden="true"
              >
                <?php echo \Shiny\Assets\icon( 'close' ); ?>
              </span>
            </button>

            <div class="Popup__img rel oflow-hidden"
            style="padding-top: <?php echo $image_padding; ?>%;"
            >
              <?php
              $image = new \Shiny\Components\Image( $this->image );
              $image->render( 'ovlay' );
              ?>
            </div>

            <div class="p-8">
              <?php if( $this->cta_type == 'form' ) : ?>
                <h3 class="hd-sm mb-2">
                  <?php echo $this->text; ?>
                </h3>
              <?php endif; ?>
              <?php if( $this->form ?? null ) { 
                $form = new \Shiny\Components\Form( $this->form );
                $form->render();
              }
              ?>

              <?php 
              if( $this->cta_type == 'button' ) {

                $cta = new \Shiny\Components\Button( [
                  'cta'       => $this->cta,
                  'fullwidth' => true,
                  'size'      => 'lg'
                ] );
                $cta->render();

              }
              ?>
        
              
              <?php if( $this->footnote ?? false ) : ?>
                <div class="subhd italic mt-4"><?php echo $this->footnote; ?></div>
              <?php endif; ?>
            </div>

          </div>

      </div>

    <?php endif; ?>

  <?php
  }

}