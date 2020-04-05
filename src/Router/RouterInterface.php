<?php

namespace WPRouter;

interface RouterInterface {
  public const custom_API_endpoint = "bolt/v1";
  public function hook (string $method, string $endpoint, callable ...$middlewares): void;
  public function get (string $endpoint, callable ...$middlewares): void;
  public function post (string $endpoint, callable ...$middlewares): void;
  public function delete (string $endpoint, callable ...$middlewares): void;
  public function put (string $endpoint, callable ...$middlewares): void;
  public function patch (string $endpoint, callable ...$middlewares): void;
}