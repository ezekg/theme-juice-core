<?php

namespace ThemeJuice\Factories;

class PackageFactory {

  /**
   * Initialize passed package
   *
   * @param {String} $package
   * @param {Array}  $options
   *
   * @return {Package|Null}
   */
  public static function setup_package( $package, $options ) {
    $class_name = self::format_package_to_class_name( $package );

    if ( class_exists( $package_class = "\\ThemeJuice\\Packages\\$class_name" ) ) {
      return new $package_class( $options );
    }
    
    return null;
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
