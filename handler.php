<?php

require_once("inc.php");

$dev = (constant("ENVIRONMENT") ?? "DEVELOPMENT") === "DEVELOPMENT";
ini_set('display_errors', $dev ? 1 : 0);
ini_set('display_startup_errors', $dev ? 1 : 0);
error_reporting($dev ? E_ALL : null);

$uri = rtrim("src/routes" . strtok($_SERVER["REQUEST_URI"], '?'), "/");

echo($uri);

$controller = "";
if (file_exists($uri . ".php")) {
  $controller = $uri . ".php";
} else if (file_exists($uri . "/index.php")) {
  $controller = $uri . "/index.php";
} else {
  sendResponseJson(404, array("success" => false, "message" => "Not Found."));
}

require($controller);
if (function_exists($_SERVER["REQUEST_METHOD"])) {
  $_SERVER["REQUEST_METHOD"]();
} else {
  sendResponseJson(405, array("success" => false, "message" => "Method Not Allowed."));
}

?>