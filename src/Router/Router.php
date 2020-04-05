<?php

namespace WPRouter;

/**
 * This class virtualises the Wordpress 
 * rest API filters and hooks in a more
 * user friendly format.
 * 
 * Inpspired from ExpressJs.
 * @author sudomaxime
 */

class Router implements RouterInterface 
{
  use RouterMethods;

  /**
   * Namespace for the custom endpoints
   */
  public $custom_API_endpoint = "api";
  

  /**
   * Set a namespace for custom endpoints
   * 
   * @var string $namespace
   */
  function __construct ($namespace = null) {
    if ($namespace) {
      $this->custom_API_endpoint = $namespace;
    }
  }

  /**
   * Applies a series of middlewares to
   * an existing WP endpoint with the 
   * specified verb
   * 
   * @var string $endpoint
   * @var callable[] $middleware
   * @return void
   */
  public function hook (string $method, string $endpoint, callable ...$middlewares): void
  {
    foreach($middlewares as $callback) {
      add_filter($this->post_hook, $this->make_callback_hook($method, $endpoint, $callback), 10, 3);
    }
  }

  /**
   * Register a new GET route in the
   * custom namespace
   * 
   * @var string $endpoint
   * @var callable $callback
   */
  public function get (string $endpoint, callable ...$middlewares): void 
  {
    $this->register_custom_route('GET', $endpoint, $middlewares);
  }

  /**
   * Register a new POST route in the
   * custom namespace
   * 
   * @var string $endpoint
   * @var callable $callback
   */
  public function post (string $endpoint, callable ...$middlewares): void 
  {
    $this->register_custom_route('POST', $endpoint, $middlewares);
  }

  /**
   * Register a new DELETE route in the
   * custom namespace
   * 
   * @var string $endpoint
   * @var callable $callback
   */
  public function delete (string $endpoint, callable ...$middlewares): void 
  {
    $this->register_custom_route('DELETE', $endpoint, $middlewares);
  }

  /**
   * Register a new PUT route in the
   * custom namespace
   * 
   * @var string $endpoint
   * @var callable $callback
   */
  public function put (string $endpoint, callable ...$middlewares): void 
  {
    $this->register_custom_route('PUT', $endpoint, $middlewares);
  }

  /**
   * Register a new PATCH route in the
   * custom namespace
   * 
   * @var string $endpoint
   * @var callable $callback
   */
  public function PATCH (string $endpoint, callable ...$middlewares): void 
  {
    $this->register_custom_route('PATCH', $endpoint, $middlewares);
  }

}