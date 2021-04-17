<?php

declare(strict_types=1);

/**
 * Holds a log of the last table constructed
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

use stdClass;
use Exception;
use PinkCrab\Table_Builder\Schema;

class Migration_log {

    /**
     * The key used to hold all migration dates
     * Can be shared between multiple plugins.
     *
     * @var string
     */
    protected $option_key;

    /**
     * Current migration details
     *
     * @var array<stdClass>
     */
    protected $migration_details = [];

    public function __construct(string $option_key = null) {
        $this->option_key = $option_key ?? 'pink_migration_log';
    }

    /**
     * Sets the migration details held in optiosn
     *
     * @return void
     */
    public function set_migration_details(): void
    {
        $migrations = get_option($this->migration_details);

        if($migrations === false){
            return;
        }

        try {
            $migrations = json_decode($migrations);
        } catch (\Throwable $th) {
            throw new Exception("Migration details as JSON from options, failed to be decoded: " . $th->getMessage());
        }

        $this->migration_details = $migrations;
    }

    /**
     * Checks if a table has been migrated
     *
     * @param string $table_name
     * @return boolean
     */
    public function has_migration(string $table_name): bool
    {
        return array_key_exists($table_name, $this->migration_details);
    }

    /**
     * Maps a schema into a migration details object.
     *
     * @param Schema $scema
     * @return stdClass
     */
    protected function create_migration(Schema $scema): stdClass
    {
        return new stdClass($table_name, $column_hash){
            public $table_name;
            public $column_hash;
            public $created;
            public $last_updated;

            public function __construct(s $var = null) {
                $this->var = $var;
            }
        };
    }




}