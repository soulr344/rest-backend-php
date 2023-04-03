<?php
class DB
{
  public $conn;
  private $tablename;
  private $columns;
  public function __construct($tablename, $columns)
  {
    $this->tablename = $tablename;
    $this->columns = $columns;

    $this->conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASS"), constant("DB_NAME"));

    if ($this->conn->connect_error) {
      die("Connection failed: " . $this->conn->connect_error);
    }
  }


  /**
   * Data fetching methods
   */
  public function fetch($colsToFetch = [], $clause = "")
  {
    $parsedCols = count($colsToFetch) === 0 ? "*" : join(", ", $colsToFetch);
    $query = "SELECT {$parsedCols} FROM `{$this->tablename}` {$clause};";

    $result = $this->conn->query($query);

    if ($result === false)
      return false;

    $rows = [];
    while ($row = $result->fetch_assoc()) {
      array_push($rows, $this->formatRow($row));
    }

    return $rows;
  }

  /**
   * Data Inserting Methods
   */
  public function insert($data, $clause = "")
  {
    $insertInto = [];
    $insertValues = [];

    foreach ($data as $column => $value) {
      $temp = "";
      if ($this->columns[$column] === "json") {
        $value = json_encode($value);
      }

      if ($this->columns[$column] === "number") {
        $temp = $this->escape_string($value);
      } else {
        $temp = "'{$this->escape_string($value)}'";
      }

      array_push($insertInto, $column);
      array_push($insertValues, $temp);
    }

    $insertInto = join(", ", $insertInto);
    $insertValues = join(", ", $insertValues);

    $query = "INSERT into `{$this->tablename}` ({$insertInto}) VALUES ({$insertValues}) {$clause};";

    return $this->conn->query($query);
  }

  /**
   * Data Manipulation Methods
   */
  public function update($data, $clause = "")
  {
    $update = "";
    foreach ($data as $column => $value) {
      $temp = "";
      if ($this->columns[$column] === "json") {
        $value = json_encode($value);
      }

      if ($this->columns[$column] === "number") {
        $temp = $this->escape_string($value);
      } else {
        $temp = "'{$this->escape_string($value)}'";
      }
      $update .= "`{$column}` = " . $temp . ", ";
    }
    $update = rtrim($update, ", ");

    $query = "UPDATE `{$this->tablename}` SET {$update} {$clause};";

    return $this->conn->query($query);
  }

  /**
   * Data Delete Methods
   */
  public function delete($clause = "")
  {
    $query = "DELETE FROM `{$this->tablename}` {$clause};";

    return $this->conn->query($query);
  }

  /**
   * Utility methods
   */
  public function escape_string($string)
  {
    return $this->conn->escape_string($string);
  }

  private function formatRow($row)
  {
    $formattedRow = array();
    foreach ($this->columns as $columnName => $type) {
      if (! isset($row[$columnName])) {
        continue;
      }

      switch ($type) {
        case "number":
          $formattedRow[$columnName] = intval($row[$columnName]);
          break;
        case "json":
          $formattedRow[$columnName] = json_decode($row[$columnName]);
          break;
        default:
          $formattedRow[$columnName] = $row[$columnName];
          break;
      }
    }
    return $formattedRow;
  }
}