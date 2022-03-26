Manager for handling and processing migrations

# Public Methods

> ## __construct( Builder $builder, wpdb $wpdb, ?string $migration_log_key = null )
> `@param  PinkCrab\Table_Builder\Builder  $builder  Instance of Table Builder`  
> `@param  wpdb  $wpdb  Valid instance of WPDB`  
> `@param  string|null  $migration_log_key  Migration log key`

Creates an instance of the migration manager

```php
$log_manager = new PinkCrab\DB_Migration\Migration_Manager($builder, $wpdb, 'acme_migrations');
// Its also possible to use the Factory
$manager = PinkCrab\DB_Migration\Factory::migration_log('acme_migrations', $wpdb);
```

> See [Log Manager](log-manager.md) for details about `$migration_log_key`