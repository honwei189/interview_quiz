<?php
include "config.php";
include "db.php";
include "sales.php";
include "rewards.php";

$db = new db(
    DB_PROFILE['server'],
    DB_PROFILE['port'],
    DB_PROFILE['username'],
    DB_PROFILE['password']
);
$db->selectdb(DB_PROFILE['dbname']);

// print_r($db->from("orders")->search());

$r     = new rewards($db);
$sales = new sales($db);

/* Checking if the user can redeem the rewards for user id = 1 */
// print_r($r->check_can_redeem(1));

/*
Checking if the user can redeem the reward and convert to how many USD before place an order and make the payment.
If redeem points is over than available points, then it will return error message = You do not have enough points to redeem
 */
// print_r($r->check_can_redeem(1, 130));
// print_r($r->check_can_redeem(1, 30));

/* A function to make the payment for the order for user id = 1 and order id = 10 */

$order_status = "Complete";

if (trim(strtolower($order_status)) == "complete") {
    $user_id  = 1;
    $order_id = 1334;

    $sales->pay($user_id, $order_id, "MYR", 50, 35);
}

/* Redeeming the reward for user id = 1, reward id = 2, and redeem points = 40. */
// print_r($r->redeem(1, 2, 40));

/* Crediting points for user id = 1 and order id = 10 */
// $r->credit(1, 10, "MYR", 50);
