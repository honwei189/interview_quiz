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


NOTE: This is using PHP 8 coding pattern, please use PHP 8.0 or above engine to execute it.

NOTE: This is using mySQL 8

NOTE: Please import schema.sql into your DB

NOTE: Please edit config.php, to save your DB userid, password and DB name