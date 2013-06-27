CREATE DATABASE IF NOT EXISTS coolpon;
USE coolpon;

CREATE TABLE IF NOT EXISTS machines (
    id        INTEGER NOT NULL AUTO_INCREMENT,
    name      VARCHAR(255) NOT NULL,
    post_code INTEGER NOT NULL,
    address   VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS businesses (
    id          INTEGER NOT NULL AUTO_INCREMENT,
    name        VARCHAR(255) NOT NULL,
    address     VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (id)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS coupons (
    id          INTEGER NOT NULL AUTO_INCREMENT,
    machine_id  INTEGER NOT NULL,
    business_id INTEGER NOT NULL,
    name        VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image       TEXT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (machine_id) REFERENCES  machines(id),
    FOREIGN KEY (business_id) REFERENCES businesses(id)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8;

TRUNCATE TABLE coupons;
TRUNCATE TABLE machines;
TRUNCATE TABLE businesses;

INSERT INTO businesses(name, address, description) VALUES
("Business 1", "12 ABC Street NSW, Sydney", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce laoreet venenatis dapibus. Fusce sed sem nunc. Vivamus sollicitudin vitae neque in posuere"),
("Business 2", "34 OIR Street NSW, Sydney", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce laoreet venenatis dapibus. Fusce sed sem nunc. Vivamus sollicitudin vitae neque in posuere"),
("Business 3", "12 UTR Street NSW, Sydney", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce laoreet venenatis dapibus. Fusce sed sem nunc. Vivamus sollicitudin vitae neque in posuere"),
("Business 4", "12 OKKM Street NSW, Sydney", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce laoreet venenatis dapibus. Fusce sed sem nunc. Vivamus sollicitudin vitae neque in posuere"),
("Business 5", "12 GFDM Street NSW, Sydney", "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce laoreet venenatis dapibus. Fusce sed sem nunc. Vivamus sollicitudin vitae neque in posuere");

INSERT INTO machines(name, post_code, address) VALUES
("Machine 1", 2007, "15 Broadway Ultimo"),
("Machine 2", 2000, "100 Market St"),
("Machine 3", 2000, "680 George St");

INSERT INTO coupons(machine_id, business_id, name, description, image) VALUES
(1, 1, 'Coupon 1', 'This is coupon 1', 'assets/1.jpg'),
(1, 2, 'Coupon 2', 'This is coupon 2', 'assets/2.jpg'),
(1, 4, 'Coupon 3', 'This is coupon 3', 'assets/3.jpg'),
(2, 2, 'Coupon 4', 'This is coupon 4', 'assets/4.jpg'),
(2, 3, 'Coupon 5', 'This is coupon 5', 'assets/5.jpg'),
(3, 2, 'Coupon 6', 'This is coupon 6', 'assets/6.jpg'),
(3, 5, 'Coupon 7', 'This is coupon 7', 'assets/7.jpg'),
(2, 5, 'Coupon 8', 'This is coupon 4', 'assets/4.jpg');
