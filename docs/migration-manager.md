Manager for handling and processing migrations

# Public Methods

> ## __construct( Builder $builder, wpdb $wpdb, ?string $migration_log_key = null )
> `@param  PinkCrab\Table_Builder\Builder  $builder  Instance of Table Builder`  
> `@param  wpdb  $wpdb  Valid instance of WPDB`  
> `@param  string|null  $migration_log_key  Migration log key`

Creates an instance of the migration manager

```php
$migrations = new PinkCrab\DB_Migration\Migration_Manager($builder, $wpdb, 'acme_migrations');
// Its also possible to use the Factory
$migrations = PinkCrab\DB_Migration\Factory::migration_log('acme_migrations', $wpdb);
```

> See [Log Manager](log-manager.md) for details about `$migration_log_key`

***

> ## add_migration( Database_Migration $migration ): Migration_Manager  
> `@param  PinkCrab\DB_Migration\Database_Migration Instance of a Database Migration`  
> `@return  PinkCrab\DB_Migration\Migration_Manager`

Adds a Migration to the manager

```php
$migrations = new Migration_Manager($builder, $wpdb, 'acme_migrations');

$migration_foo = new Migration_Foo();
$migration_bar = new Migration_Bar();

$migrations->add_migration($migration_foo);
$migrations->add_migration($migration_bar);
```

***

> ## get_migrations(): array  
> `@return  PinkCrab\DB_Migration\Database_Migration[]`

Gets all current migrations added to the manager

```php
$migrations = new Migration_Manager($builder, $wpdb, 'acme_migrations');

$migration_foo = new Migration_Foo();
$migration_bar = new Migration_Bar();
$migrations->add_migration($migration_foo);
$migrations->add_migration($migration_bar);

dump($migrations->get_migrations()); // [Migration_Foo{}, Migration_Bar{}]
```

***

> ## create_tables( string ...$excluded_table ): Migration_Manager  
> `@param string ...$exclude_table Tables to be skipped`
> `@return  PinkCrab\DB_Migration\Migration_Manager`

Create/Updates all tables, excluding any table names passed. . Once created, a record is made in the log.

```php
$migrations = new Migration_Manager($builder, $wpdb, 'acme_migrations');

$migration_foo = new Migration_Foo();  // Table name = foo_table
$migration_bar = new Migration_Bar();  // Table name = bar_table
$migrations->add_migration($migration_foo);
$migrations->add_migration($migration_bar);

$migrations->create_tables('bar_table');              // Would only create foo_table
$migrations->create_tables('bar_table', 'foo_table'); // Would create no tables.
```

***

> ## seed_tables( string ...$excluded_table ): Migration_Manager  
> `@param string ...$exclude_table Tables to be skipped`
> `@return  PinkCrab\DB_Migration\Migration_Manager`

Seeds all data to the tables, if not already seeded previously. Once seeded, a record is made in the log.
> Please note a table can only be seeded once, even if the schema has changed!

```php
$migrations = new Migration_Manager($builder, $wpdb, 'acme_migrations');

$migration_foo = new Migration_Foo();  // Table name = foo_table
$migration_bar = new Migration_Bar();  // Table name = bar_table
$migrations->add_migration($migration_foo);
$migrations->add_migration($migration_bar);

$migrations->seed_tables('bar_table');              // Would only seed foo_table
$migrations->seed_tables('bar_table', 'foo_table'); // Would seed no tables.
```

***

> ## drop_tables( string ...$excluded_table ): Migration_Manager  
> `@param string ...$exclude_table Tables to be skipped`
> `@return  PinkCrab\DB_Migration\Migration_Manager`

Drops all data to the tables, if not already seeded previously. Once dropped, any log is removed, so this could be re-added/seeded later on..

```php
$migrations = new Migration_Manager($builder, $wpdb, 'acme_migrations');

$migration_foo = new Migration_Foo();  // Table name = foo_table
$migration_bar = new Migration_Bar();  // Table name = bar_table
$migrations->add_migration($migration_foo);
$migrations->add_migration($migration_bar);

$migrations->seed_tables('bar_table');              // Would only drop foo_table
$migrations->seed_tables('bar_table', 'foo_table'); // Would drop no tables.
```

***

> ## migration_log( ): Migration_Log_Manager   
> `@return PinkCrab\DB_Migration\Log\Migration_Log_Manager` 

Returns access to the [Log Manager](log-manager.md)