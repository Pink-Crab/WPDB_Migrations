# WP_DB_Migration
System for creating database migrations with WordPress

![PinkCrab WP DB Migration Version 1.0.1](https://img.shields.io/badge/Current_Version-1.0.1-green.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)](https://github.com/ellerbrock/open-source-badge/)
![](https://github.com/Pink-Crab/WP_DB_Migration/workflows/PinkCrab_GitHub_CI/badge.svg " ")
[![codecov](https://codecov.io/gh/Pink-Crab/WP_DB_Migration/branch/master/graph/badge.svg)](https://codecov.io/gh/Pink-Crab/WP_DB_Migration)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Pink-Crab/WP_DB_Migration/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Pink-Crab/WP_DB_Migration/?branch=master)
 

***********************************************

## Requirements

Requires PinkCrab Table Builder, Composer and WordPress.

Uses the [WPDB Table Builder](https://github.com/Pink-Crab/WPDB-Table-Builder) library.

Works with PHP versions 7.1, 7.2, 7.3 & 7.4

> If you are using this with the [PinkCrab Perique framework](https://perique.info), please use the [Perique Migrations](https://github.com/Pink-Crab/Perique_Migrations) module.


## Installation

``` bash
$ composer require pinkcrab/wp-db-migrations
```

## Why

Creates a wrapper around the WPDB_Table_Builder to make it easier to create Migrations for use with WP plugins or themes. Allows for the creation and dropping of database tables and the seeding of initial data.

## How to use

You will need to create your Migrations using the ```Database_Migration``` abstract class. 

**[Read the Schema documentation](https://github.com/Pink-Crab/WPDB-Table-Builder/blob/master/docs/Schema.md)**

```php
<?php

use PinkCrab\Table_Builder\Schema;
use PinkCrab\DB_Migration\Database_Migration;

class Foo_Migration extends Database_Migration {

    // Define the tables name.
    protected $table_name = 'foo_table';

    // Define the tables schema
    public function schema( Schema $schema_config ): void {
        $schema_config->column('id')->unsigned_int(12)->auto_increment()
        $schema_config->index('id')->primary();

        $schema_config->column('column1')->text()->nullable();
        $schema_config->column('column2')->text()->nullable();
    }

    // Add all data to be seeded 
    public function seed( array $seeds ): array {
        $seeds[] = [
            'column1' => 'value1',
            'column2' => 'value2',
        ];

        $seeds[] = [
            'column1' => 'value1',
            'column2' => 'value2',
        ];
        
        return $seeds;
    }
}
```


Once you have your Migrations created it is a case of using the Migration_Manager to handle the creation, seeding and eventaul dropping of the table.

**[Read the Builder documentation](https://github.com/Pink-Crab/WPDB-Table-Builder)**

```php
<?php

global $wpdb; // You can access this however you please.

// See PinkCrab Table Builder for details about the Builder.
$builder = new Builder(...);

$manager = new Migration_Manager($builder, $wpdb, 'acme_migration_log_key');

// Add all migration to manager
$manager->add_migration(new Foo_Migration());

// Create tables
$manager->create_tables('some_table_to_skip');

// Insert all seed data
$manager->seed_tables('some_table_to_skip');

// Drop all tables
$manager->drop_tables('some_table_to_skip');
```
> It is suggested to wrap create_tables, seed_tables and drop_tables in a try/catch as they can throw exceptions.

## Factory

You can create an instance of both a Migration Manager and Migration Log.

### Factory::manager_with_db_delta(?string $option_key = null, ?wpdb $wpdb = null)
Can be used to create a manager set with a standard wpdb instance. A custom log option key can be defined and a custom wpdb instance can be used if you wish to use multiple databases.
```php
$manager = PinkCrab\DB_Migration\Factory::manager_with_db_delta('acme_migration_log_key', $custom_wpdb);
```

### Factory::migration_log(?string $option_key = null)
Creates an instance of the migration log, the option key used in the migration manager can be optionally passed if a custom value is set.
```php
$migration_log = PinkCrab\DB_Migration\Factory::migration_log('acme_migration_log_key');
```

## Migration Log
The Migration Manager has an internal log which is serialised and stored as a WP Option. This is used to ensure that tables are only updated when the schema has changed and that only a single seeding of each table can happen. 
If you need to access the Log, you can either call it from a Migration_Manager instance ```$manager->migration_log();``` or by creating an instance. 

```php
$log = new Migration_Log_Manager('custom_option_key');
```


## Exceptions

During the process, multiple exceptions can be thrown, these are all ```PinkCrab\DB_Migration\Migration_Exceptions``` 

### seed_column_doesnt_exist()
Thrown when trying get the column data from a schema, where the column doesn't exist.
> Message: *Could not find column {column name} in {table name} schema definition*
> 
> Error Code: 1

### failed_to_insert_seed()
Thrown when attempting to insert seed data, but wpdb returns an error.
> Message: *Could not insert seed into {table name}, failed with error {wpdb error}*
> 
> Error Code: 2

### failed_to_drop_table()
Thrown when wpdb produces an error removing a table.
> Message: *Failed to drop {table name}*
> 
> Error Code: 3


## Use With Plugins

The best way to use the Migration service is as part of your plugins activation/uninstall process. This would ensure that all tables are created and seeded when the plugin is activated and all tables are dropped when the plugin is uninstalled.

Thanks to the Migration_Log, tables will only be reprocessed if the schema has changed and data can only be seeded once. So if you plan to add seed data in for later versions of your plugin, they can be added when ready.

You can also hook the Migration_Manager into any custom plugin update systems. So long as you use the same Migration Log key (acme_plugin_migrations in example below), the log will be persisted in the options table.

```php
// file plugin.php

global $wpdb;
$engine  = new DB_Delta_Engine($wpdb); // https://github.com/Pink-Crab/WPDB-Table-Builder
$builder = new Builder($engine); // https://github.com/Pink-Crab/WPDB-Table-Builder

$migrations = new Migration_Manager( $builder, $wpdb, "acme_plugin_migrations");

// Add your migrations
$migrations->add_migration(new Some_Migration());
$migrations->add_migration(new Some_Other_Migration());

// Build and seed all tables using register_activation_hook
register_activation_hook( __FILE__, function() use ($migrations){
	// Create tables
	$migrations->create_tables();

	// Create seeds
	$migrations->seed_tables();

	// Register unistall action.
	register_uninstall_hook( __FILE__, function() use ($migrations){
		$migrations->drop_tables();
	});
	
});
```
Obviously this can be structured however you wish.

---
## Change log

* 1.0.1 - Allows access to the migration manager log key via `Migration_Log_Manager->get_log_key()` method
* 1.0.0 - Now supports[WPDB Table Builder](https://github.com/Pink-Crab/WPDB-Table-Builder/tree/1.0.0) 1.0.0
* 0.3.1 - Added Dependabot config
* 0.3.0 - Migrated from [WPDB Table Builder 0.2](https://github.com/Pink-Crab/WPDB-Table-Builder/tree/0.3.0) to [0.3](https://github.com/Pink-Crab/WPDB-Table-Builder/tree/1.0.0)
* 0.2.0 - Extracted from the (OLD) PinkCrab Framework v0.1.0 registerables package.
