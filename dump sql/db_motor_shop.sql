CREATE TABLE IF NOT EXISTS users
(
    email VARCHAR(100) NOT NULL,
    shipping_address_id INT,
    name VARCHAR(100),
    surname varchar(100),
    password VARCHAR(32),
    streetAddress VARCHAR(200),
    phone  VARCHAR(10),
    cap INT,
    city VARCHAR(100),
    PRIMARY KEY(email)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS users_has_a_group
(
    users_email VARCHAR(100) NOT NULL,
    group_id INT,
    PRIMARY KEY(users_email)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `group`
(
    id INT NOT NULL,
   name VARCHAR(50),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS services
(
    id INT NOT NULL,
    script varchar(50),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS services_has_a_group
(
    services_id INT NOT NULL,
    group_id INT,
    PRIMARY KEY(services_id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS Products
(
    id INT NOT NULL,
    `column` INT,
    services_id INT,
    name VARCHAR(150),
    description VARCHAR(2000),
    quantity INT,
    availability TINYINT(1),
    imageSrc LONGBLOB,
    size VARCHAR(5),
    color VARCHAR(25),
    specification VARCHAR(2000),
    information VARCHAR(2000),
    tags_id INT,
    category_id INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS Orders
(
    id INT NOT NULL,
    services_id INT,
    users_email VARCHAR(100),
    Products_id INT,
    shipping_address_id INT,
    totalPrice DOUBLE,
    quantityProduct INT,
    state VARCHAR(25),
    paymentMethod VARCHAR(25),
    date DATE,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS shipping_address
(
    id INT NOT NULL,
    services_id INT,
    Orders_id INT,
    users_email varchar(50),
    name VARCHAR(150),
    surname VARCHAR(150),
    city VARCHAR(100),
    streetAddress VARCHAR(200),
    cap INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS Feedback
(
    id INT NOT NULL,
    users_email VARCHAR(100),
    Products_id INT,
    services_id INT,
    rate INT,
    review VARCHAR(1000),
    mediumRate INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS tags
(
    id INT NOT NULL,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS category
(
    id INT NOT NULL,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


# Create FKs
ALTER TABLE users_has_a_group
    ADD    FOREIGN KEY (users_email)
        REFERENCES users(email)
;

ALTER TABLE users_has_a_group
    ADD    FOREIGN KEY (group_id)
        REFERENCES `group`(id)
;

ALTER TABLE services_has_a_group
    ADD    FOREIGN KEY (services_id)
        REFERENCES services(id)
;

ALTER TABLE services_has_a_group
    ADD    FOREIGN KEY (group_id)
        REFERENCES `group`(id)
;

ALTER TABLE Products
    ADD    FOREIGN KEY (services_id)
        REFERENCES services(id)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (users_email)
        REFERENCES users(email)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (Products_id)
        REFERENCES Products(id)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (services_id)
        REFERENCES services(id)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (users_email)
        REFERENCES users(email)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (Products_id)
        REFERENCES Products(id)
;

ALTER TABLE shipping_address
    ADD    FOREIGN KEY (services_id)
        REFERENCES services(id)
;

ALTER TABLE shipping_address
    ADD    FOREIGN KEY (Orders_id)
        REFERENCES Orders(id)
;

ALTER TABLE shipping_address
    ADD    FOREIGN KEY (users_email)
        REFERENCES users(email)
;

ALTER TABLE Orders
    ADD    FOREIGN KEY (shipping_address_id)
        REFERENCES shipping_address(id)
;

ALTER TABLE users
    ADD    FOREIGN KEY (shipping_address_id)
        REFERENCES shipping_address(id)
;

ALTER TABLE Feedback
    ADD    FOREIGN KEY (users_email)
        REFERENCES users(email)
;

ALTER TABLE Feedback
    ADD    FOREIGN KEY (Products_id)
        REFERENCES Products(id)
;

ALTER TABLE Feedback
    ADD    FOREIGN KEY (services_id)
        REFERENCES services(id)
;

ALTER TABLE Products
    ADD    FOREIGN KEY (tags_id)
        REFERENCES tags(id)
;

ALTER TABLE Products
    ADD    FOREIGN KEY (category_id)
        REFERENCES category(id)
;

INSERT INTO users (email, shipping_address_id, name,surname, password, streetAddress, phone, cap, city) VALUE
    ('admin@gmail.com',NULL,'luigi','visconti','admin','via genova 48','3921346140',67100,'L`aquila');



INSERT INTO `group` (`id`, name) VALUES(1, 'Admin'),
                                     (2, 'Customer');


INSERT INTO `services` (`id`, `script`) VALUES (1, 'dashboard.php'),(2,'login.php'),(3, 'logout.php'),(4,'register.php'),(5,'adminRegister.php');

INSERT INTO `users_has_a_group` (`users_email`, `group_id`) VALUE ('admin@gmail.com', 1);

INSERT INTO `services_has_a_group` (services_id, group_id) VALUES
                                                               (1, 1),
                                                               (2, 1),
                                                               (3, 1),
                                                               (5, 1);
CREATE SCHEMA `db_motorshop` DEFAULT CHARACTER SET utf8 ;