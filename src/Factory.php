<?php

declare(strict_types=1);

/**
 * Factory for a quick and clean setup.
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

use wpdb;
use PinkCrab\Table_Builder\Builder;
use PinkCrab\DB_Migration\Log\Migration_Log_Manager;
use PinkCrab\Table_Builder\Engines\WPDB_DB_Delta\DB_Delta_Engine;

class Factory {

	/**
	 * Creates an instace of the manager using wpdb & DB_Delta builder.
	 *
	 * @param string|null $option_key If no key passed, will use the detault defined in Migration_Log_Manager
	 * @param \wpdb|null $wpdb Can pass custom wpdb instance
	 * @return Migration_Manager
	 */
	public static function manager_with_db_delta( ?string $option_key = null, ?\wpdb $wpdb = null ): Migration_Manager {
		if ( $wpdb === null ) {
			global $wpdb;
		}

		$builder = new Builder( new DB_Delta_Engine( $wpdb ) );
		return new Migration_Manager( $builder, $wpdb, $option_key );
	}

	/**
	 * Returns an instance of the Log Manager with a defined key.
	 *
	 * If no key passed, will use the detault defined in Migration_Log_Manager
	 *
	 * @param string|null $option_key
	 * @return \PinkCrab\DB_Migration\Log\Migration_Log_Manager
	 */
	public static function migration_log( ?string $option_key = null ): Migration_Log_Manager {
		return new Migration_Log_Manager( $option_key );
	}
}
