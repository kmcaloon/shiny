<?php
namespace Shiny\Components;

/**
 * Video BG.
 */
class VideoBG extends \Shiny\Component {


  /**
   * Construct.
   *
   * @access  public
   * @param   string  $url
   * @param   string  $placeholder
   */
  public function __construct( $args = [] ) {

    if( ! $args['fields'] ) {
      $args['fields'] = get_fields();
    }

    parent::__construct( 'VideoBG', __DIR__, $args  );

    // $this->classes = [
    //   'LndHero',
    //   "is-{$this->text_shade}-txt"
    // ];
    // if( $this->hide_mobile_bg ) {
    //   $this->classes[] = 'is-hiding-mob-bg';
    // }

    // Defaults.
    if( \Shiny\Editor\in_editor() ) {

      if( ! ( $this->img ?? null ) || ! ( $this->img['img'] ?? null )  ) {
        $this->img = [
          'img'         => [
            'url' => \Shiny\Editor\img_placeholder()
          ],
          'fullheight'  => true,
        ];
      }

      $this->hline = $this->hline ?? 'Put a small-sized headline here';
      $this->text = $this->text ?? '<p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum.</p><p>No sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore.</p>';

    }

  }

  /**
   * Render.
   */
  public function render() {

    $this->print_styles();


    ?>

      <div class="VidBG text-center">

        <div class="VidBG__cont rel oflow-hidden">
          <video
          class="lazy"
          src="<?php echo $this->video['url']; ?>"
          autoplay="true"
          muted="true"
          loop="true"
          role="presentation"
          ></video>

          <?php if( $this->overlay ?? false ) : ?>
            <div class="ovlay __ovlay"
            style="
            background-color: <?php echo $this->overlay; ?>;
            opacity: <?php echo $this->overlay_opacity / 100 ?? '1'; ?>;
            "
            >
            </div>
          <?php endif; ?>

          <div class="abs-centered">
            <h2 class="hd-lg italic"><?php echo $this->headline; ?></h2>
          </div>

        </div>

      </div>
    <?php


  }
}