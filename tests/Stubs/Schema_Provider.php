<?php

declare(strict_types=1);

namespace PinkCrab\DB_Migration\Tests\Stubs;

use PinkCrab\Table_Builder\Schema;

class Schema_Provider {

	/**
	 * Returns the schema used for the migration log tests
	 *
	 * @return Schema
	 */
	public static function migration_log_schema(): Schema {
		return new Schema(
			'test_table',
			function( Schema $schema ): void {
				$schema->column( 'id' )
				->type( 'int' )
				->length( 11 )
				->auto_increment();

				$schema->column( 'user' )
				->type( 'text' )
				->default( '' );

				$schema->column( 'filters' )
				->type( 'text' )
				->default( '' );
				$schema->column( 'date_created' )
				->type( 'DATETIME' )
				->nullable( false );

				$schema->column( 'last_update' )
				->type( 'DATETIME' )
				->nullable( false );

				$schema->index( 'id' )->primary();
			}
		);
	}
}
