<?php

declare(strict_types=1);
/**
 * An abstract class for defninig migrations
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Glynn Quelch <glynn.quelch@gmail.com>
 * @license http://www.opensource.org/licenses/mit-license.html  MIT License
 * @package PinkCrab\DB_Migration
 */

namespace PinkCrab\DB_Migration;

use Exception;
use PinkCrab\Table_Builder\Schema;

abstract class Database_Migration {

	/**
	 * The tables schema.
	 *
	 * @var Schema
	 */
	protected $schema;

	/**
	 * The data to be seeded
	 *
	 * @var array<array<string, mixed>>
	 */
	protected $seed_data;

	/**
	 * The tables name
	 *
	 * @var string
	 */
	protected $table_name = '';

	/**
	 * @throws Exception If table name not defiend.
	 */
	public function __construct() {
		$this->schema    = new Schema( $this->table_name, array( $this, 'schema' ) );
		$this->seed_data = $this->seed( array() );
	}

	/**
	 * Defines the schema for the migration.
	 *
	 * @param Schema $schema_config
	 * @return void
	 */
	abstract public function schema( Schema $schema_config ): void;

	/**
	 * Defines the data to be seeded.
	 *
	 * @param array<string, mixed> $seeds
	 * @return array<string, mixed>
	 */
	public function seed( array $seeds ): array {
		return $seeds;
	}

	/**
	 * Returns the internal schema.
	 *
	 * @return Schema
	 */
	public function get_schema(): Schema {
		return $this->schema;
	}

	/**
	 * Returns the current seed data.
	 *
	 * @return array<string, mixed>
	 */
	public function get_seeds(): array {
		return $this->seed_data;
	}

	/**
	 * Returns the definied table name.
	 *
	 * @return string
	 */
	public function get_table_name(): string {
		return $this->table_name;
	}

}
