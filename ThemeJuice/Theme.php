<?php

namespace ThemeJuice;

use \ThemeJuice\Loaders\AssetLoader;
use \ThemeJuice\Loaders\PackageLoader;

class Theme {

  /**
   * @var {Array}
   */
  public $assets;

  /**
   * @var {Array}
   */
  public $packages;

  /**
   * @var {Array}
   */
  private $defaults = array(
    "assets" => array(),
    "packages" => array(),
  );

  /**
   * Constructor
   *
   * @param {Array} $options
   */
  public function __construct( $options = array() ) {
    $options = array_merge( $this->defaults, $options );

    $this->assets = $options["assets"];
    $this->packages = $options["packages"];

    AssetLoader::load( $this->assets );
    PackageLoader::load( $this->packages );
  }

  /**
   * Render doctype, head and body tags
   *
   * @return {Void}
   */
  public function render_head() {
    $buffer = array();
    $buffer = apply_filters( "tj_before_render_doctype", $buffer );
    $buffer[] = "<!doctype html>";
    $buffer = apply_filters( "tj_after_render_doctype", $buffer );
    $buffer = apply_filters( "tj_before_render_html", $buffer );
    $buffer[] = "<html class='no-js'>";
    $buffer = apply_filters( "tj_before_render_head", $buffer );
    $buffer[] = "<head>";
    $buffer[] = "<meta charset='" . get_bloginfo( "charset" ) . "'>";
    $buffer[] = "<meta http-equiv='x-ua-compatible' content='ie=edge' />";
    $buffer[] = "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    $buffer[] = "<link rel='shortcut icon' href='" . get_template_directory_uri() . "/favicon.ico' />";
    echo implode( "", $buffer );

    wp_head();

    $buffer = array();
    $buffer[] = "</head>";
    $buffer = apply_filters( "tj_after_render_head", $buffer );
    $buffer = apply_filters( "tj_before_render_body", $buffer );
    $buffer[] = "<body class='" . implode( " ", get_body_class() ) . "'>";
    echo implode( "", $buffer );
  }

  /**
   * Render footer
   *
   * @return {Void}
   */
  public function render_footer() {
    wp_footer();

    $buffer = array();
    $buffer[] = "</body>";
    $buffer = apply_filters( "tj_after_render_body", $buffer );
    $buffer[] = "</html>";
    $buffer = apply_filters( "tj_after_render_html", $buffer );
    echo implode( "", $buffer );
  }
}
