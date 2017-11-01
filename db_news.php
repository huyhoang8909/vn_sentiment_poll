<?php

require_once './config.php';

class db_news {

    private $_table = 'products';
    public static $connection = NULL;
    public $item = [
        "name" => '',
        "brand" => '',
        "price" => ''
    ];

    /**
     *
     * @return type
     */
    private function connnect() {
        if (is_null(self::$connection)) {
            self::$connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            mysqli_set_charset(self::$connection, 'utf8');
        }
        return self::$connection;
    }

    public function get($id) {

        $this->connnect();

        $sql = 'SELECT count(*) as count FROM products WHERE id=' . $id;

        $data = mysqli_query(self::$connection, $sql);

        $rows = array();

        while ($row = mysqli_fetch_assoc($data)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function query($sql) {

        $this->connnect();

        $data = mysqli_query(self::$connection, $sql);

        if (!$data) return false;

        $rows = array();

        while ($row = mysqli_fetch_assoc($data)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function insert($params) {

        $this->connnect();

        $sql = 'INSERT INTO ' . $this->_table . ' (`id`, `category`, `link`, `comment`, `num_comment`) VALUES('
                    . '' . mysqli_escape_string(self::$connection, trim(html_entity_decode($params[0]))) . ','
                    . '"' . mysqli_escape_string(self::$connection, trim($params[1])) . '",'
                    . '"' . trim($params[2]) . '",'
                    . '"' . mysqli_escape_string(self::$connection,trim($params[3])) . '",'
                    . '' . $params[4] . ')';
        // echo $sql, PHP_EOL;

        mysqli_query(self::$connection, $sql);
    }

    public function insertReview($params) {

        $this->connnect();

        $sql = 'INSERT INTO reviews (`product_id`, `category`, `link`,`rating`, `content`) VALUES('
                    . '' . $params[0] . ','
                    . '"' . $params[1] . '",'
                    . '"' . $params[2] . '",'
                    . '' . $params[3] . ','
                    . '"' . trim($params[4]) . '")';

        mysqli_query(self::$connection, $sql);
    }

    public function updateSentiment($table, $content, $sentiment) {
        $this->connnect();

        $description = $this->removeScript($description);
        $description = $this->removeTagHTML($description);

        $description = $this->removeMoreSpace($description);

        $description = trim($description);

        $sql = "UPDATE `$table` SET is_verified = 1,`sentiment` =" . $sentiment . " WHERE `content` = '".$content."';
                ";

        return mysqli_query(self::$connection, $sql);
    }

    public function updateReviewsSentiment($id, $sentiment, $column_name) {
        $this->connnect();

        $description = $this->removeScript($description);
        $description = $this->removeTagHTML($description);

        $description = $this->removeMoreSpace($description);

        $description = trim($description);

        $sql = "UPDATE `reviews` SET `$column_name` =" . $sentiment . " WHERE `id` = '".$id."';
                ";

        return mysqli_query(self::$connection, $sql);
    }

    public function updateTmpSentiment($id, $sentiment, $column_name) {
        $this->connnect();

        $description = $this->removeScript($description);
        $description = $this->removeTagHTML($description);

        $description = $this->removeMoreSpace($description);

        $description = trim($description);

        $sql = "UPDATE `tmp` SET `is_done` = 1, `$column_name` =" . $sentiment . " WHERE `id` = '".$id."';
                ";

        return mysqli_query(self::$connection, $sql);
    }

    public function update($sql) {
        $this->connnect();
        mysqli_query(self::$connection, $sql);
    }

    public function delete($params = array()) {

    }

    public function exportJson() {
        $this->connnect();

        $sql = 'SELECT item_value FROM ' . $this->_table;

        $data = mysqli_query(self::$connection, $sql);

        $items = array();

        while ($item = mysqli_fetch_assoc($data)) {

            file_put_contents('item.json', $item['item_value'] . PHP_EOL, FILE_APPEND);
        }
    }

    public function removeTagHTML($str) {

        $str = preg_replace('/<.*?>/', '', $str);
        return $str;

    }
    public function removeScript($str) {
        $str = preg_replace('/<style.*?>[\s\S]*?<\/style>/', '', $str);
        $str = preg_replace('/<script.*?>[\s\S]*?<\/script>/', '', $str);
        return $str;
    }

    public function removeMoreSpace($str) {
        $str =  preg_replace('/\s+/', ' ', $str);
        $str =  preg_replace('/\t+/', '', $str);

        $str = preg_replace('/[\r\n\t]+/', '', $str);

        return $str;

    }

}
