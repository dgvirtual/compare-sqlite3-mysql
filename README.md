# compare Mysql and Sqlite3

This little repo is meant to build test cases to compare sqlite and mysql performance.

## Prepare the environment

Run

`gunzip persons.db.gz`

to extract the sqlite3 database.

Import persons.sql.gz into a mysql database `persons` add a user `myuser` with password `myuser` to that db.


## Run the test

Run the test with

`php test.php`

(it runs a query 10 times and returns comparison)

on my machine the difference is surprising:

    SQLite search time: 0.0014431476593018 seconds
    MySQL search time: 9.0590870380402 seconds