<?php

namespace ThemeJuice;

class Theme {

  /**
   * @var {Array}
   */
  private $assets;

  /**
   * @var {Array}
   */
  private $packages;

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
    $options = array_merge( self::$defaults, $options );

    $this->assets = $options["assets"];
    $this->packages = $options["packages"];

    new Loaders\AssetLoader( self::$assets );
    new Loaders\PackageLoader( self::$packages );
  }

  /**
   * Render doctype, head and body tags
   *
   * @return {Void}
   */
  public function render_head() {
    do_action( "tj_before_render_head" );

    $buffer = array();
    $buffer[] = "<!doctype html>";
    $buffer[] = "<html class='no-js'>";
    $buffer[] = "<head>";
    $buffer[] = "<meta charset='" . get_bloginfo( "charset" ) . "'>";
    $buffer[] = "<meta http-equiv='x-ua-compatible' content='ie=edge' />";
    $buffer[] = "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    $buffer[] = "<link rel='shortcut icon' href='" . get_template_directory_uri() . "/favicon.ico' />";
    echo implode( "", $buffer );

    wp_head();

    $buffer = array();
    $buffer[] = "</head>";
    $buffer[] = "<body class='" . implode( " ", get_body_class() ) . "'>";
    echo implode( "", $buffer );

    do_action( "tj_after_render_head" );
  }

  /**
   * Render footer
   *
   * @return {Void}
   */
  public function render_footer() {
    do_action( "tj_before_render_footer" );

    wp_footer();

    $buffer = array();
    $buffer[] = "</body>";
    $buffer[] = "</html>";
    echo implode( "", $buffer );

    do_action( "tj_after_render_footer" );
  }
}
