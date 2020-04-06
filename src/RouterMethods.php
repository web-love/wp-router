<?php

namespace Weblove\WPRouter;

trait RouterMethods
{
  /**
   * Hook before WP handlers
   * used to put gards before requests
   * 
   * @var string
   */
  private $pre_hook = 'rest_pre_dispatch';

  /**
   * Hook before WP callbacks
   * used to change wordpress formatted requests
   * 
   * @var string
   */
  private $pre_callbacks = 'rest_request_before_callbacks';

  /**
   * Hook after WP actions and handlers
   * used to format response bodies
   * 
   * @var string
   */
  private $post_hook = 'rest_post_dispatch';

  /**
   * Hook before WP saves a document
   * to provided post type.
   * 
   * @var string $post_type_slug
   * @return string
   */
  private function pre_insert_hook (string $post_type_slug): string {
    return "rest_pre_insert_{$post_type_slug}";
  }
  
  /**
   * Compare endpoint against
   * request route to check
   * if patterns match.
   * 
   * TODO: Optimize and test this code for speed.
   * 
   * @var WP_REST_Request $request 
   * @var string $method
   * @var string $endpoint
   * @return bool
   */
  private function route_match ($request, string $method, string $endpoint): bool 
  {
    $mw_method = $request->get_method();
    $mw_route = $request->get_route();
    $mw_params = $request->get_url_params();

    preg_match_all('/:[a-z|A-Z]+/', $endpoint, $matches, PREG_UNMATCHED_AS_NULL);

    foreach ($matches[0] as $match) {
      $param_matches[] = str_replace(":", "", $match);
    }

    // If http verbs doesn't match
    if ($mw_method != $method) {
      return false;
    }

    // If amount of url params doesn't match
    if (count($mw_params) != count($param_matches)) {
      return false;
    }
    
    // If directory deepness doesn't match
    if (count(explode("/", $endpoint)) != count(explode("/", $mw_route))) {
      return false;
    }

    // If param names and position are different
    if (array_keys($mw_params) != $param_matches) {
      return false;
    }

    $diffs = array_diff(explode("/", $endpoint), explode("/", $mw_route));
    $url_matches = true;
    
    foreach ($diffs as $diff) {
      if (strpos($diff, ':') !== false && !in_array($diff, $matches[0])) {
        $url_matches = false;
        break;
      }
      if (strpos($diff, ':') === false) {
        $url_matches = false;
        break;
      }
    }
    
    return $url_matches;
  }

  /**
   * Applies implemented middlewares on
   * the responses before it is
   * send back on the network.
   * 
   * @var string $method
   * @var string $endpoint
   * @var callable $callback
   * 
   * @return callable
   */
  private function make_callback_hook (string $method, string $endpoint, callable $callback): callable 
  {
    return function($response, $instance, $request) use ($method, $endpoint, $callback) 
    {
      if($this->route_match($request, $method, $endpoint)) {
        return $callback($request, $response);
      } else {
        return $response;
      }
    };
  }

  /**
   * Loops over an array of
   * callable middlewares and
   * iteratively builds the response
   * object.
   * 
   * @var WP_REST_Request $request
   * @var callable[] $middlewares
   * @return Iterable
   */
  private function middleware_generator ($request, $middlewares): Iterable {
    $response = [];

    foreach($middlewares as $callback) {
      $response = $callback($request, $response);
      yield $response;
    }
  }

  /**
   * Loops over the middleware iterator
   * and passes the request and response 
   * objects to the next callback in the
   * chain. The sum of the iterator responses
   * creates the final network response.
   * 
   * The function returns a new function that
   * can be passed to wordpress register_rest_route 
   * callback.
   * 
   * @var callable[] $middlewares
   * @return function
   */
  private function callback_generator ($middlewares): callable {
    return function ($request) use ($middlewares) {
      $response;
  
      foreach ($this->middleware_generator($request, $middlewares) as $callback) {
        $response = $callback;
        // If $response returns Wordpress response, we break the iterator 
        // and shortcircuit the return. 
        if (is_a($response, 'WP_REST_Response')) {
          break;
        }
      }
  
      return $response;
    };
  }

  /**
   * Binds middleware callback to
   * wordpress REST routes with the specified
   * method, endpoint and middlewares
   * 
   * @var string $method
   * @var string $endpoint
   * @var callable[] $middlewares
   * @return void
   */
  private function register_custom_route ($method, $endpoint, $middlewares): void {
    $endpoint = $this->route_params_to_route_regex($endpoint);
    
    add_action('rest_api_init', function () use ($method, $endpoint, $middlewares) {
      register_rest_route($this->custom_API_endpoint, $endpoint, array(
        'methods' => $method,
        'callback' => $this->callback_generator($middlewares),
      ));
    });
  }
  
  /**
   * Takes an endpoint string that may contain
   * simplified url params notation (:slug) and
   * transforms it into a format that
   * Weirdpress understands.
   * 
   * TODO: Optimize and test this code for performance.
   * 
   * @var string $endpoint;
   * @return string
   */
  private function route_params_to_route_regex (string $endpoint): string {
    if (strpos($endpoint, ':') == false) { 
      return $endpoint;
    }

    $route_elements = array_filter(explode("/", $endpoint));
    $parsed_route = '';

    foreach ($route_elements as $directory) {
      $parsed_route .= '/';
      if (strpos($directory, ':') !== false) {
        $param_slug = str_replace(":", '', $directory);
        // TODO: Check if \d+ only works with ids check if the type must be 
        // maybe \s+ for slugs or expressions.
        $parsed_route .= "(?P<{$param_slug}>\d+)";
      } else {
        $parsed_route .= $directory;
      }
    }

    return $parsed_route;
  }
}