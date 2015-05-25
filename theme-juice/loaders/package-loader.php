<?php

namespace ThemeJuice\Loaders;

class PackageLoader {

  /**
   * Constructor
   *
   * @param {Array} $packages
   */
  public function __construct( $packages = array() ) {
    $this->load_packages( $packages );
  }

  /**
   * Filter out disabled packages, then load packages
   *
   * @param {Array} $packages
   *
   * @return {Void}
   */
  private function load_packages( $packages ) {
    if ( $this->packages !== false ) {
      $this->setup_packages( array_filter( $this->packages, function( $package ) {
        return $package !== false;
      }));
    }
  }

  /**
   * Setup included packages if available
   *
   * @param {Array} $packages
   *
   * @return {Void}
   */
  private function setup_packages( $packages ) {
    foreach ( array_keys( $packages ) as $package )
      $class_name = format_package_to_class_name( $package );

      if ( class_exists( $package_class = "\\ThemeJuice\\Packages\\$class_name" ) ) {
        $this->{$package} = new $package_class( function() use ( $packages, $package ) {
          if ( ! empty( $packages[$package] ) ) {
            return $packages[$package];
          } else {
            return array();
          }
        });
      }
    }
  }

  /**
   * Format package name to valid class name
   *
   * @param {String} $package
   *
   * @return {String}
   */
  private function format_package_to_class_name( $package ) {
    return implode( "", array_map( "ucfirst", preg_split( "/[_-]+/", $package )));
  }
}
