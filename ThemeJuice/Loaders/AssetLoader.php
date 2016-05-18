<?php

namespace ThemeJuice\Loaders;

class AssetLoader implements LoaderInterface {

  /**
   * Add each assets registration method to init action
   *
   * @param {Array} $assets
   *
   * @return {Void}
   */
  public static function load( $assets ) {
    if ( ! self::on_admin_pages() && ! empty( $assets ) ) {
      foreach ( $assets as $handle => $opts ) {
        self::register_asset( $handle, $opts );
      }
    }
  }

  /**
   * Register asset to theme with 'wp_enqueue_scripts()'
   *
   * @param {String} $handle               - The name of the script to register
   * @param {Array}  $opts                 - Array of options for the script
   * @param {String} $opts["type"]         - Type of asset to register
   * @param {Bool}   $opts["external"]     - Use external (off-site) asset, i.e. CDN
   * @param {String} $opts["location"]     - Location of asset (relative to theme dir if not external)
   * @param {Array}  $opts["dependencies"] - Assets that this asset depends on (i.e. jquery, etc.)
   * @param {String} $opts["version"]      - Version number for asset
   * @param {String} $opts["media"]        - Media rule for stylesheets
   * @param {Bool}   $opts["in_footer"]    - Output script to footer
   * @param {Int}    $opts["priority"]     - Priority of script
   *
   * @return {Void}
   */
  public static function register_asset( $handle, $opts ) {

    if ( ! isset( $opts["type"] ) ) {
      throw new \Exception( "Attempted to register asset '{$handle}' without a type. Aborting mission." );
    }

    if ( ! isset( $opts["location"] ) ) {
      throw new \Exception( "Attempted to register asset '{$handle}' without a location. Aborting mission." );
    }

    if ( ! isset( $opts["external"] ) || ! $opts["external"] ) {
      $opts["location"] = get_template_directory_uri() . "/{$opts["location"]}";
    }

    if ( ! isset( $opts["dependencies"] ) ) {
      $opts["dependencies"] = array();
    }

    if ( ! isset( $opts["version"] ) ) {
      $opts["version"] = false;
    }

    if ( ! isset( $opts["priority"] ) ) {
      $opts["priority"] = 50;
    }

    switch ( $opts["type"] ) {
      case "style":
        self::register_style( $handle, $opts );
        break;
      case "script":
        self::register_script( $handle, $opts );
        break;
      default:
        throw new \Exception( "Invalid asset type '{$opts['type']}' for '{$handle}'. Aborting mission." );
        break;
    }
  }

  /**
   * Register and enqueue stylesheet
   *
   * @param {String} $handle
   * @param {Array}  $opts
   *
   * @return {Void}
   */
  private static function register_style( $handle, $opts ) {
    if ( ! isset( $opts["media"] ) ) {
      $opts["media"] = "all";
    }

    add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
      if ( wp_style_is( $handle, "registered" ) ) {
        wp_deregister_style( $handle );
      }

      wp_enqueue_style( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["media"] );
    }, $opts["priority"] );
  }

  /**
   * Register and enqueue script
   *
   * @param {String} $handle
   * @param {Array}  $opts
   *
   * @return {Void}
   */
  private static function register_script( $handle, $opts ) {
    if ( ! isset( $opts["in_footer"] ) ) {
      $opts["in_footer"] = false;
    }

    add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
      if ( wp_script_is( $handle, "registered" ) ) {
        wp_deregister_script( $handle );
      }

      wp_enqueue_script( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["in_footer"] );
    }, $opts["priority"] );
  }

  /**
   * @return {Bool}
   */
  private static function on_admin_pages() {
    return ( is_admin() || $GLOBALS["pagenow"] === "wp-login.php" );
  }
}
