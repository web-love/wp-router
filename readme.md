![Wordpress router logo](https://i.ibb.co/nsRzTxx/wp-router-3.png)

# Wordpress REST API router library 
![MIT license](https://img.shields.io/packagist/l/weblove/wp-router)
![size](https://img.shields.io/github/languages/code-size/web-love/wp-router)
![version](https://img.shields.io/github/v/release/web-love/wp-router)
![open issues](https://img.shields.io/github/issues/web-love/wp-router)

Easily write modern and reusable route middlewares for your Wordpress projects and plugins. WPRouter is inspired from the excellent [expressJs](https://expressjs.com/fr/) javascript framework. Working with Wordpress REST API will now bring you joy - instead of pain.

* Hook on already existing Wordpress REST endpoints.
* Creating your own endpoints is a breeze.
* Works with custom post types.
* Simplifies the Wordpress route regex notation for simpler `/ressource/:ressource_slug` urls.
* No dependencies, less than 400locs that simply abstract Wordpress core. 

**See on:**
* [Packagist](https://packagist.org/packages/weblove/wp-router)
* [Github](https://github.com/sudomaxime/wp-router)

## Getting started
You need to have [Composer](https://getcomposer.org/) installed on your machine, [follow this link](https://getcomposer.org/doc/00-intro.md) for instructions on how to install Composer.

### Prerequisites
* You need to have PHP >= 7.0
* Wordpress >= 4.7.1

### Installing
The best way to install this library is with composer:
```bash
$ composer require weblove/wp-router
```

### Basic example
```php
$router = new Weblove\WPRouter\Router;

$router->get("/meme/:id", function ($request, $response) { 
  $params = $request->get_url_params();
  $the_meme = get_meme_by_id($params["id"]);
  $response["body"] = $the_meme;
  return $response;
});
```

The `$request` function param implements Wordpress [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) class. The `$response` param is an empty variable that can be used to pass data to the next middleware down the chain, or to send back data on your endpoint.

### Creating your first middleware
Creating a middleware is a breeze. Create an anonymous function with `$request` and `$response` as parameters. A middleware custom middleware can return any type of data that is JSON serializable or an instance of [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/)

```php
$myCustomMiddleware = function ($req, $res) {
  $potato_id = $req->get_param("id");
  $res["potato"] = get_potato($potato_id);

  return $res;
};
```

### Returning early
If you want to break your request early, you can return an instance of [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) as a response and the router will block executing subsequent middlewares and return that response. This can be very useful to send back errors to the API:

```php
$myCustomAuthMiddleware = function ($req, $res) {
  $can_touch_this = user_can_touch_this();
  
  if (!$can_touch_this) {
    return new WP_REST_Response("Can't touch this", 403);
  }

  return $res;
};
```

### Chaining middlewares
Just like expressJS, you can chain middlewares one after the other. The Router methods can take many functions as parameters. The `$response` body is simply passed to the next middleware down the chain. You can use this pattern to isolate logic in small and easy to test functions that have a single purpose:

```php
$router->get("/meme/:id/category", 
  function ($req, $res) 
  { 
    $params = $req->get_url_params();
    $the_meme = get_meme_by_id($params["id"]);
    $res["body"] = $the_meme;
    return $res;
  },
  function ($req, $res)
  {
    $meme_category_id = $res["body"]["category"]["id"];
    $meme_category_infos = get_meme_cat_infos($meme_category_id);
    $res["body"] = $meme_category_infos;
    return $res;
  }
);
```

### Hook on an existing wordpress REST endpoint
You can also modify the response body of an existing wordpress endpoint with the `public hook()` method. Middlewares added to the `hook` handler will have a pre-filled `$response` parameter with the array that Wordpress would normally return to the client. You can easily modify the response before returning. 

A hook request **MUST** end with a [WP_REST_Response](https://developer.wordpress.org/reference/classes/wp_rest_response/) class, it is possible to pass custom `$request` objects from middleware to middleware, however your last middleware (or your early breaks) must always be an instance of `WP_REST_Request`. This way you make certain that other plugins that interops with the REST API will keep working properly.

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
