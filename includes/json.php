<?php
function sendResponse($statusCode, $message)
{
  http_response_code($statusCode);
  echo ($message);
  die;
}

function sendResponseJson($statusCode, $message)
{
  header("Content-Type: application/json");
  sendResponse($statusCode, json_encode($message));
}


function getRequestData($args = [])
{
  $json = file_get_contents('php://input');
  $data = json_decode($json, true);

  $error = false;

  foreach ($args as $value) {
    $error = $error ? true : ! isset($data[$value]);
  }
  return $error ? null : $data;
}