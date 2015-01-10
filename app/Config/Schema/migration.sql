DROP TABLE IF EXISTS apis;
CREATE TABLE IF NOT EXISTS apis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop VARCHAR(220) NOT NULL,
    access_token VARCHAR(220) NOT NULL,
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

# ALTER TABLE `apis` ADD COLUMN `access_token` VARCHAR(220) NOT NULL;

DROP TABLE IF EXISTS shipping_methods;
CREATE TABLE shipping_methods (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(10),
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

DROP TABLE IF EXISTS postal_codes;
CREATE TABLE postal_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10),
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

DROP TABLE IF EXISTS shipping_rates;
CREATE TABLE shipping_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate DOUBLE DEFAULT '0.00',
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);

DROP TABLE IF EXISTS postal_codes_shipping_rates;
CREATE TABLE postal_codes_shipping_rates (
    #id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    postal_code_id INT(11) NOT NULL,
    rate_id INT(11) NOT NULL,
    PRIMARY KEY (`postal_code_id`,`rate_id`)
);

DROP TABLE IF EXISTS shipping_methods_postal_codes;
CREATE TABLE shipping_methods_postal_codes (
    #id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    postal_code_id INT(11),
    shipping_method_id INT(11),
    PRIMARY KEY (`postal_code_id`,`shipping_method_id`)
);

DROP TABLE IF EXISTS carrier_services;
CREATE TABLE carrier_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(220),
    callback_url VARCHAR(220),
    active_yn TINYINT(1) DEFAULT '1',
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);        
        
