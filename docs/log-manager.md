The log manager handles the past migrations and seeding of tables. 

> All of the data is stored in the regular WP Options table (not auto-loaded).

# Public Methods

> ## __construct( string $option_key = null )
> `@param  string|null  $option_key  The option key to hold the log in options.`

```php
$log_manager = new PinkCrab\DB_Migration\Log\Migration_Log_Manager('acme_migrations');
// Its also possible to use the Factory
$manager = PinkCrab\DB_Migration\Factory::migration_log('acme_migrations');
```

If no key is passed, `pinkcrab_migration_log` is used as a fallback

> ## has_migration( Schema $schema ): bool
> `@param  PinkCrab\Table_Builder\Schema  $schema`   
> `@return bool`

Checks if a schema has been migrated or not yet.

```php
$log_manager = new Migration_Log_Manager('acme_migrations');

if(!$log_manager->has_migration($some_schema)){
    // Doesn't exist, do something!
}
```

> ## get_log_key(): string
> `@return string`

Gets the log manager option key.

```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$log_manager->get_log_key(); // acme_migrations
```

> ## get_migration( Schema $schema ): ?Migration_Log  
> `@param Schema $schema`  
> `@return PinkCrab\DB_Migration\Log\Migration_Log|null`  

Returns the Migration Log details for an existing schema, or null if not migrated.
```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$migration = $log_manager->get_migration($some_schema);

//  Log methods.
$migration->table_name();  // (string)            The table name.
$migration->schema_hash(); // (string)            The migrations unique hash.
$migration->updated_on();  // (DateTimeImmutable) Date the migration was last updated or created if not updated.
$migration->created_on();  // (DateTimeImmutable) Date the migration was created.
$migration->is_seeded();   // (bool)              If the table has been seeded.
```

> ## can_migrate( Schema $schema ): bool
> `@param  PinkCrab\Table_Builder\Schema  $schema`   
> `@return bool`

Checks if a schema can be migrated, or if it been migrated already (based on its hash)

```php
$log_manager = new Migration_Log_Manager('acme_migrations');

if($log_manager->can_migration($some_schema)){
    // do it
}
```

> ## upsert_migration( Schema $schema ): Migration_Log_Manager
> `@param  PinkCrab\Table_Builder\Schema  $schema`   
> `@return PinkCrab\DB_Migration\Log\Migration_Log_Manager`

Either creates a new record of a migration or updates the existing record.

```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$log_manager->upsert_migration($some_schema);
```

> ## remove_migration( Schema $schema ): Migration_Log_Manager
> `@param  PinkCrab\Table_Builder\Schema  $schema`   
> `@return PinkCrab\DB_Migration\Log\Migration_Log_Manager`

Removes a schema from the log. 

```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$log_manager->remove_migration($some_schema);

```
> ## mark_table_seeded( Schema $schema ): Migration_Log_Manager
> `@param  PinkCrab\Table_Builder\Schema  $schema`   
> `@return PinkCrab\DB_Migration\Log\Migration_Log_Manager`

Marks a table as seeded in the log.

```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$log_manager->mark_table_seeded($some_schema);

```

> ## clear_log(): void
> `@return void`

Clears all log/deletes the options entry

```php
$log_manager = new Migration_Log_Manager('acme_migrations');
$log_manager->clear_log(); 
```