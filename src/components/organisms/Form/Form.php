<?php
namespace Shiny\Components;

/**
 * Form
 */
class Form extends \Shiny\Component {

  /**
   * Args consist of Gravity Form Object or id as well as other custom properties.
   */

   /**
    * Gform id.
    *
    * @var string|int
    */
  public $gform_id;


  /**
   * Default values.
   * 
   * @var array
   */
  public $default_values;

  /**
   * Unique id.
   *
   * @access public
   * @var string
   */
  public $unique_id;

  /**
   * Custom lead source.
   * 
   * @var string
   */
  public $ls;

  /**
   * Custom lead source specifics.
   * 
   * @var string
   */
  public $lss;

  /**
   * Dark inputs.
   * 
   * @var bool
   */
  public $dark_inputs;

  /**
   * Construct.
   *
   * @access public
   */
  public function __construct( $args = [] ) {

    parent::__construct( 'Form', __DIR__, $args  );

    $this->unique_id = \Shiny\Extras\generate_id();
    // $args is the form id.
    if( ! is_array( $args ) ) {
      $args = \GFAPI::get_form( $args );
    }
    else if( ! empty( $args['gform_id'] ) ) {
      $args = array_merge( \GFAPI::get_form( $args['gform_id'] ), $args );
    }

    if( $args ?? null ) {
      foreach( $args as $key => $value ) {
        $this->$key = $value;
      }
    }
  
    $default_values = _checkshow( $args, 'default_values' );


    $this->ls = _checkshow( $this, 'ls' ) ?: _checkshow( $default_values, 'ls' ) ?: _checkshow( $default_values, 'Lead Source' );
    $this->lss = _checkshow( $this, 'lss' ) ?: _checkshow( $default_values, 'lss' ) ?: _checkshow( $default_values, 'Lead Source' );
    if( $this->ls ) {
      $default_values['ls'] = $this->ls;
    }
    if( $this->lss ) {
      $default_values['lss'] = $this->lss;
    }

    if( ! empty( $default_values ) ) {


      $i = 0;

      foreach( $this->fields as $field ) {
      
        if( array_key_exists( $field->label, $default_values ) ) {
          $this->fields[$i]->defaultValue = $default_values[$field->label];
        }
        else {
          $slug = strtolower( $field->label ); 
          $is_ls = str_contains( $slug, 'source' ) && ! str_contains( $slug, 'specific' );
          $is_lss = str_contains( $slug, 'source' ) && str_contains( $slug, 'specific' );
          
          if( $is_ls && ! empty( $this->ls ) ) {
            $field['defaultValue'] = $this->ls;
          }
          else if( $is_lss && ! empty( $this->lss ) ) {
            $field['defaultValue'] = $this->lss;
          }

        }

        $i++;

      }
    }


  }

  /**
   * Render.
   */
  public function render() {

    global $is_preview;
    $classname = 'Form text-left';

    $this->print_styles();


    if( ! empty( $this->dark_inputs ) ) {
      $classname .= ' has-dark-inputs';
    }

    if( $is_preview ) {
      ?>
        <h2 class="hd-lg text-center">You must preview forms on the frontend</h2>
      <?php

      return;
    }

    $prepopulate_data = [];
    $start_prepopulate = false;
    foreach( (array)$this as $key => $value ) {
      if( $key == 'is_trash' ) {
        $start_prepopulate = true;
        continue;
      }

      if( $start_prepopulate && $key == 'email' ) {
        $prepopulate_data[$key] = $value;
      }
    }
    
    ?>

    <?php if( $this->gform_id == '293' ) : ?>
      <script>
        var formInfo = <?php echo json_encode( $this ); ?>;
      </script>
    <?php endif; ?>

    <form 
    id="<?php echo $this->unique_id; ?>"
    class="<?php echo $classname; ?> rel"
    data-form="<?php echo $this->id; ?>"
    data-type="<?php echo $this->trackingaddon['ga_type'] ?? 'leadgen'; ?>"
    data-title="<?php echo $this->title; ?>"
    method="post"
    actype="multipart/form-data"
    action="/#<?php echo $this->unique_id; ?>"
    <?php if( $prepopulate_data ) : ?>
      data-prepopulate='<?php echo json_encode( $prepopulate_data ); ?>''
    <?PHP endif; ?>
    onsubmit="window.submitForm(event)"
    >

      <div class="container-md px-0">

        <div class="row">

          <?php if( $this->fields ?? false ) : $i = 0; foreach( $this->fields as $field ) :

            // if( $field['visibility'] == 'administrative' ) {
            //   continue;
            // }

            $field_id = "{$this->unique_id}-{$field['id']}";
            
            $type = $field['type'];
            if( $field['type'] === 'phone' ) {
              $type = 'tel';
            }

            $field_classname = "Form__item";
            if( $field['isRequired'] ) {
              $field_classname .= ' is-required';
            }
            if( $field['visibility'] !== 'administrative' && $field['type'] !== 'hidden' && $field['visibility'] !== 'hidden' ) {
              $field_classname .= ' mb-4';
            }
            else {
              $field_classname .= ' hidden';
            }

            if( $field['cssClass'] ?? false ) {

              if( str_contains( $field['cssClass'], 'half' )|| str_contains( $field['cssClass'], 'medium-6' ) ) {
                $field_classname .= ' md:col-6';
              }
            }

            if( $field['allowsPrepopulate'] && $field['inputName'] ) {
              $parameter = $fields['inputName'];
            }
          ?>

            <div class="<?php echo $field_classname; ?>">

              <?php if( $type == 'checkbox' ) : ?>

                <label 
                class="Form__label form-label"
                >
                  <?php echo $field['label']; ?>
                </label>
                
                <?php $i = 1; foreach( $field['choices'] as $choice ) : 
                  $choice_id = "choice_{$this->unique_id}_{$field['id']}_$i";
                  $input_id = "{$field['id']}.$i";
                  ?>

                  <input 
                  id="<?php echo $choice_id; ?>"
                  name="input_<?php echo $input_id; ?>"
                  class="form-check-input"
                  type="checkbox"
                  value="<?php echo $choice['value']; ?>"
                  checked="<?php echo _checkshow( $choice, 'isSelected' ); ?>"
                  <?php if( $field['allowsPrepopulate'] ?? false ) : ?>
                    data-prepopulated="1"
                  <?php endif; ?>
                  <?php if( $field['inputName'] ) : ?>
                    data-param="<?php echo $field['inputName']; ?>"
                  <?php endif; ?>
                  />

                  <label 
                  for="<?php echo $choice_id; ?>"
                  class="Form__label inline-block ml-1 f-accent f-05"
                  >
                    <?php echo $choice['text']; ?>
                  </label>

                <?php $i++; endforeach; endif; ?>
              

                <?php if( $field['type'] !== 'hidden' && $field['type'] !== 'checkbox' && $field['label'] && $field['labelPlacement'] !== 'hidden_label' ?? false ) : ?>
                  <label 
                  for="<?php echo $field_id; ?>"
                  class="Form__label form-label <?php if( $type == 'checkbox' ) { echo 'd-inline-block ml-1'; } ?>"
                  >
                    <?php echo $field['label']; ?>
                  </label>
                <?php endif; ?>

                <?php if( $field['type'] === 'text' || $field['type'] == 'email' || $field['type'] == 'phone'  || $field['type'] == 'hidden' ) : ?>
                  
                      <input 
                      id="<?php echo $field_id; ?>"
                      name="input_<?php echo $field['id']; ?>"
                      <?php if( $field['defaultValue'] ?? false ) : ?>
                        value="<?php echo $field['defaultValue']; ?>"
                      <?php endif; ?>
                      type="<?php echo $type; ?>"
                      class="form-control"
                      <?php if( $field['placeholder'] ?? false ) : ?>
                        placeholder="<?php echo $field['placeholder']; ?>"
                      <?php endif; ?>
                      <?php if( $field['isRequired'] ?? false ) : ?>
                        required="true"
                      <?php endif; ?>
                      <?php if( $field['type'] == 'phone' ) : ?>
                        pattern="[(][0-9]{3}[)] [0-9]{3}-[0-9]{4}"
                      <?php endif; ?>
                      <?php if( $field['allowsPrepopulate'] ?? false ) : ?>
                        data-prepopulated="1"
                      <?php endif; ?>
                      <?php if( $field['inputName'] ) : ?>
                        data-param="<?php echo $field['inputName']; ?>"
                      <?php endif; ?>
                      />

                <?php elseif( $type === 'select' ) : 
                  
                  // Ughhhhhhggdly.
                  // We need to automatically fill cities on the fly.
                  if( ! empty( $field['choices'][0]['text'] ) ) {

                    if( $field['choices'][0]['text'] == 'AZ - Phoenix' || $field['choices'][1]['text'] == 'AZ - Phoenix' ) {

                      $sf_choices = \Shiny\Salesforce\get_cache( 'sf_lead_fields' );
                      $field['choices'] = [];
                      $locations = get_terms( 'location', [
                        'hide_empty'  => false
                      ] );
                      $location_options = [];
                      if( $locations ) {
                        foreach( $locations as $term ) {

                          $location_options[] = [
                            'text' => $term->name,
                            'value' => $term->name,
                          ];

                        }
                      }

                      if( ! empty( $location_options ) ) {
                        $field['choices'] = $location_options;
                      }

                    }

  
                    
                  }

                  
                ?>

                  <select
                  id="<?php echo $field_id; ?>"
                  name="input_<?php echo $field['id']; ?>"
                  class="form-select"
                  aria-label="<?php echo $field['label']; ?>"
                  <?php if( $field['isRequired'] ) { echo 'required'; } ?>
                  >

                    <?php if( $field['placeholder'] ) : ?>
                      <option value selected><?php echo $field['placeholder']; ?></option>
                    <?php endif; ?>

                    <?php if( $field['defaultValue'] ?? false ) : ?>
                      <option value="<?php echo $field['defaultValue']; ?>" selected><?php echo $field['defaultValue']; ?></option>
                    <?php endif; ?>
                    
                    <?php foreach( $field['choices'] as $choice ) : ?>
                      <option 
                      value="<?php echo $choice['value']; ?>"
                      <?php if( _check( $choice, 'isSelected' ) ) { echo 'selected'; } ?>
                      >
                        <?php echo $choice['text']; ?>
                      </option>
                    <?php endforeach; ?>

                  </select>

                <?php elseif( $type == 'textarea' ) : ?>

                  <textarea
                  id="<?php echo $field_id; ?>"
                  name="input_<?php echo $field['id']; ?>"
                  class="form-control"
                  rows="8"
                  aria-label="<?php echo $field['label']; ?>"
                  <?php if( $field['isRequired'] ) { echo 'required'; } ?>
                  <?php if( $field['allowsPrepopulate'] ?? false ) : ?>
                    data-prepopulated="1"
                  <?php endif; ?>
                  ></textarea>

                <?php elseif( $type == 'hidden' ) : ?>

                  <input 
                  id="<?php echo $field_id; ?>"
                  name="input_<?php echo $field['id']; ?>"
                  type="<?php echo $type; ?>"
                  class="form-control"
                  <?php if( $field['isRequired'] ) { echo 'required'; } ?>
                  <?php if( $field['allowsPrepopulate'] ?? false ) : ?>
                    data-prepopulated="1"
                  <?php endif; ?>
                  <?php if( $field['inputName'] ) : ?>
                    data-param="<?php echo $field['inputName']; ?>"
                  <?php endif; ?>
                  />


                <?php elseif( $type == 'fileupload' ) : ?>

                  <input 
                  id="<?php echo $field_id; ?>"
                  name="input_<?php echo $field['id']; ?>"
                  type="file"
                  class="form-control"
                  accept="<?php echo $field['allowedExtensions']; ?>"
                  <?php if( $field['isRequired'] ) { echo 'required'; } ?>
                  />
                  
                <?php endif; ?>

                

                
              
            
            </div>

          <?php $i++; endforeach; endif; ?>
        
        </div>

        <div class="Form__bottom">

          <?php
          $submit = new \Shiny\Components\Button( [
            'color'     => 'green',
            'type'      => 'submit',
            'text'      => $this->button['text'],
            'fullwidth' => true,
            'size'      => 'lg',
          ] );
          $submit->render();
          ?>

        </div>

      </div>
    </form>


  <?php
   //debug( $this );
  }

}