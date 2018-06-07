<?php
/**
 * Class ExternalLinksNewWindow_Test
 *
 * @package External_Links_To_New_Window
 */

/**
 * Sample test case.
 */
class ExternalLinksNewWindow_Test extends WP_UnitTestCase {

    /** 
     * Run before each test
     */
    public function setUp() {
        parent::setUp();
        $this->class_instance = new ExternalLinksNewWindow();
    }
    
	/**
	 * Testing anchors are correctly modified.
	 */
	function test_anchors() {
        $actual = $this->class_instance->transform_html( '<p><a href="http://www.google.com">External Link</a></p>', [ 'on', 'on', 'on' ] );
        $expected = '<p><a href="http://www.google.com" target="_blank" rel="nofollow" class="thisismyurl_external">External Link</a></p>';
        $this->assertEquals( $expected, $actual );
    }
}
