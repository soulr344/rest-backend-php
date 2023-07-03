<?php
/**
 * DB class provides all basic CRUD methods for data manipulation in a MySQL Database.
 * 
 * @property mysqli $conn
 */
class DB
{
  /**
   * This stores the `mysqli` object after connection is established.
   * @var mysqli
   */
  public $conn;
  /**
   * This stores the table name of the Object.
   * @var string
   */
  private $tablename;
  /**
   * This is the table column definition for the specified table.
   * Example:
   * ```
   * <?php
   * $columnDef = array(
   *    "id" => "number",
   *    "name" => "string",
   *    "someData": "json"
   * );
   * ```
   * @var array
   */
  private $columns;

  /**
   * @param string $tablename Name of the table to be accessed.
   * @param array $columns Column definition for the table. More at [Documentation](https://github.com/soulr344/rest-backend-php).
   */
  public function __construct(string $tablename, array $columns)
  {
    $this->tablename = $tablename;
    $this->columns = $columns;

    $this->conn = new mysqli(constant("DB_HOST"), constant("DB_USER"), constant("DB_PASS"), constant("DB_NAME"));

    if ($this->conn->connect_error) {
      die("Connection failed: " . $this->conn->connect_error);
    }
  }

  /**
   * @param array $colsToFetch Array of all the columns to fetch. Empty array to fetch all columns.
   * @param string $clause SQL Clause that will be appended to the end of the SQL Query.
   * @return array|bool Returns `false` if the query failed or an array of rows the query returned.
   * 
   * Fetch data from the database table.
   * Example:
   * ```
   * <?php
   * ...
   * $rows = $db->fetch([], "WHERE id='1'");
   * ```
   * More at [Documentation](https://github.com/soulr344/rest-backend-php)
   */
  public function fetch(array $colsToFetch = [], string $clause = "") : array|bool
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
   * @param array $data Associative array with all the required fields for the table.
   * 
   * Insert data to the database table.
   * Example:
   * ```
   * <?php
   * ...
   * $data = Array("name" => "John Doe", "Phone" => 1000000000);
   * $result = $user->insert($data);
   * ```
   * More at [Documentation](https://github.com/soulr344/rest-backend-php)
   */
  public function insert(array $data) : bool|mysqli_result
  {
    $insertInto = [];
    $insertValues = [];

    foreach ($data as $column => $value) {
      $temp = "";
      if ($this->columns[$column] === "json") {
        $value = json_encode($value);
        $temp = "'{$value}'";
      } else if ($this->columns[$column] === "number") {
        $temp = $this->escape_string($value);
      } else {
        $temp = "'{$this->escape_string($value)}'";
      }

      array_push($insertInto, $column);
      array_push($insertValues, $temp);
    }

    $insertInto = join(", ", $insertInto);
    $insertValues = join(", ", $insertValues);

    $query = "INSERT into `{$this->tablename}` ({$insertInto}) VALUES ({$insertValues});";

    return $this->conn->query($query);
  }

  /**
   * @param array $data Associative array with all the column values to update.
   * @param string $clause SQL Clause that will be appended at the end of the SQL Query before executing.
   * 
   * Update data to the database table.
   * Example:
   * ```
   * <?php
   * ...
   * $data = Array("name" => "Sarah Jane", "Phone" => 111111111);
   * $result = $user->update($data, "WHERE id='21'");
   * ```
   * More at [Documentation](https://github.com/soulr344/rest-backend-php)
   */
  public function update(array $data, string $clause = "") : bool|mysqli_result
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
   * Deletes data from the database table.
   * 
   * @param string $clause SQL Clause to that will be appended at the end of the SQL Query before execution.
   * Example:
   * ```
   * <?php
   * ...
   * $result = $user->delete("WHERE id='12'");
   * ```
   */
  public function delete(string $clause = "") : bool|mysqli_result
  {
    $query = "DELETE FROM `{$this->tablename}` {$clause};";

    return $this->conn->query($query);
  }

  /**
   * Alias of mysqli_real_escape_string()
   * This function is an alias of: mysqli_real_escape_string().
   *
   * @param string $string The string to be escaped. Characters encoded are `NUL (ASCII 0), \n, \r, \, ', ", and
   *                           Control-Z`.
   * @return string Returns an escaped string.
   */
  public function escape_string(string $string) : string
  {
    return $this->conn->escape_string($string);
  }

  /**
   * Formats an associative array according to the column definition.
   * Read more at [Documentation](https://github.com/soulr344/rest-backend-php).
   */
  private function formatRow(array $row)
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

    // append all other columns as string if column isn't present in the column definition
    foreach (array_diff_key($row, $formattedRow) as $columnName => $value) {
      $formattedRow[$columnName] = $row[$columnName];
    }
    return $formattedRow;
  }
}