CREATE TABLE auth (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    shop VARCHAR(220),
    code VARCHAR(220),
    hmac VARCHAR(220),
    signature VARCHAR(220),
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);        

        
CREATE TABLE postal_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10),
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);        

CREATE TABLE shipping_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rate DOUBLE DEFAULT '0.00',
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);        
       
CREATE TABLE postal_codes_shipping_rates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    postal_code_id INT(11),
    rate_id INT(11)
);        
       
CREATE TABLE carrier_services_postal_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    postal_code_id INT(11),
    carrier_service_id INT(11)
);        
        
CREATE TABLE carrier_services (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(220),
    callback_url VARCHAR(220),
    active_yn TINYINT(1) DEFAULT '1',
    created DATETIME DEFAULT NULL,
    modified DATETIME DEFAULT NULL
);        
        
