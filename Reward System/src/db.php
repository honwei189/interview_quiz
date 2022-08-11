<?php
/* Simple class for DB operations */
class db extends PDO
{
    /* An array of columns.  It is used to store the columns that you want to select.  If you don't
    specify the columns, it will select all columns. */
    private $cols = [];

    /* Used to display the SQL statement.  If you want to display the SQL statement, you can use the
    debug() function.  e.g:

    $db = new db("localhost", 3306, "root", "password");
    $db->debug();
    $db->selectdb("test");
    $db->from("test");
    $db->where("id", 1);
    $db->search(); */
    private $debug = false;

    /* Used to store the error message from PDO / DB.  You can use the error() function to get the
    error message. */
    private $error;

    /* Used to store the columns that you want to insert */
    private $fills = [];

    /* Used to store the connection resource. */
    private $res;

    /* Used to store the database name.  It is used in the selectdb() function. */
    private $selectdb;

    /* Used to store the table name.  It is used in the from() function. */
    private $table;

    /* Used to store the WHERE conditions */
    private $where = [];

    /* Setting the ordering sequence and get data from the table by ASC or DESC */
    private $order_by;

    /**
     * This function is used to connect to the database
     *
     * @param string $server The server name or IP address of the MySQL server.
     * @param int $port The port number to connect to
     * @param string $username DB username
     * @param string $password The password for the DB user
     * @param string $db The name of the database to connect to
     */
    public function __construct(?string $server, ?int $port, ?string $username, ?string $password, ?string $db = null)
    {
        $this->server   = $server;
        $this->port     = $port;
        $this->username = $username;
        $this->password = $password;

        // parent::__construct($server, $username, $password, $connection);

        $this->connect($server, $port, $username, $password);
    }

    /**
     * This function is used to connect to the database
     *
     * @param string $server The server name or IP address of the MySQL server.
     * @param int $port The port number to connect to
     * @param string $username DB username
     * @param string $password The password for the DB user
     * @param string $db The name of the database to connect to
     *
     * @return db::class
     */
    public function connect(?string $server, ?int $port, ?string $username, ?string $password, ?string $db = null)
    {
        //connect to database
        $dsn = "mysql:host=" . ($server ?? $this->server) . ";port=" . ($port ?? $this->port);

        if ($db !== null) {
            $dsn .= ';dbname=' . $db;
        }

        $this->res = new PDO($dsn, $username ?? $this->username, $password ?? $this->password);
        if ($this->res === false) {
            return false;
        } else {
            $this->res->setAttribute(PDO::ATTR_PERSISTENT, true);
            $this->res->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
            $this->res->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $this->res->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }

        return $this;
    }

    /**
     * ON / OFF display SQL
     *
     * @param bool $bool, default true
     *
     * @return db::class
     */
    public function debug(bool $bool = true)
    {
        $this->debug = $bool;

        return $this;
    }

    /**
     * It returns the error message from PDO / DB
     *
     * @return array The error message.
     */
    public function error()
    {
        return $this->error;
    }

    /**
     * To declare the table name
     *
     * @param string $table_name The name of the table you want to select from.
     *
     * @return db::class
     */
    public function from(string $table_name)
    {
        $this->table = $table_name;
        return $this;
    }

    /**
     * Alias function of fills()
     *
     * @param array|string $col The column name.  Can be array or string.  If it is an array, the format is:
     * [
     *  COLUMN_NAME => VALUE,
     *  COLUMN_NAME2 => VALUE2,
     * ]
     * @param string $value The value to be inserted
     *
     * @return db::class
     */
    public function fill(array | string $col, string $value = null)
    {
        return $this->fills($col, $value);
    }

    /**
     * It takes an array or a string and a string as parameters and returns the object.
     *
     * @param array|string $col The column name.  Can be array or string.  If it is an array, the format is:
     * [
     *  COLUMN_NAME => VALUE,
     *  COLUMN_NAME2 => VALUE2,
     * ]
     * @param string $value The value to be inserted
     *
     * @return db::class
     */
    public function fills(array | string $col, string $value = null)
    {
        if (is_array($col)) {
            foreach ($col as $key => $val) {
                $this->fills[$key] = trim($val ?? "");
            }
        } else {
            $this->fills[$col] = trim($value);
        }

        return $this;
    }

    /**
     * It's a function that select & connect to database
     *
     * @param string $db_name The name of the database you want to select.
     *
     * @return db::class
     */
    public function selectdb(string $db_name)
    {
        if ($this->res !== false) {
            // $this->selectdb[$db_name] = $this->selectdb[$db_name];
            $this->res->exec("USE $db_name");
        }

        return $this;
    }

    /**
     *
     * @param array|string $cols The table columns you want to select
     *
     * @return db::class
     */
    public function select(array | string $cols)
    {
        if (is_string($cols)) {
            $cols = explode(',', $cols);
        }

        if (is_array($cols)) {
            foreach ($cols as $col) {
                $this->cols[] = trim($col);
            }
        }

        return $this;
    }

    /**
     * To generate "WHERE" SQL statement
     *
     * @param array|string $cols Table column name
     * @param $operator_or_values Operator or column values.  e.g:
     *  Operator =, !=, >, <, >=, <=, LIKE, NOT LIKE, IN, NOT IN, BETWEEN, NOT BETWEEN, IS NULL, IS NOT NULL
     *  Values = text, number, or NULL
     *
     * @param $values Text, number, or NULL
     *
     * @return db::class
     */
    public function where(array | string $col, $operator_or_values = null, $values = null)
    {
        if (is_array($col)) {
            foreach ($col as $key => $value) {
                // $this->where($key, $value, $operator_or_values, $value
                $this->where[] = "$key = '$value'";
            }
        } else {
            if ($operator_or_values !== null && $values !== null) {
                $this->where[] = "$col $operator_or_values '$values'";
            } else {
                $this->where[] = "$col = '$operator_or_values'";
            }
        }

        return $this;
    }

    /**
     * Insert data into DB
     *
     * @param string $table_name The name of the table you want to insert into.
     *
     * @return bool The return value is a boolean.
     */
    public function insert(string $table_name = null)
    {
        if ($table_name === null) {
            if ($this->table === null || trim($this->table) === '') {
                return [
                    'status'  => false,
                    'message' => 'Table name is required.',
                ];
            } else {
                $table_name = $this->table;
            }
        }

        if ($this->debug) {
            echo "<pre>";
            echo "<b>INSERT INTO $table_name</b>" . PHP_EOL;

            foreach ($this->fills as $key => $value) {
                echo "$key \t: $value" . PHP_EOL;
            }

            echo PHP_EOL . PHP_EOL;

            echo "</pre>";

            $this->cols  = [];
            $this->from  = null;
            $this->fills = [];
            $this->where = [];

            return false;
        } else {
            $cols   = array_keys($this->fills);
            $values = array_values($this->fills);

            $sql = "insert into $table_name (" . join(", ", $cols) . ") values ('" . join("', '", $values) . "');";

            $sth = $this->res->prepare($sql);
            $res = $sth->execute();

            if ($res) {
                $this->cols  = [];
                $this->from  = null;
                $this->fills = [];
                $this->where = [];
                $this->error = null;
                return true;
            } else {
                $this->error = $this->res->errorInfo();
                return false;
            }
        }
    }

    /**
     * It updates data into DB.
     *
     * @param string $table_name The name of the table you want to update.
     * @param array|string $cols array | string
     */
    public function update(?string $table_name = null, array | string $cols = null)
    {
        // if ($cols !== null) {
        //     if (is_string($cols)) {
        //         $cols = explode(',', $cols);
        //     }

        //     if (is_array($cols)) {
        //         $c = [];
        //         foreach ($cols as $k => $v) {
        //             $c[] = "$k = '" . trim($v) . "'";
        //         }
        //     }
        // }

        if ($table_name === null) {
            if ($this->table === null || trim($this->table) === '') {
                return [
                    'status'  => false,
                    'message' => 'Table name is required.',
                ];
            } else {
                $table_name = $this->table;
            }
        }

        if ($cols !== null) {
            $this->fills($cols);
        }

        if ($this->debug) {
            echo "<pre>";
            echo "<b>UPDATE $table_name</b> set" . PHP_EOL;

            foreach ($this->fills as $key => $value) {
                echo "$key \t: $value" . PHP_EOL;
            }

            echo "WHERE " . join(" and ", $this->where) . PHP_EOL . PHP_EOL;

            echo "</pre>";

            $this->cols  = [];
            $this->from  = null;
            $this->fills = [];
            $this->where = [];

            return false;
        } else {
            $sql = "update $table_name set ";
            $c   = [];

            foreach ($this->fills as $key => $value) {
                $c[] = "$key = '$value'";
            }

            $sql .= join(", ", $c) . " where " . join(" and ", $this->where);

            $sth = $this->res->prepare($sql);
            $res = $sth->execute();

            unset($c);

            if ($res) {
                $this->cols  = [];
                $this->from  = null;
                $this->fills = [];
                $this->where = [];
                $this->error = null;
                return true;
            } else {
                $this->error = $this->res->errorInfo();
                return false;
            }
        }
    }

    /**
     * It takes the data from the database and returns it as an array.
     *
     * @return array An array of associative arrays.
     */
    public function search()
    {
        if (count($this->cols) == 0) {
            $this->cols[] = "*";
        }

        if ($this->table === null) {
            return "Unknown table";
        }

        if ($this->debug) {
            echo "<pre>";
            echo "<b>SELECT \n\t" . join(",\n\t", $this->cols) . "\nFROM $this->table</b>" . PHP_EOL;

            if ($this->where !== null && count($this->where) > 0) {
                echo "WHERE \n\t" . join("\n\t and ", $this->where) . PHP_EOL;
            }

            if ($this->order_by !== null) {
                echo $this->order_by . PHP_EOL;
            }

            echo PHP_EOL . PHP_EOL;
            echo "</pre>";

            $this->cols  = [];
            $this->from  = null;
            $this->fills = [];
            $this->where = [];
            $this->error = null;

            return false;
        } else {
            $sql = "select " . join(",", $this->cols) . " from $this->table";

            if ($this->where !== null && count($this->where) > 0) {
                $sql .= " where " . join(" and ", $this->where);
            }

            if ($this->order_by !== null) {
                $sql .= $this->order_by;
            }

            $res = $this->res->query($sql);

            $this->cols     = [];
            $this->from     = null;
            $this->fills    = [];
            $this->where    = [];
            $this->error    = null;
            $this->order_by = null;

            $data  = $res->fetchAll(PDO::FETCH_ASSOC);
            $count = $res->rowcount();

            return $data;
        }
    }

    /**
     * Create ordering sequence (ASC or DESC) for the query result
     *
     * @param string col The column name to order by
     * @param string order The column to order by.  Default is ASC
     *
     * @return The object itself.
     */
    public function order_by(string $col, string $order = "ASC")
    {
        $this->order_by = " order by $col $order";
        return $this;
    }
}
