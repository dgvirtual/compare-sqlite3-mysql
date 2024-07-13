# compare Mysql and Sqlite3

This little repo is meant to build test cases to compare sqlite and mysql performance.

## Prepare the environment

You will need PHP 7.4 - 8.3 to run this script, as well as mariadb or mysql (sqlite3 support is built-in).

Run

`gunzip persons.db.gz`

to extract the sqlite3 database.

Import persons.sql.gz into a mysql database `persons` add a user `myuser` with password `myuser` to that db.


## Run the test

Run the test with

`php test.php "Search Phrase" 3`

where "Search Phrase" is any phrase to look for in legal person name (defaults to "firma" if omited), and number of iterations of search (defaults to 10 if omited).

on my machine the difference is surprising:

    Iterations: 10
    Search phrase: Individuali įmonė
    Search time averages:
    SQLite: 0.0010130405426025 seconds
    MySQL: 1.0484039783478 seconds

