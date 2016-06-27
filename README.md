# GenerateShortUrl

Создание таблицы БД

CREATE TABLE IF NOT EXISTS short_urls (  
id INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,  
long_url VARCHAR(255) NOT NULL,  
short_code VARCHAR(10) NOT NULL,  
private_url VARCHAR(10) NOT NULL,  
date_created INTEGER UNSIGNED NOT NULL,  
life_url INTEGER UNSIGNED NOT NULL,  
counter INTEGER UNSIGNED NOT NULL DEFAULT '0',  

  PRIMARY KEY (id),  
  KEY short_code (short_code)  
)  
ENGINE=InnoDB;  

В файле connect.php нужно настроить подключения к базе
