<?php
namespace Shiny\API;

use \WP_REST_Server as WP_REST_Server;
use GuzzleHttp\Promise;


/**.
* Shiny API.
*/
class Shiny_API extends \WP_REST_Controller {


  /**
  * Register the routes for the objects of the controller.
  */
  public function register_routes() {


    /* ------ = Forms = --------------------------------------------------------------------- */

    register_rest_route( REST_NAMESPACE, '/form/(?P<id>[a-zA-Z0-9-]+)', [

      /**
       *  Submit form.
       */
      [
        'methods'           => \WP_REST_Server::EDITABLE,
        'show_in_index'     => true,
        'callback'          => function( $request ) {

          $form_id = $request['id'];
          $form = \GFAPI::get_form( $form_id );
          $data = json_decode( $request->get_body(), 'ARRAY' );

          // Prep entry data.
          $entry = [
            'form_id'       => $form_id,
            'date_created'  => date( 'Y-m-d G:i' ),
            'source_url'    => $data['source_url'],
            'user_agent'    => '',
          ];

          foreach( $data as $key => $value  ) {

            if( ! str_contains( $key, 'input' ) ) {
              continue;
            }

            // Process uploads.
            if( str_contains( $value, 'data:' ) ) {
              
              \GFCommon::log_debug( 'gform_pre_process: found string' );
		          $target_dir = \GFFormsModel::get_upload_path( $form_id ) . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

              if( ! is_dir( $target_dir ) ) {

                \GFCommon::log_debug( 'gform_pre_process: creating tmp folder' );

                if( ! wp_mkdir_p( $target_dir ) ) {
                  \GFCommon::log_debug( "gform_pre_process: Couldn't create the tmp folder: " . $target_dir );
                } else {
                  \GFCommon::recursive_add_index_file( $target_dir );
                }

              }

              $field_id = str_replace( 'input_', '', $key );
              $file_extension = explode( '/', mime_content_type( $value ) )[1];
              $file_contents = base64_decode( preg_replace( '#^data:image/\w+;base64,#i', '', $value ) );
		          $temp_filename = sprintf( '%s_input_%s.%s', \GFFormsModel::get_form_unique_id( $form_id ), $field_id, $file_extension );

              $file_id = \Shiny\Extras\generate_id();
              $filename = "$file_id.$file_extension";

              $filepath = $target_dir . $filename;

              $result = file_put_contents( $filepath, $file_contents );

              // Need to come up with something better.
              $path_strings = explode( '/wp-content', $filepath );
              $fileurl = get_home_url() . "/wp-content{$path_strings[1]}";

              
              \GFCommon::log_debug( 'gform_pre_process: file_put_contents result: ' . var_export( $result, true ) );


              $value = $fileurl;


            }

            $index = str_replace( 'input_', '', $key );
            $entry[$index] = $value;

          }


          // Create entry.
          $entry_id = \GFAPI::add_entry( $entry );

          // If all goes well, send notifications.
          if( ! is_wp_error( $entry_id ) ) {
            $entry = \RGFormsModel::get_lead( $entry_id );
            $sent = \GFAPI::send_notifications( $form, $entry, 'form_submission' );
            $response = '';

            if( $form['confirmations'] ?? false ) {

              if( is_array( $form['confirmations'] ) && count( $form['confirmations'] ) > 1 ) {
                $response = array_values( $form['confirmations'] )[1];
                $default = array_values( $form['confirmations'] )[0];

                include( ABSPATH . 'wp-content/plugins/gravityforms/form_display.php' );

                foreach ( $form['confirmations'] as $confirmation ) {

                  if ( rgar( $confirmation, 'event' ) != $event ) {
                    continue;
                  }
            
                  if ( rgar( $confirmation, 'isDefault' ) ) {
                    continue;
                  }
            
                  if ( isset( $confirmation['isActive'] ) && ! $confirmation['isActive'] ) {
                    continue;
                  }
            
                  $logic = rgar( $confirmation, 'conditionalLogic' );
                  if ( \GFCommon::evaluate_conditional_logic( $logic, $form, $entry ) ) {
                    $response = $confirmation;
                  }
                }
            
              }
              else {
                $response = array_values( $form['confirmations'] )[0];
              }
              
            }

            if( $response['message'] ) {
              $response['message'] = apply_filters( 'the_content', $response['message'] );
            }
          
            return new \WP_REST_Response( $response );
          }          

        }

      ],

    ] );


    /* ------ = Admin syncing = --------------------------------------------------------------------- */

    register_rest_route( REST_NAMESPACE, '/admin', [

      /**
       * Sync admin menu.
       */
      [
        'methods'             => \WP_REST_Server::EDITABLE,
        'show_in_index'       => false,
        'permission_callback' => function() {
          return is_user_logged_in();
        },
        'callback'            => function( $request ) {

          // Admin menu editor.
          $menusettings = get_stylesheet_directory() . '/admin-menu.json';

          if( file_exists( $menusettings ) ) {

            $json = file_get_contents( $menusettings );
     
            update_option( 'ws_menu_editor', $json );

          }

          return new \WP_REST_Response( 'Admin menu synced!' );

        }

      ],

    ] );

  }
}