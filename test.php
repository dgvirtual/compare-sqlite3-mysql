<?php
ini_set('display_errors', 1);
ini_set('max_execution_time', '400');
setlocale(LC_ALL, 'lt_LT.UTF-8');
//echo "<pre>";

// Connect to the SQLite database
// run gunzip persons.db.gz to unzip the sqlite database
$sqlite_db = new SQLite3('persons.db');
// add collation for lithuanian characters, used in the db:
$sqlite_db->createCollation('utf8_lithuanian_ci', function ($a, $b) {
    return strcoll($a, $b);
});

// replace the native LOWER function with custom (which supports utf8 lowercasing)
$sqlite_db->createFunction('LOWER', function ($str) {
    return mb_strtolower($str);
});


// create database persons with the below parameters and import into it
// the file persons.sql.gz
$mysql_host = '127.0.0.1'; // localhost did not work on my host somehow
$mysql_user = 'myuser';
$mysql_password = 'myuser';
$mysql_database = 'persons';

$mysql_db = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_database, '3306');

// Search string
$search_term = (isset($argv[1]) && is_string($argv[1])) ? $argv[1] : 'įmonė';


// **Benchmarking the search**

// Number of times to run the search for each database
// defaults to 10
$iterations = (isset($argv[2]) && is_numeric($argv[2])) ? $argv[2] : 10;

// prevent iterations zero
if ($iterations === 0) {
    echo "No iterations specified, exiting..." . PHP_EOL;
} elseif ($iterations > 30 && $iterations <= 100) {
    echo "Looping over $iterations iterations will take some time..." . PHP_EOL;
} elseif ($iterations > 100) {
    echo "Looping over $iterations iterations will take forever, exiting..." . PHP_EOL;
}

// **SQLite3 Search**

$start_time_sqlite = microtime(true);
$sqlite_found_count = 0;

for ($i = 0; $i < $iterations; $i++) {
    $sqlite_stmt = $sqlite_db->prepare('SELECT * FROM persons WHERE LOWER(ja_pavadinimas) LIKE ?');
    $sqlite_stmt->bindValue(1, "%$search_term%");
    // Execute the statement
    $result = $sqlite_stmt->execute();

    // Count rows manually
    $row_count = 0;
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $row_count++;
    }
    $sqlite_found_count += $row_count;

    $result->finalize();
    $sqlite_stmt->close();
}

$end_time_sqlite = microtime(true);
$sqlite_execution_time = $end_time_sqlite - $start_time_sqlite;

// **MySQL Search**

$start_time_mysql = microtime(true);
$mysql_found_count = 0;
for ($i = 0; $i < $iterations; $i++) {
    // Prepare the statement
    //$mysql_stmt = $mysql_db->prepare('SELECT * FROM persons WHERE ja_pavadinimas LIKE ?');
    //SELECT * FROM `persons` WHERE MATCH(ja_pavadinimas) AGAINST('įmonė' IN NATURAL LANGUAGE MODE);
    $mysql_stmt = $mysql_db->prepare('SELECT * FROM persons WHERE MATCH(ja_pavadinimas) AGAINST(? IN NATURAL LANGUAGE MODE)');

    // Create a variable for the search term and bind it
    $search_pattern = "%$search_term%";
    $mysql_stmt->bind_param('s', $search_pattern);

    // Execute the statement
    $mysql_stmt->execute();

    // Store the result (optional for efficiency)
    $mysql_stmt->store_result();

    // Free memory used by the result set
    $mysql_stmt->free_result();

    // Get number of found rows for each iteration
    $mysql_found_count += $mysql_stmt->affected_rows;

    // Close the statement
    $mysql_stmt->close();
}
$end_time_mysql = microtime(true);

// **Calculate and display results**

$sqlite_time = $end_time_sqlite - $start_time_sqlite;
$mysql_time = $end_time_mysql - $start_time_mysql;

echo "Iterations: " . $iterations . PHP_EOL;
echo "Search term: " . $search_term . PHP_EOL;
echo "Search time averages: \n";
echo "SQLite: " . $sqlite_time / $iterations . " seconds\n";
echo "MySQL: " . $mysql_time / $iterations . " seconds\n";

// **Display average number of found results**
echo "Average Found Results: \n";
echo "SQLite: " . $sqlite_found_count / $iterations . " results\n";
echo "MySQL: " . $mysql_found_count / $iterations . " results\n";

// Close connections
$sqlite_db->close();
$mysql_db->close();
