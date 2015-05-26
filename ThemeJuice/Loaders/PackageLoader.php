<?php

namespace ThemeJuice\Loaders;
use \ThemeJuice\Factories\PackageFactory as PackageFactory;

class PackageLoader implements LoaderInterface {

  /**
   * Filter out disabled packages, then load passed packages
   *
   * @param {Array} $packages
   *
   * @return {Void}
   */
  public static function load( $packages ) {
    if ( $packages !== false ) {
      self::setup_packages( array_filter( $packages, function( $package ) {
        return $package !== false;
      }));
    }
  }

  /**
   * Setup passed packages
   *
   * @param {Array} $packages
   *
   * @return {Void}
   */
  private static function setup_packages( $packages ) {
    foreach ( array_keys( $packages ) as $package ) {
      PackageFactory::setup_package( $package, $packages[$package] );
    }
  }
}
