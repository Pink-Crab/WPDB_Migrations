# WordPress Hook Subscriber

Creates a single subscriber for a hook, part of the PinkCrab Plugin Framework

![alt text](https://img.shields.io/badge/Current_Version-0.2.0-yellow.svg?style=flat " ") 
[![Open Source Love](https://badges.frapsoft.com/os/mit/mit.svg?v=102)]()

![](https://github.com/Pink-Crab/Hook_Subscriber/workflows/GitHub_CI/badge.svg " ")![alt text](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/WP_PHPUnit-V5-brightgreen.svg?style=flat " ")
![alt text](https://img.shields.io/badge/PHPCS-WP_Extra-brightgreen.svg?style=flat " ")

 

***********************************************

## Requirements

Requires PinkCrab Plugin Framework Composer and WordPress.

Works with PHP versions 7.1, 7.2, 7.3 & 7.4

## Installation

``` bash
$ composer require pinkcrab/wp-hook-subscriber
```

This module allows for the creation of single Hook Subscriptions for creating an interface with WordPress. Under the hood it still uses the same registration process, the PinkCrab framework is built on, but gives a clean abstraction for single calls.

Each class which extends the provided base class, will have its hook added to the loader on either the defined action or deffered. Allowing full use of the DI container.

Due to the way the Loader registers hook calls, classes are instanced on the init hook. Which can be problematic for WooCommerce and other extendable plugins, where some globals are populated later. The Hook_Subscriber allows for late construction, so your callback will be created in the global scope at that time.

### None Deferred Subscriber

``` php
class On_Single_Hook extends Abstract_Hook_Subscription {

	/**
	 * The hook to register the subscriber
	 * @var string
	 */
	protected $hook = 'some_hook';

    /** 
     * Some service
     * @param My_Service
     */
    protected $my_service;

    public function __constuct(My_Service $my_service ){
        $this->my_service = $my_service;
    }

    /** 
     * Callback
     * @param mixed ...$args
     */
	public function execute( ...$args ): void {
		// Args are accessed in the order they are passed.
        // do_actuion('foo', 'first','second','third',.....);
        //$args[0] = first, $args[1] = second, $args[2] = third, .....

        if($args[0] === 'something'){
            $this->my_service->do_something($args[1]);
        }        
	}
}

// Would be called by
do_action('some_hook', 'something', ['some','data','to do','something']);
```

### Deferred Subscriber

``` php
class Deferred_Hook extends Abstract_Hook_Subscription {

	/**
	 * The hook to register the subscriber
	 * @var string
	 */
	protected $hook = 'some_hook';

    /**
	 * Defered hook to call
	 *
	 * @var string|null
	 */
	protected $deferred_hook = 'some_global_populated';

    /** 
     * Our global data
     * @param Some_Global|null
     */
    protected $some_global;

    public function __constuct(){
        global $some_global;
        $this->some_global = $some_global;
    }

    /** 
     * Callback
     * @param mixed ...$args
     */
	public function execute( ...$args ): void {
        // Depends on a global wich is set later than init.
        if ( $args[0] === 'something' && ! empty( $this->some_global ) ) {
            do_something( $this->some_global->some_property, $args[1] );
        }        
	}
}
```

> Somewhere in another plugin or wp-core $some_global is populated, we can then hook in anytime from when thats created and our hook is actually called.

``` php
function achme_plugin_function(){
    global $some_global; // Currently empty/null
    $some_global = new Some_Global();

    do_action('some_global_populated', ['some', 'data']);
}  
```

> When some_global_populated is fired, a new instance of Deferred_Hook is created and the callback is registered. This gives us access to Some_Global no matter whenever some_global_populated(). We end up creating 2 instances of our deferred hooks, once on init to register the first call, then again on our deferred hook, for the actual hook call.

## Changelog

* 0.2.0 - Moved from the inital Event_Hook naming and made a few minor changes to how deferred hooks are added, using DI to recreate an new instance, over resetting state.
