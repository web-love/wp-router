<?php

namespace Weblove\WPRouter;

/**
 * Interface to implement
 * the public Router methods. 
 */

interface RouterInterface {
  public const custom_API_endpoint = "api/v1";
  public function hook (string $method, string $endpoint, callable ...$middlewares): void;
  public function get (string $endpoint, callable ...$middlewares): void;
  public function post (string $endpoint, callable ...$middlewares): void;
  public function delete (string $endpoint, callable ...$middlewares): void;
  public function put (string $endpoint, callable ...$middlewares): void;
  public function patch (string $endpoint, callable ...$middlewares): void;
}