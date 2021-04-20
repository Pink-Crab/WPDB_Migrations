<?php

declare(strict_types=1);

/**
 * Mock Migration Bar
 * 
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests\Stubs;

use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Database_Migration;

class Stub_Migration_Bar extends Database_Migration {

	protected $table_name = 'uu_bar_table';

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'bar' )->text();
	}
}
