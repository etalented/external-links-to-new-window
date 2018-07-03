<?php
/*
Plugin Name: External Links to New Window
Plugin URI: https://etalented.co.uk/wordpress-plugin-external-links-to-a-new-window
Description: Open all external links in your blog posts and pages automatically in a new tab or new window when clicked or tapped.
Author: etalented
Author URI: https://etalented.co.uk/
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

class ExternalLinksNewWindow {
    
	public $version = '2.0.3';
    
	protected static $_instance = null;
    
    protected $is_plugin_enabled = false;
    
    protected $options = [
        'nofollow' => false,
        'icon' => false,
    ];
    
    public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
    
    private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}
    
    private function is_request( $type ) {
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
    
    public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();
		$this->convert_options();
        $this->init_options();
	}
    
    private function define_constants() {
		$this->define( 'EXTERNALLINKSNEWWINDOW_ABSPATH', dirname( __FILE__ ) . '/' );
		$this->define( 'EXTERNALLINKSNEWWINDOW_VERSION', $this->version );
	}
    
    private function includes() {
        require_once( EXTERNALLINKSNEWWINDOW_ABSPATH . 'includes/library/simple_html_dom.php' );
    }
    
    private function init_hooks() {
        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_head', array( $this, 'admin_style' ) );
        add_filter( 'the_content', array( $this, 'parse_the_content' ) );
        add_action( 'wp_head', array( $this, 'plugin_style' ) );
	}
    
    public function init_options() {
        $this->is_plugin_enabled = $this->get_option( 'externallinksnewwindow_enabled' );
        $this->options['nofollow'] = $this->get_option( 'externallinksnewwindow_nofollow' );
        $this->options['icon'] = $this->get_option( 'externallinksnewwindow_withicon' );
	}
    
    public function get_option( $name ) {
        return get_option( $name );
    }
    
    public function update_option( $name, $value ) {
        return update_option( $name, $value, true );
    }
    
    public function add_option( $name, $value ) {
        return add_option( $name, $value, true );
    }
    
    public function delete_option( $name ) {
        return delete_option( $name );
    }
    
    public function set_options( $data ) {
        $this->update_option( 'externallinksnewwindow_enabled', $data['external_links_enabled'] );
        $this->update_option( 'externallinksnewwindow_nofollow', $data['external_links_nofollow'] );
        $this->update_option( 'externallinksnewwindow_withicon', $data['external_links_icon'] );
        
        $this->init_options();
        
        return true;
    }
    
    /**
     * Convert old seralised options into individual option keys and autoload
     */
    public function convert_options() {
        $legacy_options = $this->get_option( 'thisismyurl_externallinks' );
        $new_options = $this->get_option( 'externallinksnewwindow_enabled' );
        if ( !$new_options && $legacy_options ) {
            $legacy_options = json_decode( $legacy_options );
            $this->add_option( 'externallinksnewwindow_enabled', $legacy_options[0], true );
            $this->add_option( 'externallinksnewwindow_nofollow', $legacy_options[1], true );
            $this->add_option( 'externallinksnewwindow_withicon', $legacy_options[2], true );
            $this->delete_option( 'thisismyurl_externallinks' );
        }
	}
    
    public function plugin_style() {
        if ( $this->options['icon'] ) {
        ?>
            <style type="text/css">
                .thisismyurl_external,
                .external-links-new-window {
                    background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAOVBMVEX////39/fa2trW1tbFxcW9vb2xsbGpqamUlJSIiIiEhIRzc3Nra2tnZ2daWlpSUlJKSkpGRkb///9eei67AAAAE3RSTlP///////////////////////8Asn3cCAAAAEtJREFUCNcdy0kCwCAIBMFJwrgEQcz/Hxu0T3VpfNmIiOnYtEK2eVgVkIBmxsNVHieoJRDSX9CqDSxe43XNA0uAfh/ORko59FjZ5g/2GQR2Q86/9gAAAABJRU5ErkJggg==) no-repeat right;
                    padding-right: 14px;
                }
            </style>
        <?php
        }
    }
    
    public function admin_style() {
        ?>
        <style type="text/css">
            table.form-table fieldset {
                margin-top: 4px;
            }
            table.form-table fieldset:first-child {
                margin-top: 0;
            }
            table.form-table fieldset p.description {
                margin-bottom: 8px;
            }
            .external-links-new-window {
                background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAOVBMVEX////39/fa2trW1tbFxcW9vb2xsbGpqamUlJSIiIiEhIRzc3Nra2tnZ2daWlpSUlJKSkpGRkb///9eei67AAAAE3RSTlP///////////////////////8Asn3cCAAAAEtJREFUCNcdy0kCwCAIBMFJwrgEQcz/Hxu0T3VpfNmIiOnYtEK2eVgVkIBmxsNVHieoJRDSX9CqDSxe43XNA0uAfh/ORko59FjZ5g/2GQR2Q86/9gAAAABJRU5ErkJggg==) no-repeat right;
                padding-right: 14px;
            }
        </style>
        <?php
    }
    
    public function parse_the_content( $content ) {
        if ( $this->is_plugin_enabled ) {
            return $this->transform_html( $content );
        }
        return $content;
    }
    
    public function transform_html( $html ) {
        $html = str_get_html( $html );
        $anchors = $html->find( 'a' );
        foreach( $anchors as $a ) {
            $href = strtolower( $a->href );

            if ( stripos( $href, get_bloginfo('url') ) === false && substr($href, 0, 4) == 'http' ) {
                $a->target = '_blank';
                if ( $this->options['nofollow'] ) {
                    $a->rel = 'nofollow';
                }
                if ( $this->options['icon'] ) {
                    $a->class = 'thisismyurl_external external-links-new-window';
                }
            }
        }
        return $html->save();
    }
    
    public function admin_menu() {
        return add_options_page( 'External Links', 'External Links', 'edit_posts', 'external_links_new_window', array( $this, 'admin_page' ) );
    }
    
    public function admin_page() {
        $options_saved = null;
        
        if ( $_POST && $_POST['external_links_save'] ) {
            
            check_admin_referer( 'external-links-save' );
            
            $options_saved = $this->set_options( $_POST );
        }
        
        $this->render_admin_page( $options_saved );
    }
    
    public function render_admin_page( $options_saved = null ) {
        $enabled_checked = ( $this->is_plugin_enabled )? ' checked="checked"':'';
        $disabled_attribute = ( !$enabled_checked )? ' disabled="true"':'';
        $nofollow_checked = ( $this->options['nofollow'] )? ' checked="checked"':'';
        $icon_checked = ( $this->options['icon'] )? ' checked="checked"':'';
        ?>
        <?php if ( $options_saved === true ): ?>
            <div class="notice notice-success">
                <p><?php _e( 'Your settings have been saved.', 'external-links-new-window' ); ?></p>
            </div>	
        <?php elseif ( $options_saved === false ): ?>
            <div class="notice notice-warning">
                <p><?php _e( 'There was a problem saving your settings. Please try again.', 'external-links-new-window' ); ?></p>
            </div>	
        <?php endif; ?>
        <div class="wrap">
            <h2><?php _e( 'External Links to New Window','external-links-new-window' ); ?></h2>
            <div class="postbox-container">
                <form method="post" action="">
                    <input type="hidden" name="action" value="update" /> 
                    <h3><label for="externalLinksEnable"><input<?php echo $enabled_checked; ?> id="externalLinksEnable" name="external_links_enabled" type="checkbox" value="1"> Enable/Disable</label></h3>
                    <p><?php _e( 'Enable to open all external links in a new window','external-links-new-window' ); ?></p>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th scope="row"><?php _e( 'External link settings','external-links-new-window' ); ?></th>
                                <td>
                                    <fieldset>
                                        <label for="externalLinksNofollow"><input<?php echo $nofollow_checked . $disabled_attribute; ?> id="externalLinksNofollow" name="external_links_nofollow" type="checkbox" value="1"> <?php _e( 'Add rel="nofollow" attribute','external-links-new-window' ); ?></label>
                                        <p class="description"><a href="https://en.wikipedia.org/wiki/Nofollow" target="_blank" class="thisismyurl_external"><?php _e( 'Read more about rel="nofollow" on Wikipedia','external-links-new-window' ); ?></a></p>
                                    </fieldset>
                                    <fieldset>
                                        <label for="externalLinksIcon"><input<?php echo $icon_checked . $disabled_attribute; ?> id="externalLinksIcon" name="external_links_icon" type="checkbox" value="1"> <?php _e( 'Include an icon after link','external-links-new-window' ); ?></label>
                                        <p class="description"><a href="#" target="_blank" rel="nofollow" class="external-links-new-window"><?php _e( 'This is an example link with icon','external-links-new-window' ); ?></a></p>
                                    </fieldset>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="submit">
                        <input name="external_links_save" class="button-primary external-links-save-button" type="submit" value="Save changes">
						<?php wp_nonce_field( 'external-links-save' ); ?>
                    </p>
                </form>
            </div>
        </div>
        <script>
        jQuery( '#externalLinksEnable' ).on( 'click', function() {
            if ( jQuery( this ).is( ':checked' ) ) {
                jQuery( '#externalLinksNofollow' ).prop( 'disabled', false );
                jQuery( '#externalLinksIcon' ).prop( 'disabled', false );
            } else {
                jQuery( '#externalLinksNofollow' ).prop( 'disabled', true );
                jQuery( '#externalLinksIcon' ).prop( 'disabled', true );
            }
        });
        </script>
        <?php
    }
}

endif;

function external_links_new_window() {
	return ExternalLinksNewWindow::instance();
}

// Global for backwards compatibility.
$GLOBALS['external_links_new_window'] = external_links_new_window();