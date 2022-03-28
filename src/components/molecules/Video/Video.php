<?php
namespace Shiny\Components;

/**
 * Video
 */
class Video extends \Shiny\Component {

    /**
     * Video ID
     *
     * @access protected
     * @var string
     */
    public $id;

    /**
     * The video
     *
     * @access protected
     * @var string
     */
    public $video;

    /**
     * Modal option.
     * 
     * @var bool
     */
    public $modal = false;

    /**
     * In gallery option
     *
     * @access protected
     * @var bool
     */
    public $in_gallery = false;

    /**
     * URL
     *
     * @access protected
     * @var string
     */
    public $url;

    /**
     * Poster image component.
     *
     * @access protected
     * @var object
     */
    public $poster;


  /**
   * Video constructor
   *
   * @access protected
   * @param array   $args
   * @param string  $args['video']       HTML string for video iframe formatted by ACF
   * @param array   $args['video_id']    allows for a manual id for the video
   */
  public function __construct( $args ) {

    parent::__construct( 'Video', __DIR__, $args  );

    $this->id = \Shiny\Extras\generate_id();

    $this->poster = $args['poster'] ?? null;
    $this->modal = $args['modal'] ?? false;

    if( ! empty( $args['video'] ) ) {

      // Add lazy class to video.
      $classes = "lazy";
      $this->url = \Shiny\ACF\get_video_url( $args['video'] );
      $video_embed = substr_replace( $args['video'], "class='$classes'", stripos( $args['video'], '<iframe ' ) + 8, 0 );
      $this->video = str_replace( 'src=', 'data-src=', $video_embed );
    }
    else if( $args['url'] ?? null ) {
      $this->url = $args['url'];
      $this->video = "<iframe class='lazy' width='640' height='360' data-src='{$args['url']}' frameborder='0' allow='autoplay; accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
    }



    if( stripos( $this->url, 'youtube' ) ) {
      $this->type = 'youtube';
    }
    else {
      'vimeo';
    }
  }

  /**
   * Render.
   */
  public function render() {

    $this->print_styles();

    ?>

    <div 
    class="Vid rel oflow-hidden is-<?php echo $this->type ?? 'modal'; ?>"
    <?php if( empty( $this->modal ) ) : ?>
      data-ref="<?php echo $this->id; ?>"
    <?php endif; ?>
    >

      <?php if( empty( $this->modal ) ) : ?>

        <?php echo $this->video; ?>

        <?php if( $this->poster ?? null ) : 
          $iframe_el = "[data-ref='{$this->id}'] iframe";
          ?>
          
          <button 
          class="Vid__poster abs ovlay z-2"
          data-shows="<?php echo $this->id; ?>"
          onclick="const iframe = document.querySelector( `<?php echo $iframe_el; ?>` ); iframe.src = iframe.dataset.src + `?autoplay=1`;"
          >
            <span class="srt">Play Video</span>
            <?php $this->poster->render( 'abs ovlay z-2' ); ?>
          </button>
          
        <?php endif; ?>

      <?php else : ?>

        <button 
        class="Vid__poster ovlay z-2"
        data-shows="<?php echo $this->id; ?>"
        >
          <span class="srt">Play Video</span>
          <?php if( _check( $this, 'poster' ) ) { 
            $this->poster->render( 'abs ovlay z-2' ); 
          } ?>
        </button>

        <?php
        $modal = new \Shiny\Components\Modal( [
          'id'    => $this->id,
          'video' => true,
        ] );
        $modal->render( function() {
          $the_video = new \Shiny\Components\Video( [
            'video' => $this->fields['video']
          ] );
          $the_video->render();
        } );
        ?>

      <?php endif; ?>

    </div>

    <?php
  }


}