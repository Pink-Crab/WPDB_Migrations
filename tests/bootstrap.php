<?php

use Dice\Dice;
use PinkCrab\Core\Application\App;
use PinkCrab\Core\Services\Dice\WP_Dice;
use PinkCrab\Core\Services\Registration\Loader;
use PinkCrab\Core\Services\ServiceContainer\Container;
use PinkCrab\Hook_Subscriber\Tests\Stubs\Deferred_Hook;
use PinkCrab\Core\Services\Registration\Register_Loader;
use PinkCrab\Hook_Subscriber\Tests\Stubs\On_Single_Hook;

/**
 * PHPUnit bootstrap file
 */

// Composer autoloader must be loaded before WP_PHPUNIT__DIR will be available
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Give access to tests_add_filter() function.
require_once getenv( 'WP_PHPUNIT__DIR' ) . '/includes/functions.php';

tests_add_filter(
	'muplugins_loaded',
	function() {
		$loader    = Loader::boot();
		$di        = WP_Dice::constructWith( new Dice() );
		$container = new Container();

		// Setup the service container .
		$container->set( 'di', $di );
		$app = App::init( $container );

		add_action(
			'init',
			function () use ( $loader, $app ) {

				// Mock global.
				global $deferred_global;
				$deferred_global = 'init';

				// Register our test subscribers.
				$registerables = array( On_Single_Hook::class, Deferred_Hook::class );
				Register_Loader::initalise( $app, $registerables, $loader );
				$loader->register_hooks();
			},
			1
		);
	}
);

// Start up the WP testing environment.
require getenv( 'WP_PHPUNIT__DIR' ) . '/includes/bootstrap.php';
