<?php
/*
Plugin Name: External Links to New Window
Plugin URI: http://etalented.co.uk/
Description: The external link plugin for WordPress will allow site admins to automatically open external site links to a new window with options for an icon and nofollow rules. 
Author: etalented
Author URI: http://etalented.co.uk/
Version: 2.0.3
Text Domain: external-links-new-window
GitHub Theme URI: https://github.com/etalented/external-links-to-new-window
*/

/**
 *  External Links to New Window core file
 *
 * This file contains all the logic required for the plugin
 *
 * @link		http://wordpress.org/extend/plugins/external-links-to-new-window/
 *
 * @package 		External Links to New Window
 * @copyright		Copyright (c) 2018 Etalented Limited, credit to Christopher Ross (the original author)
 * @license		http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, v2 (or newer)
 *
 * @since 		External Links to New Window 1.0
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'ExternalLinksNewWindow' ) ) :

final class ExternalLinksNewWindow {
    
	public $version = '2.0.3';
    
	protected static $_instance = null;
    
    public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
    
    function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
    
    function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}
    
    function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
	}
    
    function define_constants() {
		$this->define( 'EXTERNALLINKSNEWWINDOW_ABSPATH', dirname( __FILE__ ) . '/' );
		$this->define( 'EXTERNALLINKSNEWWINDOW_VERSION', $this->version );
	}
    
    function includes() {
        require_once( EXTERNALLINKSNEWWINDOW_ABSPATH . 'includes/library/simple_html_dom.php' );
    }
    
    function init_hooks() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_head', array( $this, 'admin_style' ) );
        add_filter( 'the_content', array( $this, 'parse_the_content' ) );
        add_action( 'wp_head', array( $this, 'plugin_style' ) );
	}
    
    function plugin_style() {
        $setting = json_decode( get_option( 'thisismyurl_externallinks' ) );
        if ($setting[2]) {
        ?>
            <style type="text/css">
                .thisismyurl_external{
                    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAOVBMVEX////39/fa2trW1tbFxcW9vb2xsbGpqamUlJSIiIiEhIRzc3Nra2tnZ2daWlpSUlJKSkpGRkb///9eei67AAAAE3RSTlP///////////////////////8Asn3cCAAAAEtJREFUCNcdy0kCwCAIBMFJwrgEQcz/Hxu0T3VpfNmIiOnYtEK2eVgVkIBmxsNVHieoJRDSX9CqDSxe43XNA0uAfh/ORko59FjZ5g/2GQR2Q86/9gAAAABJRU5ErkJggg==) no-repeat right;
                    padding-right: 14px;
                }
            </style>
        <?php
        }
    }
    
    function admin_style() {
        ?>
        <style type="text/css">
            .thisismyurl{ background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAMAAABEpIrGAAAA0lBMVEX///9Jj6xrpLxJj6xrpLxhnbdJj6xxqL5moblhnbdJj6xhnbdJj6xxqL5rpLxmoblJj6xxqL5hnbdJj6x1qsBJj6xrpLxmoblJj6xrpLxJj6x4rMFJj6x1qsBJj6xxqL5Jj6x4rMF1qsBxqL5Jj6z////3+vv0+Pru9ffm7/Tf6/Hd6u/V5ezS4+rO4OjG3OW71eC509+z0Nyvztuqy9ikx9aZwNCRu82OucuMtcWCssZ/sMWAsMV4rMF1qsBrpLxmoblhnbdamrRUlrFSlLBJj6wucLoaAAAAJXRSTlMAESIiMzMzRERERFVVZmZmZnd3d4iImZmZqqq7u8zM3d3u7u7u5Bc3VAAAAehJREFUOMttU9li0zAQdAkNEEoJhXIUp4HEK1vxJeLYiQ/FuGL//5dYWb5I2SdLM56dPWRZQ7z9tj5LHevPr63ncbfN9wEDCi84lNvbC/h6m0cwiShfXU/x27PQt2muM+Qpcdl+MxH5UnKAuJRDlMQPyg89/p5w7yj/iaMHvHxn8DdnTgd5EfRTtHnREtYCvBEXQc/wYL/S+LICGPUrlg1ZgJWviPBDgBiVo2D8jkGQxE0DYBJUR/1XbjCtWQKTL62vGYin9jIAP/eNWMZaXgTpJ+sxhgIbfTzoRhsBbngpxCsLOTSIWqOKQurgQVM9YyIHf2MhAFI8nSV1L9oB8KxiaWeTTPQE/FPpIbHIo3Hu+joARgLGZo47PSlRPSfIYdShTyIdwZOdSUR/XAYWc9ab/GnKRMym69J3lsr8bt2fQBAhnOBe87s+m0Zld9ZcAajBYhvSuEYFHGftsJKpRwg7HFNIbT3uxoUa8TQQmg6vwVULvRCPGXCF8S/X4EmfgEPx0G7UzInBL6nPsaa4qsN9EM6VWcolHdonk8js1OE1p+Lm/VovlWjV696eSqgZuBwfxo2TkQQvWn9NQXS3cObTpzWzVcrGSt1UPVxdvM6FjUUSkk03TAq0F/953/N729EpHPvjbLz9C0A/pR4RVDYdAAAAAElFTkSuQmCC) no-repeat;}
        </style>
        <?php
    }
    
    function parse_the_content( $content ) {
        $options = json_decode( get_option( 'thisismyurl_externallinks' ) );
        error_log(print_r($options,true));
        return $this->transform_html( $content, $options );
    }
    
    function transform_html( $html, $options ) {
        $html = str_get_html( $html );
        $anchors = $html->find( 'a' );
        foreach( $anchors as $a ) {
            $href = strtolower( $a->href );
            $window = null;
            $nofollow = null;
            $class = null;

            if ( $options[0] ) {
                $window = '_blank';
            }
            if ( $options[1] ) {
                $nofollow = 'nofollow';
            }
            if ( $options[2] ) {
                $class = 'thisismyurl_external';
            }

            if ( stripos( $href, get_bloginfo('url') ) === false && substr($href, 0, 4) == 'http' ) {
                if ( $window ) {
                    $a->target = $window;
                }
                if ( $nofollow ) {
                    $a->rel = $nofollow;
                }
                if ( $class ) {
                    $a->class = $class;
                }
            }
        }
        return $html->save();
    }
    
    function admin_menu() {
        $hook_suffix = add_options_page( 'External Links', 'External Links', 'edit_posts', 'external_links_new_window', array( $this, 'admin_page' ) );
        
        add_action( 'load-' . $hook_suffix, array( $this, 'enqueue_scripts' ) );
    }
    
    function enqueue_scripts() {
        wp_enqueue_style('dashboard');
        wp_enqueue_script('postbox');
        wp_enqueue_script('dashboard');
    }
    
    function admin_page() {
        if ($_POST) {
            $setting = array($_POST['setting1'],$_POST['setting2'],$_POST['setting3']);
            update_option('thisismyurl_externallinks', json_encode($setting));	
        }

        if (empty($setting)) {
            $setting = json_decode(get_option('thisismyurl_externallinks'));
        }

        $settingcount = 0;
        foreach ($setting as $settingitem) {
            if ($setting[$settingcount]) {$cb[$settingcount] = ' checked="checked"';}	
            $settingcount++;
        }
        ?>
        <div class="wrap">
            <h2><?php _e( 'Settings for External Links to New Window','external-links-new-window' ); ?></h2>
            <div class="postbox-container">
                <form method="post" action="">
                    <input type="hidden" name="action" value="update" /> 
                    <input type="hidden" name="page_options" value="setting1,setting2,setting3" />
                    <div class="metabox-holder">
                        <div class="meta-box-sortables">
                            <div id="edit-pages" class="postbox">
                                <h3><span><?php _e( 'Plugin Settings','external-links-new-window' ) ?></span></h3>
                                <div class="inside">
                                    <p><input type="checkbox" name="setting1" id="setting1"<?php echo $cb[0] ?>>&nbsp;<label for="setting1"><?php _e( 'Open external content in new window','external-links-new-window' ); ?></label></p>
                                    <p><input type="checkbox" name="setting2" id="setting2"<?php echo $cb[1] ?>>&nbsp;<label for="setting2"><?php _e( 'Add nofollow attribute','external-links-new-window' ); ?></label></p>
                                    <p><input type="checkbox" name="setting3" id="setting3"<?php echo $cb[2] ?>>&nbsp;<label for="setting3"><?php _e( 'Include Icon after link','external-links-new-window' ); ?></label></p>
                                </div>
                            </div>
                            <input type="submit" name="Submit" class="button-primary" value="<?php _e( 'Save Settings','external-links-new-window' ); ?>" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}

endif;

function external_links_new_window() {
	return ExternalLinksNewWindow::instance();
}

// Global for backwards compatibility.
$GLOBALS['external_links_new_window'] = external_links_new_window();