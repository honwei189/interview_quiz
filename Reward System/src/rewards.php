<?php
/**
 *
 * A simple class that is used to credit and redeem rewards points.
 *
 *
 */
class rewards
{
    /* A private variable that is used to store the database connection. */
    private $db;

    /* Used to store the rewards that are available to the user. */
    private $rewards = [];

    /**
     * It creates a new instance of the class.
     *
     * @param db The database connection object.
     */
    public function __construct(db $db)
    {
        $this->db = $db;
    }

    /**
     * Credit new reward points to a user.
     *
     * @param int $user_id The user ID of the user who is being credited.
     * @param int $order_id The order ID of the order that the user is being rewarded for.
     * @param string $currency The currency code of the order.
     * @param float $total_amount The total amount of the order
     *
     * @return array An array that contains the result of the operation.
     */
    public function credit(int $user_id, int $order_id, string $currency = "USD", float $total_amount = 0)
    {
        $amount = $this->get_usd_amount($currency, $total_amount);
        $points = (int) ($amount / 1);

        if ($this->db->fill([
            'user_id'     => $user_id,
            'order_id'    => $order_id,
            'points'      => $points,
            'redeemed'    => 0,
            'available'   => $points,
            'is_redeemed' => 0,
            'expiry_at'   => date("Y-m-d H:i:s", strtotime("+1 year")),
            'created_at'  => date("Y-m-d H:i:s"),
            'created_by'  => ($_SESSION['user_id'] ?? "999999"),
        ])->debug(false)->insert('rewards')) {
            return ["success" => "$points reward points successfully credited."];
        } else {
            return ["error" => "Failed to credit reward points."];
        }
    }

    /**
     * It checks if the user has enough points to redeem the amount of money they want to redeem
     * and return total amount that can be redeemed.
     *
     * @param int $user_id The user's ID
     * @param int $points The total of points that the user wants to redeem into how many USD
     *
     * @return array Total redeemable amount and total points that can be redeemed.
     */
    public function check_can_redeem(int $user_id, int $points = null)
    {
        // * @param string $currency The currency of the total amount.
        // * @param float $total_amount The total amount of the order
        // , string $currency = "USD", float $total_amount = 0

        if (count($this->rewards) == 0) {
            $rewards = $this->get_available_points($user_id);
        } else {
            $rewards = $this->rewards;
        }

        if ($points > 0) {
            list($avail_points) = array_values($this->check_can_redeem($user_id));

            if ($points == 0) {
                return ["error" => "No rewards available"];
            }

            if ($points > $avail_points) {
                return ["error" => "You do not have enough points to redeem"];
            }

            return ['redeem' => $points, 'amount' => $points * 0.01];
        }

        $total         = array_sum($rewards);
        $redeem_amount = $total * 0.01;

        return ['redeem' => $total, 'amount' => $redeem_amount];

        // $amount = $this->get_usd_amount($currency, $total_amount) * 0.01;
        // $points = (int) ($amount / 1);

        // if ($total > $points) {
        //     return number_format(($total - $points), 2, '.', '');
        // } else {
        //     return number_format($total, 2, '.', '');
        // }
    }

    /**
     * It takes a currency and an amount and returns the amount in USD
     *
     * @param string $currency The currency you want to convert to USD.
     * @param float $amount The amount of the transaction in the currency of the transaction.
     *
     * @return float the amount in USD.
     */
    public function get_usd_amount(string $currency, float $amount)
    {
        if (strtolower(trim($currency)) == "usd") {
            return $amount;
        }

        ob_start();
        header('Content-type: application/json');

        //lets add our API key to the request
        $request['api_key'] = 'b028bbef6dfd4866b682ba29ed4175f2';
        $request['base']    = strtoupper(trim($currency));
        $request['target']  = 'USD';

        $url   = "https://exchange-rates.abstractapi.com/v1/live/";
        $final = $url . "?" . http_build_query($request);

        //open connection
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL            => $final,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => '',
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => 'GET',
            CURLOPT_HTTPHEADER     => array(
                'Content-Type: application/json',
                'Accept: application/json',
            ),
        ));

        //Make the API call
        $response = json_decode(curl_exec($curl), true);
        $error    = curl_error($curl);

        curl_close($curl);
        ob_end_clean();

        if ($error) {
            echo json_encode(array('status' => false, 'message' => $error));
            exit;
        }

        return (float) ($response['exchange_rates']['USD'] ?? 0) * $amount;
    }

    /**
     * It takes a user_id, order_id, currency, and total_amount, and then checks if the user has enough
     * points to redeem, and if so, it updates the database with the new values
     *
     * @param int $user_id The user's ID
     * @param int $order_id The order ID of the order that the user is redeeming points for.
     *
     * @return array
     */
    public function redeem(int $user_id, int $order_id, int $redeem_points): array
    {
        // * @param string $currency The currency of the order.
        // * @param float $total_amount The total amount of the order
        // , string $currency = "USD", float $total_amount = 0

        if (count($this->rewards) == 0) {
            $rewards = $this->get_available_points($user_id);
        } else {
            $rewards = $this->rewards;
        }

        list($points, $amount) = array_values($this->check_can_redeem($user_id, $redeem_points));
        // $amount = $this->check_can_redeem($user_id)['amount'];

        if ($points == 0) {
            return ["error" => "No rewards available"];
        }

        if ($redeem_points > $points) {
            return ["error" => "You do not have enough points to redeem"];
        }

        // $points = (int) ($amount / 1);
        $points = $redeem_points;

        foreach ($rewards as $k => $v) {
            // Start redeem from all rewards and old rewards points will be redeemed first
            if ($v >= $points) {
                $this->db->where('id', $k);
                $this->db->debug(false)->update('rewards',
                    [
                        'is_redeemed' => 1,
                        'redeemed'    => $points,
                        'available'   => $v - $points,
                        'updated_at'  => date("Y-m-d H:i:s"),
                        'updated_by'  => ($_SESSION['user_id'] ?? "999999"),
                    ]
                ); // Use this reward ID to redeem points. If not all redeemed and there are remaining points available, they will be redeemed for the next order

                $this->db->fill([
                    'user_id'    => $user_id,
                    'order_id'   => $order_id,
                    'rewards_id' => $k,
                    'redeemed'   => $points,
                    'available'  => $v - $points,
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => ($_SESSION['user_id'] ?? "999999"),
                ])->debug(false)->insert('redeemed_logs');

                $points -= $v;
                break;
            } else {
                // if the redeem points are greater than the available reward points (collected from previous orders.  Check from each order's rewards)
                $this->db->where('id', $k);
                $this->db->debug(false)->update('rewards',
                    [
                        'is_redeemed' => 1,
                        'redeemed'    => $v,
                        'available'   => 0,
                        'updated_at'  => date("Y-m-d H:i:s"),
                        'updated_by'  => ($_SESSION['user_id'] ?? "999999"),
                    ]
                ); // Redeem all the points from this reward id and then find to next reward id and redeem remaining points

                $this->db->fill([
                    'user_id'    => $user_id,
                    'order_id'   => $order_id,
                    'rewards_id' => $k,
                    'redeemed'   => $v,
                    'available'  => ($rewards[$k] - $v),
                    'created_at' => date("Y-m-d H:i:s"),
                    'created_by' => ($_SESSION['user_id'] ?? "999999"),
                ])->debug(false)->insert('redeemed_logs');

                $points -= $v;
            }
        }

        return ["success" => "Redeemed USD $amount"];
    }

    /**
     * Scan all available rewards points from user
     *
     * @param int $user_id The user's ID
     *
     * @return array An array of available rewards.
     */
    private function get_available_points(int $user_id)
    {
        $find = $this->db->select('id, available')
            ->from('rewards')
            ->where('user_id', $user_id)
            ->where('available', '>', 0)
            ->where('expiry_at', '>=', date("Y-m-d H:i:s"))
            ->order_by('expiry_at', 'asc')
            ->debug(false)->search();

        $rewards = [];

        if (is_array($find) && count($find) > 0) {
            foreach ($find as $v) {
                $rewards[$v['id']] = $v['available'];
            }

            if (array_sum($rewards) == 0) {
                return ["error" => "No rewards available"];
            }

            $this->rewards = $rewards;
            unset($rewards);

            return $this->rewards;
        }

        return [];
    }
}
