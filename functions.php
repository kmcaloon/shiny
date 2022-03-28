<?php

require( 'vendor/autoload.php' );


/**
 * Set up the config as constants.
 */
$config_json = file_get_contents( __DIR__ . '/config.json' );
$config = json_decode( $config_json, true );
foreach( $config as $setting => $value ) {
  define( $config['NAMESPACE'] . "\\${setting}", $value );
}


/**
 * Provide some PHP 8 functions to older versions.
 */
if( ! function_exists( 'str_contains' ) ) {

  /**
   * Check is string contains substring
   * 
   * @param string  $haystack
   * @param string  $needle
   * @return bool
   */
  function str_contains( $haystack, $needle ) {

    if( strpos( $haystack, $needle ) !== false ) {
      return true;
    }
    else {
      return false;
    }
    
  }
}
if( ! function_exists( 'str_starts_with' ) ) {

  /**
   * Check is string starts with substring.
   * 
   * @param string  $haystack
   * @param string  $needle
   * @return bool
   */
  function str_starts_with( $haystack, $needle ) {
    return strpos( $haystack, $needle ) === 0;
  }
}

/**
 * Workaround to dreaded isset() and empty() conundrums.
 * There are a ton of blog posts, stackoverlow threads, and opinions about this.
 * I often use flex editor previews within the admin as users are updating content,
 * so there may be array or object properties that are either not set or empty at
 * any given time, and it can be painstaking to check if admins have entered in values.
 * This function checks if the variable, or nested index/property has a value. 
 * It can check nested properties 3 levels deep.
 * 
 * @param   mixed                 $var    
 * @param   string|array|object   $key1   Array or object property.
 * @param   string|array|object   $key2   Array or object property.
 * @param   string                $key3   Array or object property.
 * @return  bool
 */
function _check( $var, $key1 = null, $key2 = null, $key3 = null ) {

  $result = isset( $var ) && $var;

  if( $key1 && $result ) {
    
    if( gettype( $var ) == 'object' ) {
      $var = (array)$var;
    } 
    $result = isset( $var[$key1] ) && $var[$key1];

    if ($key2 && $result ) {

      if( gettype( $key1 ) == 'object' ) {
        $key1 = (array)$key1;
      } 
      $result = isset( $var[$key1][$key2] ) && $var[$key1][$key2];

      if( $key3 && $result ) {

        if( gettype( $key2 ) == 'object' ) {
          $key2 = (array)$key2;
        } 
      
        $result = isset( $var[$key1][$key2][$key3] ) && $var[$key1][$key2][$key3];

      }
    }
  }

  return $result;

}

/**
 * Shortcut to return a value if variable isset and not empty.
 * 
 * @param   mixed                 $var    
 * @param   string|array|object   $key1   Array or object property.
 * @param   string|array|object   $key2   Array or object property.
 * @param   string                $key3   Array or object property.
 * @return  mixed
 */
function _checkshow( $var, $key1 = null, $key2 = null, $key3 = null ) {

  $result = _check( $var, $key1, $key2, $key3 );
  if( ! $result ) {
    return null;
  }
  $var_object = is_object( $var );
  $output = null;

  if( $var_object ) {
    $var = (array) $var;
  }

  if( $result ) {
    if( ! $key1 ) {
      $output = $var;
    }
    elseif( ! $key2 ) {
      $output = $var[$key1];
    }
    elseif( ! $key3 ) {
      $output = $var[$key1][$key2];
    }
    elseif( ! $key3 ) {
      $output = $var[$key1][$key2][$key3];
    }
  }

  if( $var_object ) {
    $var = (object) $var;
  }

  return $output;

}

/**
 * Include all necessary code into the app
 */
$lib = [
  'lib/dev.php',                        // For debugging and whatnot.
  'lib/settings.php',                   // Global settings.
  'lib/acf.php',                        // Custom ACF functions.
  'lib/admin.php',                      // Admin functionality.
  'lib/assets.php',                     // Scripts and stylesheets
  'lib/block-editor.php',               // Block editor stuff.
  'lib/extras.php',                     // Custom functions.
  'lib/setup.php',                      // Theme setup.
  'lib/theme-wrapper.php',              // Setup custom theme wrapper.
  'lib/*',                              // Include all index files for subdirectories.
];
foreach( $lib as $filepath ) {

  // Regular files
  if( ! strpos( $filepath, '*' ) ) {
    include_once( $filepath );
  }
  // Index files in subdirectories.
  else {
    foreach( glob( __DIR__ . "/{$filepath}/_*.php" ) as $index_file ) {
      include_once( $index_file );
    }
  }

}
unset( $filepath );