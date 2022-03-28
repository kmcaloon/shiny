<?php
/**
 * The main wrapper for the site
 */
?>

<!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>

  <head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">

    <!--<link rel="preconnect" href="https://use.typekit.net/" crossOrigin="anonymous" />
    <link rel="preconnect" href="https://p.typekit.net/" crossOrigin="anonymous" />
    
    <link rel="preload" href="https://use.typekit.net/id.css" as="style" />
    <link rel="stylesheet" href="https://use.typekit.net/id.css" />-->

    <!-- Favicon -->


    <?php \Shiny\Assets\load_fonts(); ?>

    <?php wp_head(); ?>

    <?php if( ! \Shiny\Settings\in_devmode() && ! is_user_logged_in()  ) : ?>

       <!-- Analytics -->

    <?php endif; ?>

  </head>

  <?php echo \Shiny\Assets\print_page_scripts( 'head-scripts' ); ?>

  <body <?php body_class(); ?>>
    <div class="rel"
    style="overflow-x: hidden;"
    >

      <!--[if IE]>
      <div class="alert alert-warning alert-browser">
        <?php
        _e( 'You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.', 'shiny' );
        ?>
      </div>
      <![endif]-->

      <a
      class="skip-link srt"
      href="#content"
      >
        <?php esc_html_e( 'Skip to content', 'text' ); ?>
      </a>

      <?php // Render header.
      global $header_component;

      $header = $header_component ?? new Shiny\Components\Header();

      if( is_object( $header ) ) {
        $header->render();
      }
      ?>

      <main id="content" class="page" role="main">

        <?php // Render template.
        include Shiny\Wrapper\template_path();
        ?>

      </main>

      <?php // Render footer.
      global $footer_component;
      $footer = $footer_component ?? new Shiny\Components\Footer();
      if( is_object( $footer ) ) {
        $footer->render();
      }
      wp_footer();

      ?>

      <?php 
      // For browsersync during development.
      if( str_contains( $_SERVER['HTTP_HOST'], '3000' ) ) : ?>

        <script id="__bs_script__">//<![CDATA[
          document.write("<script async src='http://HOST:3000/browser-sync/browser-sync-client.js?v=2.26.7'><\/script>".replace("HOST", location.hostname));
      //]]>
        </script>

      <?php endif; ?>

    </div>      
  </body>
</html>