<?php

declare(strict_types=1);

/**
 * Simple, single hook call test.
 *
 */

namespace PinkCrab\Hook_Subscriber\Tests\Stubs;

use PinkCrab\Hook_Subscriber\Abstract_Hook_Subscription;


class On_Single_Hook extends Abstract_Hook_Subscription {

	/**
	 * The hook to register the subscriber
	 *
	 * @var string|null
	 */
	protected $hook = 'pc_on_single_hook';

	/**
	 * Holds an array for testing callbacks.
	 *
	 * @var array
	 */
	public static $log = array();

	public function execute( ...$args ): void {
		foreach ( $args as $value ) {
			self::$log[] = $value;
		}
	}

}
