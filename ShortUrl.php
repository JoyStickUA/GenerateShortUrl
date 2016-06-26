<?php

class ShortUrl
{
    protected static $chars = "123456789bcdfghjkmnpqrstvwxyzBCDFGHJKLMNPQRSTVWXYZ";
    protected static $table = "short_urls";
    protected static $checkUrlExists = true;

    protected $pdo;
    protected $timestamp;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->timestamp = $_SERVER["REQUEST_TIME"];
    }

    public function urlToShortCode($url, $time)
    {
        if (empty($url)) {
            throw new \Exception("Не получен адрес URL.");
        }

        if (empty($time)) {
            $time = 0;
        }

        if ($this->validateUrlFormat($url) == false) {
            throw new \Exception(
                "Адрес URL имеет неправильный формат.");
        }

        if ($this->validateLifeFormat($time) == false) {
            throw new \Exception(
                "Время жизни URL имеет неправильный формат.");
        }

        if (self::$checkUrlExists) {
            if (!$this->verifyUrlExists($url)) {
                throw new \Exception(
                    "Адрес URL не существует.");
            }
        }

        $shortCode = $this->urlExistsInDb($url);
        if ($time != 0 || $shortCode == false) {
            $shortCode = $this->createShortCode($url, $time);
        }

        return $shortCode;
    }

    protected function validateUrlFormat($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL,
            FILTER_FLAG_HOST_REQUIRED);
    }

    protected function validateLifeFormat($time)
    {
        return filter_var(preg_match("/^[0-9]+$/", $time));
    }

    protected function verifyUrlExists($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return (!empty($response) && $response != 404);
    }

    protected function urlExistsInDb($url)
    {
        $query = "SELECT short_code, date_created, life_url FROM " . self::$table .
            " WHERE long_url = :long_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "long_url" => $url
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return !(empty($result['short_code']) || $result['short_created'] != $result['life_url']) ? false : $result["short_code"];
    }

    protected function createShortCode($url, $time)
    {
        $id = $this->insertUrlInDb($url);
        $shortCode = $this->convertIntToShortCode($id);
        $this->insertShortCodeInDb($id, $shortCode);
        $this->LifeUrl($id, $time);
        return $shortCode;
    }

    protected function insertUrlInDb($url)
    {
        $query = "INSERT INTO " . self::$table .
            " (long_url, date_created) " .
            " VALUES (:long_url, :timestamp)";
        $stmnt = $this->pdo->prepare($query);
        $params = array(
            "long_url" => $url,
            "timestamp" => $this->timestamp
        );
        $stmnt->execute($params);

        return $this->pdo->lastInsertId();
    }

    protected function convertIntToShortCode($id)
    {
        $id = intval($id);
        if ($id < 1) {
            throw new \Exception(
                "ID не является некорректным целым числом.");
        }

        $length = strlen(self::$chars);
        // Проверяем, что длина строки
        // больше минимума - она должна быть
        // больше 10 символов
        if ($length < 10) {
            throw new \Exception("Длина строки мала");
        }

        $code = "";
        while ($id > $length - 1) {
            // Определяем значение следующего символа
            // в коде и подготавливаем его
            $code = self::$chars[fmod($id, $length)] .
                $code;
            // Сбрасываем $id до оставшегося значения для конвертации
            $id = floor($id / $length);
        }

        // Оставшееся значение $id меньше, чем
        // длина self::$chars
        $code = self::$chars[$id] . $code;
        return $code;
    }

    protected function insertShortCodeInDb($id, $code)
    {
        if ($id == null || $code == null) {
            throw new \Exception("Параметры ввода неправильные.");
        }
        $query = "UPDATE " . self::$table .
            " SET short_code = :short_code WHERE id = :id";
        $stmnt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code,
            "id" => $id
        );
        $stmnt->execute($params);

        if ($stmnt->rowCount() < 1) {
            throw new \Exception(
                "Строка не обновляется коротким кодом.");
        }

        return true;
    }

    protected function LifeUrl($id, $time)
    {
        $query = "UPDATE " . self::$table .
            " SET life_url = :life_url WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "id" => $id,
            "life_url" => $this->timestamp + 60 * 60 * $time
        );
        $stmt->execute($params);
    }

//    ----------------------------------------------------------------------------------------------------------------------
    public function createPrivateShortCode($url, $private_url, $time)
    {
        if (empty($url)) {
            throw new \Exception("Не получен адрес URL.");
        }

        if (empty($time)) {
            $time = 0;
        }

        if (empty($private_url)) {
            throw new \Exception("Не получен короткий адрес URL.");
        }

        if ($this->validateUrlFormat($url) == false) {
            throw new \Exception(
                "Адрес URL имеет неправильный формат.");
        }

        if ($this->validateLifeFormat($time) == false) {
            throw new \Exception(
                "Время жизни URL имеет неправильный формат.");
        }

        if (self::$checkUrlExists) {
            if (!$this->verifyUrlExists($url)) {
                throw new \Exception(
                    "Адрес URL не существует.");
            }
        }

        $count = $this->compareUrl($private_url);
        if($count>0){
            throw new \Exception(
                "Адрес URL существует.");
        }
        $shortCode = $this->privateUrlExistsInDb($url, $private_url);
        if ($time != 0 || $shortCode == false) {
            $shortCode = $this->createPrivateShortUrl($url, $private_url, $time);
        }
        return $shortCode;
    }

    protected function compareUrl($private_url)
    {
        $query = "SELECT COUNT(*) FROM " . self::$table .
            " WHERE short_code = :short_code " .
            " OR private_url = :private_url";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $private_url,
            "private_url" => $private_url
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return $result["COUNT(*)"];
    }

    protected function privateUrlExistsInDb($url, $private_url)
    {
        $query = "SELECT private_url, date_created, life_url FROM " . self::$table .
            " WHERE long_url = :long_url " .
            " AND private_url = :private_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "long_url" => $url,
            "private_url" => $private_url
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return !(empty($result['private_url']) || $result['short_created'] != $result['life_url']) ? false : $result["private_url"];
    }

    protected function createPrivateShortUrl($url, $private_url, $time)
    {
        $id = $this->insertUrlInDb($url);
        $shortCode = $private_url;
        $this->insertPrivateShortCodeInDb($id, $shortCode);
        $this->LifeUrl($id, $time);
        return $shortCode;
    }

    protected function insertPrivateShortCodeInDb($id, $private_url)
    {
        if ($id == null || $private_url == null) {
            throw new \Exception("Параметры ввода неправильные.");
        }
        $query = "UPDATE " . self::$table .
            " SET private_url = :private_url WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "private_url" => $private_url,
            "id" => $id
        );
        $stmt->execute($params);

        if ($stmt->rowCount() < 1) {
            throw new \Exception(
                "Строка не обновляется коротким кодом.");
        }

        return true;
    }

//    ----------------------------------------------------------------------------------------------------------------------
    public function shortCodeToUrl($code, $increment = true)
    {
        if (empty($code)) {
            throw new \Exception("Не задан короткий код.");
        }

        if ($this->validateLifeShortCode($code) == false) {
            throw new \Exception(
                "Истек срок жизни URL");
        }

        if ($this->validateShortCode($code) == false) {
            throw new \Exception(
                "Короткий код имеет неправильный формат.");
        }

        $urlRow = $this->getUrlFromDb($code);
        if (empty($urlRow)) {
            throw new \Exception(
                "Короткий код не содержится в базе.");
        }

        if ($increment == true) {
            $this->incrementCounter($urlRow["id"]);
        }

        return $urlRow["long_url"];
    }

    protected function validateLifeShortCode($code)
    {
        $query = "SELECT date_created, life_url FROM " . self::$table .
            " WHERE short_code = :short_code " .
            "OR private_url = :private_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code,
            "private_url" => $code
        );
        $stmt->execute($params);
        $result = $stmt->fetch();
        if ($this->timestamp < $result['life_url']) {
            return true;
        } elseif ($result['life_url'] == $result['date_created']) {
            return true;
        } else {
            return false;
        }
    }

    protected function validateShortCode($code)
    {
        return preg_match("|[" . self::$chars . "]+|", $code);
    }

    protected function getUrlFromDb($code)
    {
        $query = "SELECT id, long_url FROM " . self::$table .
            " WHERE BINARY short_code = :short_code " .
            "OR private_url = :private_url LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code,
            "private_url" => $code
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result;
    }

    protected function incrementCounter($id)
    {
        $query = "UPDATE " . self::$table .
            " SET counter = counter + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "id" => $id
        );
        $stmt->execute($params);
    }

    public function shortCounter($code)
    {
        $query = "SELECT counter FROM " . self::$table .
            " WHERE short_code = :short_code LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code
        );
        $stmt->execute($params);

        $result = $stmt->fetch();
        return (empty($result)) ? false : $result;
    }
}
