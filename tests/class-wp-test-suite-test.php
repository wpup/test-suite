<?php

/**
 * Test to test so all works as it should.
 */
class WP_Test_Suite_Test extends WP_UnitTestCase {

	public function test_is_admin_exists() {
		$this->assertTrue( function_exists( 'is_admin' ) );
	}

}
