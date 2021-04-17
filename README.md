# WP_DB_Migration
Abstract class for handling database migrations in the PinkCrab plugin framework


![alt text](https://img.shields.io/badge/Current_Version-0.2.0-yellow.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

![](https://github.com/Pink-Crab/WP_DB_Migration/workflows/PinkCrab_GitHub_CI/badge.svg " ")
![alt text](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/WP_PHPUnit-V5-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/PHPCS-WP_Extra-brightgreen.svg?style=flat " ")

 

***********************************************

## Requirements

Requires PinkCrab Table Builder Composer and WordPress.

Compatable with version 0.3.* of the (WPDB Table Builder)[https://github.com/Pink-Crab/WPDB-Table-Builder]

Works with PHP versions 7.1, 7.2, 7.3 & 7.4


## Installation

``` bash
$ composer require pinkcrab/wp-db-migrations
```

## Why

Creates a wrapper around the WPDB_Table_Builder to make it easier to create Migrations for use with WP plugins or themes. Allows for the creation and dropping of database tables and the seeding of initial data.

## How to use

To create a new migration, you will need to extend the abstract class, ```Database_Migration``` and deifne your schema and any data to seed.

```php
<?php

class My_Table extends Database_Migration {

	// Define the tables schema.
    public function set_schema(): void {		
		// Create table
		$this->schema = new Schema('test_table', function(Schema $schema): void{
			$schema->column('id')->unsigned_int(11)->auto_increment();
		    // ........			
			$schema->index('id')->primary();
		});
	}

    // Seeds the table with inital data
    public function seed(): array{
        return array(
			array (
                'col1' => 'alpha',
			    'col2' => 'bravo',
            ),
            array(
                ....
            )
		);
    }

}

## Changelog

* 0.3.0 - Updated to reflect the new version of the WPDB Table Builder.
* 0.2.0 - Extracted from the PinkCrab Framework v0.1.0 registerables package.


