<?php

declare(strict_types=1);

/**
 * Base test case.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\Hook_Subscriber\Tests;

use PHPUnit\Framework\TestCase;

class Test_Test extends TestCase {

	/**
	 * Test none deferred can accept 20 args if neeed.
	 *
	 * @return void
	 */
	public function test_test(): void {

		$this->assertTrue( 1 === 1 );
	}
}
