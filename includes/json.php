<?php

/**
 * Sends response to the client along with the status code and stops the execution of the script after.
 * 
 * @param int $statusCode The status code to be sent. Default is `200`.
 * @param string $message Message that is sent as response body to the client.
 * 
 * Example:
 * ```php
 * <?php
 * $body = "Hello World!";
 * sendResponse(200, $body));
 * ?>
 * ```
 * 
 * Sends the response body `Hello World!` with status code `200`.
 */
function sendResponse(int $statusCode = 200, string $message) : void
{
  http_response_code($statusCode);
  echo ($message);
  die;
}

/**
 * Sends response to the client as JSON along with the status code and stops the execution of the script after.
 * 
 * @param int $statusCode The status code to be sent. Default is `200`.
 * @param array $jsonObj Array from which a JSON string is generated and sent to the client.
 * 
 * Example:
 * ```php
 * <?php
 * $body = array("error" => "Access Denied.");
 * sendResponseJson(403, $body));
 * ?>
 * ```
 * 
 * Sends the response body `{"error": "Access Denied"}` with status code `403`.
 */
function sendResponseJson(int $statusCode = 200, array $jsonObj) : void
{
  header("Content-Type: application/json");
  sendResponse($statusCode, json_encode($jsonObj));
}

/**
 * Returns associative array sent to the server as JSON in request body.
 * @param array $args Indexed array of keys that must be present in the request body.
 * @return array|null Returns associative array of the json parsed request body or `null` if any key in `$args` isn't present.
 * 
 * Example:
 * ```
 * <?php
 * // $data is null if one of email or password field isn't in the request body,
 * // or an associative array with the password and the email field.
 * $data = getRequestData(["email", "password"]);
 * ?>
 * ```
 */
function getRequestData($args = []) : array|null
{
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $error = false;

  foreach ($args as $value) {
    $error = $error ? true : ! isset($data[$value]);
  }
  return $error ? null : $data;
}