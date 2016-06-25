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
            " WHERE short_code = :short_code LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code
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
            " WHERE BINARY short_code = :short_code LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $params = array(
            "short_code" => $code
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
