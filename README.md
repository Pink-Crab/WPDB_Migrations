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

<<<<<<< Updated upstream
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
=======
## Useage

If you are planning to use this outside of the PinkCrab framework, you will need to manually inject the dependencies (or use your own DI Container)

```php
## CREATE THE MIGRATION OBJECT

use PinkCrab\Table_Builder\Table_Schema;
use PinkCrab\DB_Migration\Database_Migration;

class User_Details_Migrations extends Database_Migration {

    /**
     * Define your table schema using Table_Schema or any other
     * table schema that implments SQL_Schema interface
     */ 
	public function set_schema(): void {
		$this->schema = Table_Schema::create( 'user_details' )
			->column( 'id' )
				->type( 'int' )
				->length( 11 )
				->auto_increment()

			->column( 'user' )
				->type( 'tinytext' )
				->default( '' )

			->column( 'email' )
				->type( 'text' )
				->default( '' )

            ->column( 'phone' )
				->type( 'text' )
				->default( '' )
			
            ->column( 'date_created' )
				->type( 'DATETIME' )
				->nullable( false )

			->column( 'last_update' )
				->type( 'DATETIME' )
				->nullable( false )
			->primary( 'id' );
	}

	/**
	 * Creates a defualt admin user on setup.
	 *
	 * @return void
	 */
	public function post_up(): void {
		$this->wpdb->insert(
			'test_table',
			array(
				'user'         => 'admin',
				'email'      => 'admin@url.com',
				'phone'      => '0115 9559682',
				'date_created' => date( 'Y-m-d H:i:s', time() ),
				'last_update'  => date( 'Y-m-d H:i:s', time() ),
			),
			array( '%s', '%s', '%s', '%s' , '%s' )
		);
	}
```
> Once you have created your Migration, its just a case of calling it whenver you need. In this example we will use the 
>>>>>>> Stashed changes

## Changelog

* 0.3.0 - Updated to reflect the new version of the WPDB Table Builder.
* 0.2.0 - Extracted from the PinkCrab Framework v0.1.0 registerables package.


