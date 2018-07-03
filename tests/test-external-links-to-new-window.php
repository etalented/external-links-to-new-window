<?php
/**
 * Class WP_UnitTestCase_ExternalLinksNewWindow
 *
 * @package WP_UnitTestCase_ExternalLinksNewWindow
 */

class ExternalLinksNewWindow_UnitText extends ExternalLinksNewWindow {
    
    private $saved_options; 
    
    public function get_option( $name ) {
        if ( isset( $this->saved_options[ $name ] ) ) {
            return $this->saved_options[ $name ];
        }
        return false;
    }
    
    public function add_option( $name, $value ) {
        $this->saved_options[ $name ] = $value;
        return true;
    }
    
    public function update_option( $name, $value ) {
        $this->saved_options[ $name ] = $value;
        return true;
    }
}

/**
 * Sample test case.
 */
class WP_UnitTestCase_ExternalLinksNewWindow extends WP_UnitTestCase {

    /** 
     * Run before each test
     */
    public function setUp() {
        $this->class_instance = new ExternalLinksNewWindow_UnitText();
        parent::setUp();
    }
    
	/**
	 * Test HTML is correctly modified.
	 */
	function test_all_options_on() {
        $this->class_instance->set_options( [
            'external_links_enabled' => 1,
            'external_links_nofollow' => 1,
            'external_links_icon' => 1,
        ] );
        $actual = $this->class_instance->transform_html( '<p><a href="http://www.google.com">External Link</a></p>' );
        $expected = '<p><a href="http://www.google.com" target="_blank" rel="nofollow" class="thisismyurl_external external-links-new-window">External Link</a></p>';
        $this->assertEquals( $expected, $actual );
    }
    
	/**
	 * Test options are correctly saved.
	 */
	function test_set_options() {
        $this->class_instance->set_options( [
            'external_links_enabled' => 1,
            'external_links_nofollow' => 0,
            'external_links_icon' => 0,
        ] );
        $expected = 1;
        $actual = $this->class_instance->get_option( 'externallinksnewwindow_enabled' );
        $this->assertEquals( $expected, $actual );
    }
    
	/**
	 * Test converting legacy options to new options.
	 */
	function test_legacy_options_conversion() {
        $this->class_instance->add_option( 'thisismyurl_externallinks', '[1,1,1]' );
        $this->class_instance->convert_options();
        
        // Enabled option
        $expected = 1;
        $actual = $this->class_instance->get_option( 'externallinksnewwindow_enabled' );
        $this->assertEquals( $expected, $actual );
        
        // Nofollow option
        $expected = 1;
        $actual = $this->class_instance->get_option( 'externallinksnewwindow_nofollow' );
        $this->assertEquals( $expected, $actual );
        
        // Withicon option
        $expected = 1;
        $actual = $this->class_instance->get_option( 'externallinksnewwindow_withicon' );
        $this->assertEquals( $expected, $actual );
    }
    
	/**
	 * Test enabling/disabling of plugin.
	 */
	function test_plugin_enable_disable() {
        // Disabled by default
        $expected = '<p><a href="http://www.google.com">External Link</a></p>';
        $actual = $this->class_instance->parse_the_content( '<p><a href="http://www.google.com">External Link</a></p>' );
        $this->assertEquals( $expected, $actual );
        
        // Enable plugin (without setting any options)
        $this->class_instance->add_option( 'externallinksnewwindow_enabled', 1 );
        $expected = '<p><a href="http://www.google.com" class="thisismyurl_external external-links-new-window">External Link</a></p>';
        $actual = $this->class_instance->parse_the_content( '<p><a href="http://www.google.com" class="thisismyurl_external external-links-new-window">External Link</a></p>' );
        $this->assertEquals( $expected, $actual );
        
        // Disable plugin
        $this->class_instance->add_option( 'externallinksnewwindow_enabled', 0 );
        $expected = '<p><a href="http://www.google.com">External Link</a></p>';
        $actual = $this->class_instance->parse_the_content( '<p><a href="http://www.google.com">External Link</a></p>' );
        $this->assertEquals( $expected, $actual );
    }
    
	/**
	 * Test showing admin page settings save success and failure message.
	 */
	function test_admin_settings_page_messages() {
        // Success message
        ob_start();
        $this->class_instance->render_admin_page( true );
        $output = ob_get_contents();
        ob_end_clean();
        $result = strpos( $output, '<p>Your settings have been saved.</p>' ) > 0;
        $this->assertTrue( $result );
        $result = strpos( $output, '<p>There was a problem saving your settings. Please try again.</p>' ) > 0;
        $this->assertFalse( $result );
                          
        // Failure message
        ob_start();
        $this->class_instance->render_admin_page( false );
        $output = ob_get_contents();
        ob_end_clean();
        $result = strpos( $output, '<p>Your settings have been saved.</p>' ) > 0;
        $this->assertFalse( $result );
        $result = strpos( $output, '<p>There was a problem saving your settings. Please try again.</p>' ) > 0;
        $this->assertTrue( $result );
    }
    
	/**
	 * Test settings are disabled by default on admin settings page.
	 */
	function test_plugin_is_disabled_by_default_on_admin_page() {
        ob_start();
        $this->class_instance->render_admin_page();
        $output = ob_get_contents();
        ob_end_clean();
        
        // Nofollow option
        $result = strpos( $output, '<input disabled="true" id="externalLinksNofollow" name="external_links_nofollow" type="checkbox" value="1">' ) > 0;
        $this->assertTrue( $result );
        
        // Withicon option
        $result = strpos( $output, '<input disabled="true" id="externalLinksIcon" name="external_links_icon" type="checkbox" value="1">' ) > 0;
        $this->assertTrue( $result );
    }
    
	/**
	 * Test settings are disabled by default on admin settings page.
	 */
	function test_enabling_plugin_enables_settings() {
        $this->class_instance->add_option( 'externallinksnewwindow_enabled', 1 );
        $this->class_instance->init_options();
        ob_start();
        $this->class_instance->render_admin_page();
        $output = ob_get_contents();
        ob_end_clean();
        
        // Nofollow option
        $result = strpos( $output, '<input id="externalLinksNofollow" name="external_links_nofollow" type="checkbox" value="1">' ) > 0;
        $this->assertTrue( $result );
        
        // Withicon option
        $result = strpos( $output, '<input id="externalLinksIcon" name="external_links_icon" type="checkbox" value="1">' ) > 0;
        $this->assertTrue( $result );
    }
}
