![Wordpress router logo](https://i.ibb.co/nsRzTxx/wp-router-3.png)

# Wordpress REST API router library 
![MIT license](https://img.shields.io/packagist/l/weblove/wp-router)
![size](https://img.shields.io/github/languages/code-size/sudomaxime/wp-router)
![version](https://img.shields.io/github/v/release/sudomaxime/wp-router)
![open issues](https://img.shields.io/github/issues/sudomaxime/wp-router)

Easily write modern and reusable route middlewares for your Wordpress projects and plugins. WPRouter is inspired from the excellent [expressJs](https://expressjs.com/fr/) javascript framework. Working with Wordpress REST API will now bring you joy - instead of pain.

* Hook on already existing Wordpress REST endpoints.
* Creating your own endpoints is a breeze.
* Works with custom post types.
* Simplifies the Wordpress route regex notation for simpler `/ressource/:ressource_slug` urls.
* No dependencies, only =/- 300locs and simply extends/abstract Wordpress core. 

**See on:**
* [Packagist](https://packagist.org/packages/weblove/wp-router)
* [Github](https://github.com/sudomaxime/wp-router)

## Getting started
You need to have [Composer](https://getcomposer.org/) installed on your machine, [follow this link](https://getcomposer.org/doc/00-intro.md) for instructions on how to install Composer.

## Prerequisites
* You need to have PHP >= 5.5.0
* Wordpress >= 4.7.1

### Installing
The best way to install this library is with composer:
```bash
$ composer require weblove/wp-router
```

### Basic example
```php
/**
 * Initialize your custom router, default route is
 * on /wp-json/api
 */
$router = new Weblove\WPRouter\Router;

/**
 * Hook an already existing wordpress REST endpoint
 * with the GET verb, apply your middlewares on
 * the request.
 */
$router->hook('GET', '/wp/v2/posts/:id', 
  $authMiddleware,
  $responseMiddleware
);

/**
 * Or even better, create your own endpoints !
 */
$router->get("/foo/:id", 
  function ($request, $response) 
  { 
    $response["woo"] = "foo";
    return $response;
  }, 
  function ($request, $response) 
  {
    echo $reponse["woo"]; // echoes string "foo" 
    $response["foo"] = "woo";
    return $response;
  }
);
```

The `$request` function param implements Wordpress [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) class. The `$response` param is an empty variable that can be used to pass data to the next middleware down the chain, or to send back data on your endpoint.

### Creating a middleware
Creating a middleware is easy, simply create an anonymous function that takes `$request` and `$response` as parameters. A middleware should **always** return a `array $response` or an instanceof `WP_REST_Request`:

```php
$myCustomMiddleware = function ($req, $res) {
  $potato_id = $req->get_param("id");
  $res["potato"] = get_potato($potato_id);

  return $res;
};
```

If you want to shortcircuit your request you can simply return an instance of [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) as a response and the router will automatically break early and return that response instead. This can be very useful to send back errors to the API:

```php
$myCustomAuthMiddleware = function ($req, $res) {
  $can_touch_this = user_can_touch_this();
  
  if (!$can_touch_this) {
    return new WP_REST_Response("Can't touch this", 403);
  }

  return $res;
};
```

### Hook on an existing wordpress REST endpoint
You can also easily modify the response body of an existing wordpress endpoint with the `public hook()` method. Simply put, middlewares added to the `hook` handler will have a pre-filled `$response` parameter with the array that Wordpress would normally return to the client. You can easily modify the return response this way. 

A hook request **MUST** end with a [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) class, it is possible to pass custom $request objects from middleware to middleware, however in order to make other plugins and other wordpress REST hooks work, you must respect this pattern. 

note: *You do not need to put /wp-json in your endpoint address.*

```php
$router->hook('GET', '/wp/v2/posts/:id', 
  $authMiddleware,
  $responseMiddleware // this function ends by returning an instance of WP_REST_Response
);
```

### Customize the default directory
By default your routes get added under the `/wp-json/api` directory. You can change the default behavior by providing a router parameter to the `Router` class:

```php
$router = new Weblove\WPRouter\Router; // defaults to /wp-json/api
$router = new Weblove\WPRouter\Router("custom"); // now set to /wp-json/custom
```

### Public methods
* `get(string $endpoint, middleware ...$middleware)`
* `post(string $endpoint, middleware ...$middleware)`
* `put(string $endpoint, middleware ...$middleware)`
* `delete(string $endpoint, middleware ...$middleware)`
* `patch(string $endpoint, middleware ...$middleware)`
* `hook(string $method_verb, string $endpoint, middleware ...$middleware)`  - Use an already existing Wordpress endpoint.
* **In development** `use(string $endpoint, middleware ...$middleware)` - Use middlewares on all verbs of that endpoint.

### Troubleshooting and frequent errors
* My endpoint is returning an error `Uncaught Error: Call to a member function get_matched_route()` - This means you have a hook that doesn't return an instance of WP_REST_Response after all the middlewares have been executed. See this guide section on how to properly hook on existing wordpress endpoints.

## Authors
* Maxime Nadeau - *initial work* - [Weblove](http://weblove.ca)

## License
This project is licensed under the MIT License - see the license.md file for details.
