Your company would like to develop a reward system. The requirement are as below:
  1. Customers will be rewarded with Points when Sales Order in “Complete” status.
  2. Every USD$1 sales amount will be rewarded with 1 point, if the sales amount is not USD, convert to equivalent amount in USD for reward amount calculation.
  3. Reward amount will be credited into the customer account with expiry date, which is 1 year later.
  4. Points can be used for new order payment, every 1 point equivalent to USD$0.01.


Please provide the following :
  1. Flowchart or sequence UML diagram on the reward system
  2. Design MySQL database schema for this reward system
  3. PHP functions to
    a. use reward points for new order payment
    b. credit user reward points after order completion



HOW TO TEST:
------------

php src/tests.php


NOTE: This is the coding pattern using PHP 8, please use the engine of PHP 8.0 or above to execute it.

NOTE: The DB schema definition is based on mySQL 8 standard.

NOTE: Please import the database file -- db_schema.sql to the database before you run test

NOTE: Please edit config.php and save your database user id, password, and database name before execute src/tests.php
