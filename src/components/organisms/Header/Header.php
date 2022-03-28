<?php
namespace Shiny\Components;

/**
 * Header
 */
class Header extends \Shiny\Component {

  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    $args['fields'] = get_field( 'header', 'options' )[0];

    parent::__construct( 'Header', __DIR__, $args  );

  }

  /**
   * Render.
   */
  public function render() {
  ?>


  <?php
  }

}