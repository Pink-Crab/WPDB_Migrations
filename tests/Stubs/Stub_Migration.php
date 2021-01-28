<?php

declare(strict_types=1);

/**
 * Base test case.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests\Stubs;

use PinkCrab\Table_Builder\Table_Schema;
use PinkCrab\DB_Migration\Database_Migration;

class Stub_Migration extends Database_Migration {

	public function set_schema(): void {
		$this->schema = Table_Schema::create( 'test_table' )
			->column( 'id' )
				->type( 'int' )
				->length( 11 )
				->auto_increment()

			->column( 'user' )
				->type( 'tinytext' )
				->default( '' )

			->column( 'filters' )
				->type( 'text' )
				->default( '' )
			->column( 'date_created' )
				->type( 'DATETIME' )
				->nullable( false )

			->column( 'last_update' )
				->type( 'DATETIME' )
				->nullable( false )
			->primary( 'id' );
	}

	/**
	 * Creates mock entires
	 *
	 * @return void
	 */
	public function post_up(): void {
		$this->wpdb->insert(
			'test_table',
			array(
				'user'         => 'alpha',
				'filters'      => 'bravo',
				'date_created' => date( 'Y-m-d H:i:s', time() ),
				'last_update'  => date( 'Y-m-d H:i:s', time() ),
			),
			array( '%s', '%s', '%s', '%s' )
		);
	}

	/**
	 * Called to changed the schema for update test.
	 *
	 * @return void
	 */
	public function change_schema() {
		$this->schema = Table_Schema::create( 'test_table' )
			->column( 'id' )
				->type( 'int' )
				->length( 11 )
				->auto_increment()
			->primary( 'id' )

			->column( 'username' )
				->type( 'tinytext' )
				->default( '' )

			->column( 'foo' )
				->type( 'text' )
				->default( '' )

			->column( 'date_created' )
				->type( 'DATETIME' )
				->nullable( false )

			->column( 'last_update' )
				->type( 'DATETIME' )
				->nullable( false );
	}

	/**
	 * Gives access to the schema object for manipulating.
	 *
	 * @param callable $function
	 * @return void
	 */
	public function edit_schema( callable $function ): void {
		$this->schema = $function( $this->schema );
	}


}
