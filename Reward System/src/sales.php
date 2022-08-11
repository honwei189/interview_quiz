<?php
/**
 * It's a simple class that handles payments and redeem, credit rewards points
 *
 */
class sales
{
    private $db;
    private $rewards;

    public function __construct($db)
    {
        $this->db      = $db;
        $this->rewards = new rewards($db);
    }

    /**
     * Create payment record.  If successful, redeem rewards points and credit new points to the user
     *
     * @param int user_id The user's ID
     * @param int order_id The order ID
     * @param string currency The currency code of the payment.
     * @param float amount The amount of the payment
     * @param int redeem_points The amount of points the user wants to redeem.
     */
    public function pay(int $user_id, int $order_id, string $currency, float $amount, int $redeem_points = 0)
    {
        $points        = $redeem_points;
        $redeem_amount = 0;

        if ($redeem_points > 0) {
            $check = $this->rewards->check_can_redeem($user_id, $redeem_points);

            if (!isset($check['error'])) {
                $points        = $check['redeem'];
                $redeem_amount = $check['amount'];
            } else {
                print_r($check);
                exit;
            }
        }

        $usd_amount = $this->rewards->get_usd_amount($currency, $amount);
        $usd_amount = number_format($usd_amount, 2, '.', '');

        if ($this->db->from("payments")->fill([
            'user_id'           => $user_id,
            'order_id'          => $order_id,
            'currency'          => $currency,
            'base_amount'       => $amount,
            'usd_amount'        => $usd_amount,
            'redeemed_currency' => "USD",
            'redeemed_points'   => $points,
            'redeemed_amount'   => $redeem_amount,
            'created_by'        => ($_SESSION['user_id'] ?? "999999"),
        ])->debug(false)->insert()) {

            echo PHP_EOL;
            echo "Order ID $order_id (original amount $currency $amount)";

            if ($redeem_points > 0) {
                $amount = $amount - $redeem_amount;
            }

            echo " has paid successfully with amount USD $" . number_format($usd_amount, 2, ".", ",") . " - USD $" . number_format($redeem_amount, 2, ".", ",") . "\n";

            echo PHP_EOL;

            print_r($this->rewards->redeem($user_id, $order_id, $points));
            echo PHP_EOL;

            print_r($this->rewards->credit($user_id, $order_id, $currency, $amount));

            return true;
        } else {
            return false;
        }
    }
}
