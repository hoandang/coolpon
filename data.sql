use coolpon;

TRUNCATE TABLE machines;
TRUNCATE TABLE coupons;

CREATE TABLE IF NOT EXISTS machines (
    id INTEGER NOT NULL AUTO_INCREMENT,
    name VARCHAR(20) NOT NULL,
    post_code INTEGER NOT NULL,
    address VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS coupons (
    id INTEGER NOT NULL AUTO_INCREMENT,
    machine_id INTEGER NOT NULL,
    name VARCHAR(20) NOT NULL,
    description TEXT NOT NULL,
    image TEXT NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (machine_id) REFERENCES machines(id)
) ENGINE=MYISAM DEFAULT CHARSET=UTF8;

INSERT INTO machines(name, post_code, address) VALUES
("Machine 1", 2007, "15 Broadway Ultimo"),
("Machine 2", 2000, "100 Market St"),
("Machine 3", 2000, "680 George St");

INSERT INTO coupons(machine_id, name, description, image) VALUES
(1, 'Coupon 1', 'This is coupon 1', 'Image 1'),
(1, 'Coupon 2', 'This is coupon 2', 'Image 2'),
(1, 'Coupon 3', 'This is coupon 3', 'Image 3'),
(2, 'Coupon 4', 'This is coupon 4', 'Image 4'),
(2, 'Coupon 5', 'This is coupon 5', 'Image 5'),
(3, 'Coupon 6', 'This is coupon 6', 'Image 6');
