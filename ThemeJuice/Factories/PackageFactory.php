<?php

namespace ThemeJuice\Factories;
use \ThemeJuice\Theme as Theme;

class PackageFactory {

  /**
   * Initialize passed package
   *
   * @param {String} $package
   *
   * @return {Void}
   */
  public static function setup_package( $package ) {
    $class_name = self::format_package_to_class_name( $package );

    if ( class_exists( $package_class = "\\ThemeJuice\\Packages\\$class_name" ) ) {
      Theme::packages[$package] = new $package_class( function() use ( $packages, $package ) {
        if ( ! empty( $packages[$package] ) ) {
          return $packages[$package];
        } else {
          return array();
        }
      });
    }
  }

  /**
   * Format package name to valid class name
   *
   * @param {String} $package
   *
   * @return {String}
   */
  private static function format_package_to_class_name( $package ) {
    return implode( "", array_map( "ucfirst", preg_split( "/[_-]+/", $package )));
  }
}
