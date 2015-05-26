<?php

namespace ThemeJuice;

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
    "packages" => array(),
    "assets" => array(),
  );

  /**
   * Constructor
   *
   * @param {Array} $options
   */
  public function __construct( $options = array() ) {
    $options = array_merge( $this->defaults, $options );

    $this->packages = $options["packages"];
    $this->assets = $options["assets"];

    Loaders\PackageLoader::load( $this->packages );
    Loaders\AssetLoader::load( $this->assets );
  }

  /**
   * Render doctype, head and body tags
   *
   * @return {Void}
   */
  public function render_head() {
    $buffer = array();

    do_action( "tj_before_render_doctype" );
    $buffer[] = "<!doctype html>";
    do_action( "tj_after_render_doctype" );
    do_action( "tj_before_render_html" );
    $buffer[] = "<html class='no-js'>";
    do_action( "tj_before_render_head" );
    $buffer[] = "<head>";
    $buffer[] = "<meta charset='" . get_bloginfo( "charset" ) . "'>";
    $buffer[] = "<meta http-equiv='x-ua-compatible' content='ie=edge' />";
    $buffer[] = "<meta name='viewport' content='width=device-width, initial-scale=1'>";
    $buffer[] = "<link rel='shortcut icon' href='" . get_template_directory_uri() . "/favicon.ico' />";
    echo implode( "", $buffer );

    wp_head();

    $buffer = array();
    $buffer[] = "</head>";
    do_action( "tj_after_render_head" );
    do_action( "tj_before_render_body" );
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
    do_action( "tj_after_render_body" );
    $buffer[] = "</html>";
    do_action( "tj_after_render_html" );
    echo implode( "", $buffer );
  }
}
