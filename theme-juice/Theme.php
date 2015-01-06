<?php

namespace ThemeJuice;

/**
 * Setup and initialize theme
 *
 * @package Theme Juice Starter
 * @subpackage Theme Juice Core
 * @author Ezekiel Gabrielse, Produce Results
 * @link https://produceresults.com
 * @copyright Produce Results (c) 2014
 * @since 1.0.0
 */
class Theme {

    /**
     * @var {String} - String that contains theme root directory
     */
    public $root;

    /**
     * @var {Array} - Array that contains theme assets
     */
    public $assets;

    /**
     * @var {Array} - Array that contains packages to include
     */
    public $packages;

    /**
     * @var {Object} - Instance of package \ThemeJuice\Functions,
     *  used to register theme functions.
     */
    public $functions;

    /**
     * @var {Object} - Instance of package \ThemeJuice\Shortcodes,
     *  used to register theme shortcodes.
     */
    public $shortcodes;

    /**
     * @var {Object} - Instance of package \ThemeJuice\Customizer,
     *  used to setup the theme customizer.
     */
    public $customizer;

    /**
     * Constructor
     *
     * @param {Array} $options - Array that contains theme settings
     */
    public function __construct( $options = array() ) {

        // Merge new options with defaults
        $options = array_merge( array(
            "root" => get_template_directory_uri(),
            "meta" => true,
            "assets" => array(),
            "packages" => array(
                "functions" => array(),
                "shortcodes" => array(),
                "customizer" => array(),
            )
        ), $options );

        // Root directory
        $this->root = $options["root"];

        // Assets
        $this->assets = $options["assets"];

        // Packages
        $this->packages = $options["packages"];

        // Lets not load this stuff on admin pages
        if ( ! $this->on_admin_pages() ) {

            /**
             * Start the output buffer, but don't return anything until rendering is complete;
             *  this is done so that we don't get a 'headers already sent' message when
             *  a request gets redirected (for example, when a URL doesn't contain a
             *  trailing slash and is redirected to a URL that does).
             *
             * @TODO - This might be able to be done away with if another hook is used for
             *  building the head, but up to this point I haven't found one that works.
             */
            ob_start();

            // Fix for PHP <= 5.3.x not allowing $this inside of closures
            $self = $this;

            // Add assets
            if ( ! empty( $self->assets ) ) {
                add_action( "init", function() use ( &$self ) {
                    foreach ( $self->assets as $handle => $opts ) {
                        $self->register_asset( $handle, $opts );
                    }
                });
            }

            // Add meta tags to head
            if ( $options["meta"] ) {
                add_action( "wp_head", function() use ( &$self ) {
                    $self->set_meta_tags();
                });
            }

            // Render head after WP has loaded
            add_action( "wp", function() use ( &$self ) {
                $self->render_head();
            });

            // Render footer before shutdown
            add_action( "shutdown", function() use ( &$self ) {
                $self->render_footer();
            });

            // Output buffers (this is here for the sake of clarity)
            register_shutdown_function( function() {
                while ( @ob_end_flush() );
            });
        }

        // Loop through and setup packages
        if ( $this->packages !== false ) {
            $this->setup_packages( $this->packages );
        }
    }

    /**
     * Make sure we're not on admin or login pages
     *
     * @return {Bool}
     */
    public function on_admin_pages() {
        return ( is_admin() || $GLOBALS["pagenow"] === "wp-login.php" );
    }


    /**
     * Setup included packages if available
     *
     * @param {Array} $options - Array of packages and their settings
     *
     * @return {Void}
     */
    public function setup_packages( $packages ) {

        // Import packages if available
        foreach ( array_keys( $packages ) as $package ) {

            // Create class name
            $class_name = ucfirst( $package );

            // Import functions
            if ( class_exists( $PackageClass = "\\ThemeJuice\\$class_name" ) ) {

                // If functions array was passed, then pass in options to constructor
                $this->$package = new $PackageClass( function() use ( $packages, $package ) {
                    if ( ! empty( $packages[$package] ) ) {
                        return $packages[$package];
                    } else {
                        return array();
                    }
                });
            } else {
                // @TODO - Show a dialog box in WP backend that links to all of the add-ons
                //  for Theme Juice, such as functions, shortcodes and the customizer.
            }
        }
    }

    /**
     * Register asset to theme with 'wp_enqueue_scripts()'
     *
     * @param {String} $handle               - The name of the script to register
     * @param {Array}  $opts                 - Array of options for the script
     * @param {String} $opts["type"]         - Type of asset to register
     * @param {Bool}   $opts["external"]     - Use external (off-site) asset
     * @param {String} $opts["location"]     - Location of asset (relative to $root if not external)
     * @param {Array}  $opts["dependencies"] - Assets that this asset depends on (i.e. jquery, etc.)
     * @param {String} $opts["version"]      - Version number for asset
     * @param {String} $opts["media"]        - Media rule for stylsheets
     * @param {Bool}   $opts["in_footer"]    - Output script to footer
     *
     * @return {Void}
     */
    public function register_asset( $handle, $opts ) {

        // Make sure asset type was passed
        if ( ! isset( $opts["type"] ) ) {
            throw new \Exception( "Attempted to register asset '$handle' without a type. Aborting mission." );
        }

        // Make sure asset location was passed
        if ( ! isset( $opts["location"] ) ) {
            throw new \Exception( "Attempted to register asset '$handle' without a location. Aborting mission." );
        } else {

            /**
             * Make sure this is not an external asset,
             *   else redefine location from $root
             */
            if ( ! isset( $opts["external"] ) || ! $opts["external"] ) {
                $opts["location"] = $this->root . "/" . $opts["location"];
            }
        }

        // Set 'dependencies' option if not set
        if ( ! isset( $opts["dependencies"] ) ) {
            $opts["dependencies"] = array();
        }

        // Set 'version' option if not set
        if ( ! isset( $opts["version"] ) ) {
            $opts["version"] = false;
        }

        switch ( $opts["type"] ) {
            case "style":

                // Set 'media' option if not set
                if ( ! isset( $opts["media"] ) ) {
                    $opts["media"] = "all";
                }

                // Make sure stylesheet is not already enqueued, if it is,
                //  deregister it to make room for new stylesheet
                if ( wp_style_is( $handle, "enqueued" ) ) {
                    wp_deregister_script( $handle );
                }

                // Enqueue stylesheet within closure
                add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
                    wp_enqueue_style( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["media"] );
                });

                break;
            case "script":

                // Set 'in_footer' option if not set
                if ( ! isset( $opts["in_footer"] ) ) {
                    $opts["in_footer"] = false;
                }

                // Make sure script is not already enqueued, if it is,
                //  deregister it to make room for new script
                if ( wp_script_is( $handle, "enqueued" ) ) {
                    wp_deregister_script( $handle );
                }

                // Enqueue script within closure
                add_action( "wp_enqueue_scripts", function() use ( $handle, $opts ) {
                    wp_enqueue_script( $handle, $opts["location"], $opts["dependencies"], $opts["version"], $opts["in_footer"] );
                });

                break;
            default:
                throw new \Exception( "Invalid asset type '" . $opts['type'] . "' for '$handle'. Aborting mission." );
                break;
        }
    }

    /**
     * Build out meta tags
     *
     * @return {Void}
     */
    public function set_meta_tags() {
        $buffer = array();

        if ( have_posts() ) {
            the_post();

            // Opengraph
            $buffer[] = "<meta property='og:type' content='article'>";
            $buffer[] = "<meta property='og:site_name' content='" . get_bloginfo( "name" ) . "'>";
            $buffer[] = "<meta property='og:title' content='" . get_the_title() . "'>";
            $buffer[] = "<meta property='og:url' content=" . get_the_permalink() . "'>";

            // Twitter card
            $buffer[] = "<meta name='twitter:card' content='summary'>";
            $buffer[] = "<meta name='twitter:title' content='" . get_the_title() . "'>";
            $buffer[] = "<meta name='twitter:url' content='" . get_the_permalink() . "'>";

            // Google+ schema.org
            $buffer[] = "<meta itemprop='name' content='" . get_the_title() . "'>";

            if ( is_single() || is_page() ) {

                $description = get_the_excerpt();

                // Description
                $buffer[] = "<meta name='description' content='" . $description . "'>";
                // Opengraph
                $buffer[] = "<meta property='og:description' content='" . $description . "'>";
                // Twitter card
                $buffer[] = "<meta name='twitter:description' content='" . $description . "'>";
                // Google+ schema.org
                $buffer[] = "<meta itemprop='description' content='" . $description . "'>";

                // Get post thumbnail
                if ( has_post_thumbnail() ) {

                    $image = wp_get_attachment_image_src( get_post_thumbnail_id(), "full" );

                    // Opengraph
                    $buffer[] = "<meta property='og:image' content='" . $image[0] . "'>";
                    // Twitter card
                    $buffer[] = "<meta name='twitter:image' content='" . $image[0] . "'>";
                    // Google+ schema.org
                    $buffer[] = "<meta itemprop='image' content='" . $image[0] . "'>";
                }
            }

            rewind_posts();
        } else {

            // Opengraph
            $buffer[] = "<meta property='og:type' content='website'>";
            $buffer[] = "<meta property='og:site_name' content='" . get_bloginfo( "name" ) . "'>";
            $buffer[] = "<meta property='og:title' content='" . get_the_title() . "'>";
            $buffer[] = "<meta property='og:url' content='" . home_url() . "'>";

            // Twitter card
            $buffer[] = "<meta name='twitter:card' content='summary'>";
            $buffer[] = "<meta name='twitter:title' content='" . get_the_title() . "'>";
            $buffer[] = "<meta name='twitter:url' content='" . home_url() . "'>";

            // Google+ schema.org
            $buffer[] = "<meta itemprop='name' content='" . get_the_title() . "'>";
        }

        // Return current buffer
        echo implode( "", $buffer );
    }

    /**
     * Render HTML doctype and head, 'wp_head', opening tags
     *
     * @return {Void}
     */
    public function render_head() {
        $buffer = array();

        // Doctype
        $buffer[] = "<!DOCTYPE html>";
        $buffer[] = "<html class='no-js'>";
        $buffer[] = "<head>";

        // Title
        $buffer[] = "<title>" . wp_title( "-", false, "right" ) . "</title>";

        // Favicon
        $buffer[] = "<link rel='shortcut icon' href='" . get_template_directory_uri() . "/favicon.ico' />";

        // Required meta tags
        $buffer[] = "<meta charset='" . get_bloginfo( "charset" ) . "'>";
        $buffer[] = "<meta http-equiv='X-UA-Compatible' content='IE=edge, chrome=1' />";
        $buffer[] = "<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=no'>";

        // Return current buffer
        echo implode( "", $buffer );

        // Wordpress hook
        wp_head();

        // Create new buffer
        $buffer = array();

        // Close head and open body
        $buffer[] = "</head>";
        $buffer[] = "<body class='" . implode( " ", get_body_class() ) . "'>";

        // Return current buffer
        echo implode( "", $buffer );
    }

    /**
     * Render 'wp_footer', close out tags
     *
     * @return {Void}
     */
    public function render_footer() {
        $buffer = array();

        // Wordpress hook
        wp_footer();

        // Render close body and html
        $buffer[] = "</body>";
        $buffer[] = "</html>";

        // Return current buffer
        echo implode( "", $buffer );
    }
}
