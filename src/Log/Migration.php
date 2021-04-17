<?php

declare(strict_types=1);

/**
 * Model for migration log.
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

namespace PinkCrab\DB_Migration\Log;

use DateTime;
use JsonSerializable;
use DateTimeImmutable;

class Log implements JsonSerializable {

    
    /**
     * The table name
     *
     * @var string
     */
    protected $table_name;

    /**
     * Has of the table columns
     *
     * @var string
     */
    protected $column_hash;

    /**
     * Date the migration was created on    
     *
     * @var DateTimeImmutable
     */
    protected $created_on;

    /**
     * Date the migration was last updated
     *
     * @var DateTimeImmutable
     */
    protected $updated_on;

    public function __construct(string $table_name, string $hash, ) {
        $this->var = $var;
    }


    public function jsonSerialize()
    {
        return (object)[
            'table_name' => $this->table_name,
            'column_hash' => $this->column_hash,
            'created_on' => $this->created_on->format(DateTime::ISO8601),
            'updated_on' => $this->updated_on->format(DateTime::ISO8601),
        ];
    }


}