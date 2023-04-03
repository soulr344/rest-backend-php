<?php

/**
 * This is an example database model configuration.
 */

class SampleModel extends DB
{
  function __construct()
  {
    parent::__construct("sample_table_name", array(
      "id" => "number",
      "name" => "string",
      "email" => "string",
    ));
  }
}