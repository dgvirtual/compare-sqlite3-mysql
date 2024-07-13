<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', '400');
setlocale(LC_ALL, 'lt_LT.UTF-8');
//echo "<pre>";

// Connect to the SQLite database
// run gunzip persons.db.gz to unzip the sqlite database
$sqlite_db  = new SQLite3('persons.db');
// add collation for lithuanian characters, used in the db:
$sqlite_db->createCollation('utf8_lithuanian_ci', function ($a, $b) {
  return strcoll($a, $b);
});

// create database persons with the below parameters and import into it 
// the file persons.sql.gz
$mysql_host = '127.0.0.1'; // localhost did not work on my host somehow
$mysql_user = 'myuser';
$mysql_password = 'myuser';
$mysql_database = 'persons';

$mysql_db = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database, '3306');

// Search string
$search_term = 'įmonė';

// **Benchmarking the search**

$iterations = 10; // Number of times to run the search for each database

// **SQLite3 Search**

$start_time_sqlite = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
  $sqlite_stmt = $sqlite_db->prepare('SELECT * FROM persons WHERE ja_pavadinimas LIKE ?');
  $sqlite_stmt->bindValue(1, "%$search_term%");
  // Execute the statement and get the result
  $result = $sqlite_stmt->execute();
}
$sqlite_stmt->close();
$end_time_sqlite = microtime(true);

// **MySQL Search**

$start_time_mysql = microtime(true);
for ($i = 0; $i < $iterations; $i++) {
  // Prepare the statement
  $mysql_stmt = $mysql_db->prepare('SELECT * FROM persons WHERE ja_pavadinimas LIKE ?');

  // Create a variable for the search term and bind it
  $search_pattern = "%$search_term%";
  $mysql_stmt->bind_param('s', $search_pattern);

  // Execute the statement
  $mysql_stmt->execute();

  // Store the result (optional for efficiency)
  $mysql_stmt->store_result();

  // Free memory used by the result set
  $mysql_stmt->free_result();

  // Close the statement
  $mysql_stmt->close();
}
$end_time_mysql = microtime(true);

// **Calculate and display results**

$sqlite_time = $end_time_sqlite - $start_time_sqlite;
$mysql_time = $end_time_mysql - $start_time_mysql;

echo "SQLite search time: " . $sqlite_time . " seconds\n";
echo "MySQL search time: " . $mysql_time . " seconds\n";

// Close connections
$sqlite_db->close();
$mysql_db->close();