<?php
namespace Shiny\Components;

\Shiny\Extras\include_from_current_and_subs( __DIR__ );

/**
 * Lazy component.
 */
function lazy_component( $component ) {

  ob_start();
  $component->render();
  $html = ob_get_contents();
  ob_end_clean();
  ?>

  <script>
    var lazyHTML = `<?php echo $html; ?>`;
  </script>

  <div id="lazyc">
  </div>

  <script>
    document.getElementById( 'lazyc' ).setAttribute( 'data-html', lazyHTML );
    var observer = new IntersectionObserver( lazyloadComponent, {
      rootMargin: '200px',
      threshold: 1.0,
    } );
    var el = document.getElementById( 'lazyc' );
    observer.observe(el);
  </script>

  <?php



}