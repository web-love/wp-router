# Wordpress router for REST API
WP router is an [expressJs](https://expressjs.com/fr/) inspired router for Wordpress REST api. We always found Wordpress REST logic to be cumbersome, boilerplate-y and difficult to extend, leading to very large callback functions and spagetthi code. With this plugin you can easily write reusable middlewares for your projects or your plugins. You can also bypass Wordpress permission callbacks and easily make your own Authorization middlewares.

* You can extend or "hook" existing Wordpress REST endpoints
* Works great with virtually all other WP REST plugins 
* Works great with cache plugins for fast API's.
* If you are not a Wordpress or PHP guru, you can use this to get superpowers.
* You don't need to understand route regexes, you can use simple param notation for your routes `example/:example_id/ressource`
* Promotes code re-use and fast development time.
* Without dependencies and only 300 loc, make Wordpress fast again.

### Basic example
```php
/**
 * Initialize your custom router, default route is
 * on /wp-json/api
 */
$router = new WPRouter\Router;

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
 * like the previous example, you can chain custom
 * middlewares that have access to the the $request 
 * and $response object, 
 */
$router->get("/foo/:id", 
  function ($request, $response) 
  {
    echo $request->get_url_params(); // echoes array [0 => "id"] 
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

The `$request` function param implements Wordpress [WP_REST_Request](https://developer.wordpress.org/reference/classes/wp_rest_request/) class. So any methods you are used inside a REST callback are available there. The `$response` param is an empty array, use it to pass anything to the next middleware down the chain. 

### Creating a middleware
Creating a middleware is very easy, simply create an anonymous function that takes $request and $response as parameters. A middleware should **always** return a `array $response` or an instanceof `WP_REST_Request`:

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
You can also easily modify the response body of an existing wordpress endpoint with the `public hook()` method. Simply put, middlewares added to the `hook` handler will have a pre-filled `$response` parameter with what Wordpress would normally return to the client. You can easily modify the return response this way. You can also hook onto custom post types and any plugin responses that uses Wordpress REST hooks.

note: *You do not need to put /wp-json in your endpoint address.*

```php
$router->hook('GET', '/wp/v2/posts/:id', 
  $authMiddleware,
  $responseMiddleware
);
```

### Customize the default directory
By default your routes get added under the `/wp-json/api` directory. You can change the default behavior by providing a router parameter to the `Router` class:

```php
$router = new WPRouter\Router; // defaults to /wp-json/api
$router = new WPRouter\Router("custom"); // now set to /wp-json/custom
```

### Public methods
* `get(string $endpoint, middleware ...$middleware)`
* `post(string $endpoint, middleware ...$middleware)`
* `put(string $endpoint, middleware ...$middleware)`
* `delete(string $endpoint, middleware ...$middleware)`
* `patch(string $endpoint, middleware ...$middleware)`
* `hook(string $method_verb, string $endpoint, middleware ...$middleware)`  - Use an already existing Wordpress endpoint.
* **In development** `use(string $endpoint, middleware ...$middleware)` - Use middlewares on all verbs of that endpoint.