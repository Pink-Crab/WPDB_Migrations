<?php

declare(strict_types=1);

/**
 * Base test case.
 * @author GLynn Quelch <glynn.quelch@gmail.com>
 */

namespace PinkCrab\DB_Migration\Tests\Stubs;

use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Database_Migration;

class Stub_Migration_Foo extends Database_Migration {

	protected $table_name = 'uu_foo_table';

	public function schema( Schema $schema_config ): void {
		$schema_config->column( 'foo' )->text();
		$schema_config->column( 'number' )->int();
	}

	public function seed( array $seeds ): array {
		$seeds[] = array(
			'foo'    => 'text1',
			'number' => 1,
		);
		$seeds[] = array(
			'foo'    => 'text2',
			'number' => 2,
		);

		return $seeds;
	}


}
