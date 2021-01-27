<?php

declare(strict_types=1);

/**
 * Tests the registering of the callback at a deferred time.
 */

namespace PinkCrab\Hook_Subscriber\Tests\Stubs;

use PinkCrab\Hook_Subscriber\Abstract_Hook_Subscription;


class Deferred_Hook extends Abstract_Hook_Subscription {

	/**
	 * The hook to register the subscriber
	 *
	 * @var string|null
	 */
	protected $hook = 'pc_on_deferred_hook';

	/**
	 * Defered hook to call
	 *
	 * @var string|null
	 */
	protected $deferred_hook = 'pc_pre_deferred_hook';

	/**
	 * Holds an array for testing callbacks.
	 *
	 * @var array
	 */
	public static $log = array();

	public static $deferred = null;

	/**
	 * Constructor to log when its created
	 */
	public function __construct() {
		global $deferred_global;
		self::$deferred = $deferred_global;
	}

	public function execute( ...$args ): void {
		foreach ( $args as $value ) {
			self::$log[ $value ] = self::$deferred;
		}
	}

}
