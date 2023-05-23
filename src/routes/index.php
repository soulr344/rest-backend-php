<?php

function GET()
{
  sendResponseJson(200, array("status" => true, "message" => "Hello from API."));
}