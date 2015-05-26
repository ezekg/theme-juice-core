<?php

namespace ThemeJuice\Loaders;
use \ThemeJuice\Theme as Theme;

class AssetLoader {

  /**
   * Add each assets registration method to init action
   *
   * @param {Array} $assets
   *
   * @return {Void}
   */
  public static function load_assets( $assets ) {

    // @TODO Fix for PHP <= 5.3.x not allowing $this inside of closures
    $self = __CLASS__;

    if ( ! self::on_admin_pages() && ! empty( $assets ) ) {
      add_action( "init", function() use ( &$self ) {
        foreach ( $assets as $handle => $opts ) {
          $self::register_asset( $handle, $opts );
        }
      });
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
   *
   * @return {Void}
   */
  private static function register_asset( $handle, $opts ) {

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

    if ( wp_style_is( $handle, "registered" ) ) {
      wp_deregister_style( $handle );
    }

    add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
      wp_enqueue_style( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["media"] );
    }, 50 );
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

    if ( wp_script_is( $handle, "registered" ) ) {
      wp_deregister_script( $handle );
    }

    add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
      wp_enqueue_script( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["in_footer"] );
    }, 50 );
  }

  /**
   * @return {Bool}
   */
  private static function on_admin_pages() {
    return ( is_admin() || $GLOBALS["pagenow"] === "wp-login.php" );
  }
}
