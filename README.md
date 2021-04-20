# WP_DB_Migration
Abstract class for handling database migrations in the PinkCrab plugin framework


![alt text](https://img.shields.io/badge/Current_Version-0.3.0-yellow.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

![](https://github.com/Pink-Crab/WP_DB_Migration/workflows/PinkCrab_GitHub_CI/badge.svg " ")
![alt text](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/WP_PHPUnit-V5-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/PHPCS-WP_Extra-brightgreen.svg?style=flat " ")

 

***********************************************

## Requirements

Requires PinkCrab Table Builder, Composer and WordPress.

Compatable with version 0.3.* of the [WPDB Table Builder](https://github.com/Pink-Crab/WPDB-Table-Builder)

Works with PHP versions 7.1, 7.2, 7.3 & 7.4


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
        $schema_config->column('column1')->text()->nullable();
        $schema_config->column('column2')->text()->nullable();
        
		$schema_config->index('id')->primary();
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

$manager = new Migration_Manager($builder, $wpdb, 'achme_migration_log_key');

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

## Exceptions

During the process, mutliple excptions can be thrown, these are all ```PinkCrab\DB_Migration\Migration_Exceptions``` 

### seed_column_doesnt_exist()
Thrown when trying get the column data from a schema, where the column doesnt exist.
> Messge: *Could not find column {column name} in {table name} schema definition*
> 
> Error Code: 1

### failed_to_insert_seed()
Thrown when attempting to insert seed data, but wpdb returns an error.
> Messge: *Could not insert seed into {table name}, failed with error {wpdb error}*
> 
> Error Code: 2