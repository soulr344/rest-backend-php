<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2
 * of the License.
 */

ob_start();
require_once("inc.php");
ob_end_clean();

/**
 * Sets the logging according to the
 */
function setLogging()
{
  $dev = (constant("ENVIRONMENT") ?? "DEVELOPMENT") === "DEVELOPMENT";
  ini_set('display_errors', $dev ? 1 : 0);
  ini_set('display_startup_errors', $dev ? 1 : 0);
  error_reporting($dev ? E_ALL : null);
}

/**
 * Parses route and handles the logic.
 */
function parseRoutes()
{
  $uri = rtrim("src/routes" . strtok($_SERVER["REQUEST_URI"], '?'), "/");

  $controller = "";
  if (file_exists($uri . ".php")) {
    $controller = $uri . ".php";
  } else if (file_exists($uri . "/index.php")) {
    $controller = $uri . "/index.php";
  } else {
    sendResponseJson(404, array("success" => false, "message" => "Not Found."));
  }

  $frontend = false;

  ob_start();
  require($controller);
  if ($frontend)
    return;
  else
    ob_end_clean();

  if (function_exists($_SERVER["REQUEST_METHOD"])) {
    $_SERVER["REQUEST_METHOD"]();
  } else {
    sendResponseJson(405, array("success" => false, "message" => "Method Not Allowed."));
  }
}

setLogging();
parseRoutes();

?>