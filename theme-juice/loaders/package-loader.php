<?php

namespace ThemeJuice\Loaders;

class PackageLoader {

  /**
   * Filter out disabled packages, then load passed packages
   *
   * @param {Array} $packages
   *
   * @return {Void}
   */
  public static function load_packages( $packages ) {
    if ( $this->packages !== false ) {
      $this->setup_packages( array_filter( $this->packages, function( $package ) {
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
  private function setup_packages( $packages ) {
    foreach ( array_keys( $packages ) as $package )
      Factories\PackageFactory::setup_package( $package );
    }
  }
}
