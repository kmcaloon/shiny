<?php
namespace Shiny\Forms;
use GuzzleHttp\Promise;

class TrackingAddOn extends \GFAddOn {

  protected $_version = '1.0';
  protected $_min_gravityforms_version = '1.0';
  protected $_slug = 'trackingaddon';
  protected $_full_path = __FILE__;
  protected $_title = 'Tracking';
  protected $_short_title = 'Tracking';

  /**
   * @var object|null $_instance If available, contains an instance of this class.
   */
  private static $_instance = null;


  /**
   * Returns an instance of this class, and stores it in the $_instance property.
   *
   * @return object $_instance An instance of this class.
   */
  public static function get_instance() {
    if( self::$_instance == null ) {
      self::$_instance = new self();
    }

    return self::$_instance;

  }

  /**
   * Init.
   */
  public function init() {
    parent::init();

    add_action( 'gform_post_add_entry', [ $this, 'process_submission' ], 10, 2 );

  }

  /**
   * Process the submission.
   * 
   * @param   array   $entry
   * @param   array   $form
   */
  public function process_submission( $entry, $form ) {


    $promises = [];

    if( ! $promises ) {
      return true;
    }

    // Run the promises.
    $promise_results = Promise\Utils::settle( $promises )->wait();

  }

  /**
   * Configures the settings which should be rendered on the Form Settings -> Tracking tab.
   *
   * @param array $form
   * @return array
   */
  public function form_settings_fields( $form ) {


  }


}