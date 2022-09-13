CREATE DATABASE IF NOT EXISTS db_motorShop;

USE db_motorShop;

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

CREATE TABLE IF NOT EXISTS users_has_group
(
    users_email VARCHAR(100) NOT NULL,
    group_id INT,
    PRIMARY KEY(users_email)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS groups
(
    id INT NOT NULL,
    name VARCHAR(50),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS services
(
    id INT NOT NULL,
    category VARCHAR(100),
    description VARCHAR(200),
    script VARCHAR(200),
    link VARCHAR(200),
    route VARCHAR(200),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS services_has_group
(
    services_id INT NOT NULL,
    group_id INT NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS products
(
    id INT NOT NULL,
    name VARCHAR(150),
    description VARCHAR(2000),
    quantity INT,
    availability TINYINT(1),
    imageSrc LONGBLOB,
    size VARCHAR(5),
    specification VARCHAR(2000),
    information VARCHAR(2000),
    tags_id INT,
    category_id INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
CREATE TABLE IF NOT EXISTS orders_has_products
(
    id INT NOT NULL,
    products_id INT,
    quantity INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS orders
(
    id INT NOT NULL,
    users_email VARCHAR(100),
    orders_has_products_id INT,
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
    Orders_id INT,
    users_email varchar(50),
    name VARCHAR(150),
    surname VARCHAR(150),
    city VARCHAR(100),
    streetAddress VARCHAR(200),
    cap INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS feedbacks
(
    id INT NOT NULL,
    users_email VARCHAR(100),
    Products_id INT,
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

CREATE TABLE IF NOT EXISTS categorys
(
    id INT NOT NULL,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS offers
(
    id INT NOT NULL,
    category_id INT,
    products_id INT,
    activation_date DATE,
    expiration DATE,
    percentage INTEGER,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;



ALTER TABLE users_has_group
    ADD    FOREIGN KEY (users_email)
    REFERENCES users(email)change md5 in php storm
;
    
ALTER TABLE services_has_group
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;
    
ALTER TABLE services_has_group
    ADD    FOREIGN KEY (group_id)
    REFERENCES groups(id)
;
     
    
    
    
ALTER TABLE orders
    ADD    FOREIGN KEY (users_email)
    REFERENCES users(email)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (orders_has_products_id )
    REFERENCES orders_has_products(id)
;
    
    
ALTER TABLE shipping_address
    ADD    FOREIGN KEY (Orders_id)
    REFERENCES orders(id)
;
    
ALTER TABLE shipping_address
    ADD    FOREIGN KEY (users_email)
    REFERENCES users(email)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (shipping_address_id)
    REFERENCES shipping_address(id)
;
    
ALTER TABLE users
    ADD    FOREIGN KEY (shipping_address_id)
    REFERENCES shipping_address(id)
;
    
ALTER TABLE feedbacks
    ADD    FOREIGN KEY (users_email)
    REFERENCES users(email)
;
    
ALTER TABLE feedbacks
    ADD    FOREIGN KEY (Products_id)
    REFERENCES products(id)
;
    
    
ALTER TABLE products
    ADD    FOREIGN KEY (category_id)
    REFERENCES categorys(id)
;
    
ALTER TABLE offers
    ADD    FOREIGN KEY (category_id)
    REFERENCES categorys(id)
;
    
ALTER TABLE offers
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE orders_has_products
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
    


INSERT INTO users (email, shipping_address_id, name,surname, password, streetAddress, phone, cap, city) VALUE
    ('admin@gmail.com',NULL,'luigi','visconti',MD5='admin','via genova 48','3921346140',67100,'L`aquila');



INSERT INTO `groups` (`id`, name) VALUES(1, 'Admin'),
                                     (2, 'Customer');


INSERT INTO `services` (`id`, `script`) VALUES (1, 'dashboard.php'),(2,'login.php'),(3, 'logout.php'),(4,'register.php'),(5,'adminRegister.php');

INSERT INTO `users_has_group` (`users_email`, `group_id`) VALUE ('admin@gmail.com', 1);

INSERT INTO `services_has_group` (services_id, group_id) VALUES
                                                               (6, 2);
