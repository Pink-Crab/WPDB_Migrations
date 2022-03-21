<?php

declare(strict_types=1);

/**
 * Tests for the Factory
 *
 * @since 0.3.0
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests;

use WP_UnitTestCase;
use PinkCrab\DB_Migration\Factory;
use Gin0115\WPUnit_Helpers\Objects;

class Test_Factory extends WP_UnitTestCase {

	/** @testdox It should be possible to create Migration Manager instance using the global wpdb. */
	public function test_manager_with_gloabl_wpdb(): void {
		$manager = Factory::manager_with_db_delta();
		$this->assertSame( $GLOBALS['wpdb'], Objects::get_property( $manager, 'wpdb' ) );
	}

	/** @testdox It should be possible to create a Migration Manager using a custom instance of wpdb. */
	public function test_manager_with_custom_wpdb(): void {
		$mock_wpdb = $this->createMock( \wpdb::class );
		$manager   = Factory::manager_with_db_delta( 'with_mock_wpdb', $mock_wpdb );
		$this->assertSame( $mock_wpdb, Objects::get_property( $manager, 'wpdb' ) );
	}

	/** @testdox It should be possible to create a Migration Manager instance using the default option key. */
	public function test_manager_with_fallback_option_key(): void {
		$manager = Factory::manager_with_db_delta();
		$log     = $manager->migration_log();
		$this->assertSame(
			'pinkcrab_migration_log',
			Objects::get_property( $log, 'option_key' )
		);
	}

	/** @testdox It should be possible to create a Migration Manager instance using a custom option key. */
	public function test_manager_with_custom_option_key(): void {
		$manager = Factory::manager_with_db_delta( 'custom_option_key' );
		$log     = $manager->migration_log();
		$this->assertSame(
			'custom_option_key',
			Objects::get_property( $log, 'option_key' )
		);
	}

	/** @testdox It should be possible to create the Migration Log using the default option key. */
	public function test_log_with_default_option_key(): void {
		$log = Factory::migration_log();
		$this->assertSame(
			'pinkcrab_migration_log',
			Objects::get_property( $log, 'option_key' )
		);
	}

	/** @testdox It should be possible to create the Migration Log using the custom option key. */
	public function test_log_with_custom_option_key(): void {
		$log = Factory::migration_log( 'custom_option_key' );
		$this->assertSame(
			'custom_option_key',
			Objects::get_property( $log, 'option_key' )
		);
	}
}
