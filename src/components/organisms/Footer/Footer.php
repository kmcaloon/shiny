<?php
namespace Shiny\Components;

/**
 * Footer.
 */
class Footer extends \Shiny\Component {

  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    $args['fields'] = get_field( 'footer', 'options' )[0];

    parent::__construct( 'Footer', __DIR__, $args );

  }

  /**
   * Render.
   */
  public function render() {

    $this->print_styles();

    ?>

    <?php

  }

}