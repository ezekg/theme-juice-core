<?php

namespace ThemeJuice;

/**
 * This is used to register assets, and render the doctype, head and body tags.
 *
 * @since 0.1.0
 */
class Theme {

    /**
     * @var {String}
     *   String that contains WP root directory
     *
     * @since 0.1.0
     */
    public $root;

    /**
     * @var {Array}
     *   Array that contains theme assets
     *
     * @since 0.1.0
     */
    public $assets;

    /**
     * Constructor
     *
     * @param {Array} $options
     *   Array that contains theme settings
     *
     * @since 0.1.0
     */
    public function __construct( $options = array() ) {

        // Merge new options with defaults
        if ( ! empty( $options ) ) {
            $options = array_merge( array(
                "root" => get_template_directory_uri(),
                "assets" => array()
            ), $options );
        }

        // Root directory
        $this->root = $options["root"];

        // Assets
        $this->assets = $options["assets"];

        if ( ! $this->on_admin_pages() ) {

            // Fix for PHP <= 5.3.x not allowing $this inside of closure
            $self = $this;

            // Add assets
            if ( ! empty( $self->assets ) ) {
                add_action( "init", function() use ( $self ) {
                    foreach ( $self->assets as $handle => $opts ) {
                        $self->register_asset( $handle, $opts );
                    }
                });
            }

            // Render header after WP has loaded
            add_action( "wp", function() use ( $self ) {
                $self->render_head();
            }, 50);

            // Render footer before shutdown
            add_action( "shutdown", function() use ( $self ) {
                $self->render_footer();
            }, 50);
        }
    }

    /**
     * Make sure we're not on admin or login pages
     *
     * @return {Bool}
     *
     * @since 0.1.0
     */
    public function on_admin_pages() {
        global $pagenow;
        return ( is_admin() || $pagenow == "wp-login.php" ) ? true : false;
    }

    /**
     * Register asset to theme with `wp_enqueue_scripts()`
     *
     * @param {String} $handle
     *   The name of the script to register
     * @param {Array}  $opts
     *   Array of options for the script
     *
     * @param {String} $opts["type"]
     *   Type of asset to register
     * @param {Bool}   $opts["external"]
     *   Use external (off-site) asset
     * @param {String} $opts["location"]
     *   Location of asset (relative to $root if not external)
     * @param {Array}  $opts["dependencies"]
     *   Assets that this asset depends on (i.e. jquery, etc.)
     * @param {String} $opts["version"]
     *   Version number for asset
     *
     * @return {Void}
     *
     * @since 0.1.0
     */
    public function register_asset( $handle, $opts ) {

        // Make sure asset type was passed
        if ( ! isset( $opts["type"] ) ) {
            throw new \Exception( "Attempted to register an asset without a type. Aborting mission." );
        }

        // Make sure asset location was passed
        if ( ! isset( $opts["location"] ) ) {
            throw new \Exception( "Attempted to register asset `$handle` without a location. Aborting mission." );
        } else {
            // Make sure this is not an external asset,
            //   else redefine location from $root
            if ( ! isset( $opts["external"] ) === true ) {
                $opts["location"] = $this->root . "/" . $opts["location"];
            }
        }

        if ( ! isset( $opts["dependencies"] ) ) {
            $opts["dependencies"] = array();
        }

        if ( ! isset( $opts["version"] ) ) {
            $opts["version"] = false;
        }

        switch ( $opts["type"] ) {
            case "style":

                // Make sure it's not already enqueued
                if ( wp_style_is( $handle, "enqueued" ) ) {
                    return;
                }

                // Enqueue script within closure
                $enqueue_style = function() use ( $handle, $opts ) {
                    wp_enqueue_style( $handle, $opts["location"], $opts["dependencies"], $opts["version"] );
                };

                // Add closure to `wp_enqueue_scripts()`
                add_action( "wp_enqueue_scripts", $enqueue_style );
                break;
            case "script":

                // Make sure it's not already enqueued
                if ( wp_script_is( $handle, "enqueued" ) ) {
                    return;
                }

                // Enqueue script within closure
                $enqueue_script = function() use ( $handle, $opts ) {
                    wp_enqueue_script( $handle, $opts["location"], $opts["dependencies"], $opts["version"] );
                };

                // Add closure to `wp_enqueue_scripts()`
                add_action( "wp_enqueue_scripts", $enqueue_script );
                break;
            default:
                throw new \Exception( "Invalid asset type `" . $opts['type'] . "` for `$handle`. Aborting mission." );
                break;
        }
    }

    /**
     * Render HTML doctype and head, wp_head, opening tags
     *
     * @return {Void}
     *
     * @since 0.1.0
     */
    public function render_head() {

        // New buffer
        $buffer = array();

        // Doctype
        $buffer[] = '<!DOCTYPE html>';
        $buffer[] = '<html class="no-js">';
        $buffer[] = '<head>';

        // Title
        $buffer[] = '<title>' . wp_title( "-", false ) . '</title>';

        // Favicon
        $buffer[] = '<link rel="shortcut icon" href="' . get_template_directory_uri() . '/favicon.ico" />';

        // Meta tags
        $buffer[] = '<meta charset="' . get_bloginfo( 'charset' ) . '">';
        $buffer[] = '<meta http-equiv="X-UA-Compatible" content="IE=edge, chrome=1" />';
        $buffer[] = '<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">';

        if ( have_posts() ) {
            the_post();

            // Opengraph
            $buffer[] = '<meta property="og:type" content="article">';
            $buffer[] = '<meta property="og:site_name" content="' . get_bloginfo( "name" ) . '">';
            $buffer[] = '<meta property="og:title" content="' . get_the_title() . '">';
            $buffer[] = '<meta property="og:url" content="' . get_the_permalink() . '">';

            // Twitter card
            $buffer[] = '<meta name="twitter:card" content="summary">';
            $buffer[] = '<meta name="twitter:title" content="' . get_the_title() . '">';
            $buffer[] = '<meta name="twitter:url" content="' . get_the_permalink() . '">';

            // Google+ schema.org
            $buffer[] = '<meta itemprop="name" content="' . get_the_title() . '">';

            if ( get_the_excerpt() != "" ) {
                // Description
                $buffer[] = '<meta name="description" content="' . get_the_excerpt() . '">';
                // Opengraph
                $buffer[] = '<meta property="og:description" content="' . get_the_excerpt() . '">';
                // Twitter card
                $buffer[] = '<meta name="twitter:description" content="' . get_the_excerpt() . '">';
                // Google+ schema.org
                $buffer[] = '<meta itemprop="description" content="' . get_the_excerpt() . '">';
            }

            if ( get_the_post_thumbnail() != "" ) {
                $image = wp_get_attachment_image_src( get_post_thumbnail_id(), "full" );
                // Opengraph
                $buffer[] = '<meta property="og:image" content="' . $image[0] . '">';
                // Twitter card
                $buffer[] = '<meta name="twitter:image" content="' . $image[0] . '">';
                // Google+ schema.org
                $buffer[] = '<meta itemprop="image" content="' . $image[0] . '">';
            }

            rewind_posts();
        } else {

            // Opengraph
            $buffer[] = '<meta property="og:type" content="website">';
            $buffer[] = '<meta property="og:site_name" content="' . get_bloginfo( "name" ) . '">';
            $buffer[] = '<meta property="og:title" content="' . get_the_title() . '">';
            $buffer[] = '<meta property="og:url" content="' . home_url() . '">';

            // Twitter card
            $buffer[] = '<meta name="twitter:card" content="summary">';
            $buffer[] = '<meta name="twitter:title" content="' . get_the_title() . '">';
            $buffer[] = '<meta name="twitter:url" content="' . home_url() . '">';

            // Google+ schema.org
            $buffer[] = '<meta itemprop="name" content="' . get_the_title() . '">';
        }

        // Return current buffer
        echo implode( PHP_EOL, $buffer );

        // Wordpress hook
        wp_head();

        // Create new buffer
        $buffer = array();

        // Close head and open body
        $buffer[] = '</head>';
        $buffer[] = '<body class="' . implode( " ", get_body_class() ) . '">';

        // Return current buffer
        echo implode( PHP_EOL, $buffer );
    }

    /**
     * Render wp_footer, close out tags
     *
     * @return {Void}
     *
     * @since 0.1.0
     */
    public function render_footer() {

        // New buffer
        $buffer = array();

        // Wordpress hook
        wp_footer();

        // Render close body and html
        $buffer[] = '</body>';
        $buffer[] = '</html>';

        // Return current buffer
        echo implode( PHP_EOL, $buffer );
    }
}
