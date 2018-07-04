<?php
/*
Plugin Name: External Links to New Window
Plugin URI: https://etalented.co.uk/wordpress-plugin-external-links-to-a-new-window
Description: Open all external links in your blog posts and pages automatically in a new tab or new window when clicked or tapped.
Author: etalented
Author URI: https://etalented.co.uk/
Version: 2.0.4
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
    
	public $version = '2.0.4';
    
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
    
    public function admin_style() {
        ?>
        <style type="text/css">
            #externalLinksNewWindow>h2:first-child {
                background-image: url(https://ps.w.org/external-links-to-new-window/assets/icon-128x128.png);
                background-repeat: no-repeat;
                background-size: 42px;
                background-position: 0px 2px;
                padding-left: 50px;
            }
            #externalLinksNewWindow table.form-table fieldset {
                margin-top: 4px;
            }
            #externalLinksNewWindow table.form-table fieldset:first-child {
                margin-top: 0;
            }
            #externalLinksNewWindow table.form-table fieldset p.description {
                margin-bottom: 8px;
            }
            #externalLinksNewWindow .external-links-new-window {
                background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAMAAAC67D+PAAAAOVBMVEX////39/fa2trW1tbFxcW9vb2xsbGpqamUlJSIiIiEhIRzc3Nra2tnZ2daWlpSUlJKSkpGRkb///9eei67AAAAE3RSTlP///////////////////////8Asn3cCAAAAEtJREFUCNcdy0kCwCAIBMFJwrgEQcz/Hxu0T3VpfNmIiOnYtEK2eVgVkIBmxsNVHieoJRDSX9CqDSxe43XNA0uAfh/ORko59FjZ5g/2GQR2Q86/9gAAAABJRU5ErkJggg==) no-repeat right;
                padding-right: 14px;
            }
            #externalLinksNewWindow #poststuff #post-body.columns-2 {
                margin-right: 320px;
            }
            #externalLinksNewWindow #post-body.columns-2 #postbox-container-1 {
                float: right;
                margin-right: -320px;
                width: 300px;
            }
            #externalLinksNewWindow .box {
                margin-top: 20px;
                padding: .7em 1.5em 1em;
                border: 1px solid #e5e5e5;
                box-shadow: 0 1px 1px rgba(0,0,0,.04);
                background: #fff;
            }
            #externalLinksNewWindow #poststuff .box h2 {
                padding-left: 0;
                padding-right: 0;
            }
            #externalLinksNewWindow .rate {
                display: inline-block;
                top: -2px;
                position: relative;
                margin-left: 5px;
            }
            #externalLinksNewWindow .rating-stars a {
                text-decoration: none;
            }
            #externalLinksNewWindow .box.rating h2 {
                display: inline-block;
            }
            #externalLinksNewWindow .box.github img {
                height: 18px;
            }
            #externalLinksNewWindow .byline {
                text-align: center;
                margin-top: 2em;
            }
        </style>
        <?php
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
        <div id="externalLinksNewWindow" class="wrap">
            <h2><?php _e( 'External Links to New Window','external-links-new-window' ); ?></h2>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="update" /> 
                            <h3><label for="externalLinksEnable"><input<?php echo $enabled_checked; ?> id="externalLinksEnable" name="external_links_enabled" type="checkbox" value="1"> Enable/Disable</label></h3>
                            <p><?php _e( '<strong>Enable</strong> to open all external links in a new window or tab for all blog post and page content.','external-links-new-window' ); ?> <?php echo sprintf( _e( '<a href="$s" target="_blank">Here is an example</a> of how it will work.','external-links-new-window' ), 'https://etalented.co.uk' ); ?></p>
                            <p><?php _e( 'The CSS class <code>thisismyurl_external</code> (legacy) and <code>external-links-new-window</code> will be applied to external links so that you can use custom CSS for bespoke styling.' ); ?></p>
                            <table class="form-table">
                                <tbody>
                                    <tr>
                                        <th scope="row"><?php _e( 'Settings for external links','external-links-new-window' ); ?></th>
                                        <td>
                                            <fieldset>
                                                <label for="externalLinksNofollow"><input<?php echo $nofollow_checked . $disabled_attribute; ?> id="externalLinksNofollow" name="external_links_nofollow" type="checkbox" value="1"> <?php _e( 'Add <code>rel="nofollow"</code> attribute','external-links-new-window' ); ?></label>
                                                <p class="description"><?php _e( 'This stops search engines visting and indexing the external link.','external-links-new-window' ); ?><br><a href="https://en.wikipedia.org/wiki/Nofollow" target="_blank" rel="nofollow" class="external-links-new-window"><?php _e( 'Read more about rel="nofollow" on Wikipedia','external-links-new-window' ); ?></a></p>
                                            </fieldset>
                                            <fieldset>
                                                <label for="externalLinksIcon"><input<?php echo $icon_checked . $disabled_attribute; ?> id="externalLinksIcon" name="external_links_icon" type="checkbox" value="1"> <?php _e( 'Include an icon after the link','external-links-new-window' ); ?></label>
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
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="box rating">
                            <h2 class="title"><?php _e( 'What\'s Your Rating?','external-links-new-window' ); ?></h2>
                            <div class="rate">
                                <div class="rating-stars">
                                    <a data-rating="1" target="_blank" href="//wordpress.org/support/plugin/external-links-to-new-window/reviews/?rate=1#new-post" title="Poor"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="2" target="_blank" href="//wordpress.org/support/plugin/external-links-to-new-window/reviews/?rate=2#new-post" title="Works"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="3" target="_blank" href="//wordpress.org/support/plugin/external-links-to-new-window/reviews/?rate=3#new-post" title="Good"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="4" target="_blank" href="//wordpress.org/support/plugin/external-links-to-new-window/reviews/?rate=4#new-post" title="Great"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a><a data-rating="5" target="_blank" href="//wordpress.org/support/plugin/external-links-to-new-window/reviews/?rate=5#new-post" title="Fantastic!"><span class="dashicons dashicons-star-empty" style="color:#ffb900 !important;"></span></a>
                                </div>
                            </div>
                            <p><?php echo sprintf( __( 'If you could spare <a href="%s" target="_blank" rel="nofollow" class="external-links-new-window">30 seconds to rate this plugin</a>, that would be fantastic!','external-links-new-window' ), 'https://wordpress.org/support/plugin/external-links-to-new-window/reviews?rate=5#new-post' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need Help?','external-links-new-window' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow" class="external-links-new-window">Read the FAQ on WordPress.org</a> to see if your question is answered.','external-links-new-window' ), 'https://wordpress.org/plugins/external-links-to-new-window/#faq' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need Support?','external-links-new-window' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow" class="external-links-new-window">Visit the WordPress.org Support Forum</a> to read the existing topics or  create a new topic to get support.','external-links-new-window' ), 'https://wordpress.org/support/plugin/external-links-to-new-window/' ); ?></p>
                        </div>
                        <div class="box">
                            <h2 class="title"><?php _e( 'Need a Feature?','external-links-new-window' ); ?></h2>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow" class="external-links-new-window">Request a new feature on the WordPress.org Support Forum</a>','external-links-new-window' ), 'https://wordpress.org/support/plugin/external-links-to-new-window/' ); ?></p>
                        </div>
                        <div class="box github">
                            <h2 class="title"><?php _e( 'Want to Contribute?','external-links-new-window' ); ?></h2>
                            <p><a href="https://github.com/etalented/external-links-to-new-window" target="_blank"><img src="https://assets-cdn.github.com/images/modules/logos_page/GitHub-Logo.png" alt=""></a></p>
                            <p><?php echo sprintf( __( '<a href="%s" target="_blank" rel="nofollow" class="external-links-new-window">The entire codebase is on GitHub</a>, so please feel free to make a pull request.','external-links-new-window' ), 'https://github.com/etalented/external-links-to-new-window' ); ?></p>
                        </div>
                        <p class="byline"><script src="https://etalented.co.uk/bl/bl.js"></script></p>
                    </div>
                </div>
            </div>
        </div>
        <script>
        jQuery( document ).ready( function( $ ) {
            $( '#externalLinksEnable' ).on( 'click', function() {
                if ( jQuery( this ).is( ':checked' ) ) {
                    jQuery( '#externalLinksNofollow' ).prop( 'disabled', false );
                    jQuery( '#externalLinksIcon' ).prop( 'disabled', false );
                } else {
                    jQuery( '#externalLinksNofollow' ).prop( 'disabled', true );
                    jQuery( '#externalLinksIcon' ).prop( 'disabled', true );
                }
            });
            $( '.rating-stars' ).find( 'a' ).on( 'hover', function() {
                $( this ).nextAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-filled' ).addClass( 'dashicons-star-empty' );
                $( this ).prevAll( 'a' ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
                $( this ).children( 'span' ).removeClass( 'dashicons-star-empty' ).addClass( 'dashicons-star-filled' );
            });
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