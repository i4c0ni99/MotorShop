CREATE DATABASE IF NOT EXISTS db_motorShop;

USE db_motorShop;

CREATE TABLE IF NOT EXISTS users
(
    email VARCHAR(100) NOT NULL,
    shipping_address_id INT,
    name VARCHAR(100),
    surname varchar(100),
    password VARCHAR(32),
    phone  VARCHAR(10),
    PRIMARY KEY(email)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS users_has_groups
(
    users_email VARCHAR(100) NOT NULL,
    groups_id INT
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS groups
(
    id INT NOT NULL,
    roul VARCHAR(50),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS services
(
    id INT NOT NULL,
    category VARCHAR(100),
    description VARCHAR(200),
    script VARCHAR(200),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS groups_has_services
(
    services_id INT NOT NULL,
    groups_id INT NOT NULL
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS products
(
    id INT NOT NULL,
    name VARCHAR(150),
    description VARCHAR(2000),
    quantity INT,
    availability BOOLEAN,
	specification VARCHAR(2000),
    information VARCHAR(2000),
	mediumRate INT,
	categories_id INT,
    images_id INT,
    sizes_id INT,
	colours_id INT,
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
    state VARCHAR(25),
    paymentMethod VARCHAR(25),
	details VARCHAR(200),
    date DATE,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS shipping_address
(
    id INT NOT NULL,
    orders_id INT,
    users_email varchar(50),
    name VARCHAR(150),
    surname VARCHAR(150),
	phone VARCHAR(10),
	province VARCHAR(100),
    city VARCHAR(100),
    streetAddress VARCHAR(200),
    cap INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS feedbacks
(
    id INT NOT NULL,
    users_email VARCHAR(100),
    products_id INT,
    rate INT,
    review VARCHAR(1000),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS categories
(
    id INT NOT NULL,
	name VARCHAR(25),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS subcategories
(
    id INT NOT NULL,
	name VARCHAR(25),
	categories_id INT,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS offers
(
    id INT NOT NULL,
    categories_id INT,
    products_id INT,
    activation_date DATE,
    expiration DATE,
    percentage INTEGER,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS sizes
(
    id INT NOT NULL,
    quantity INT,
    availability BOOLEAN,
    products_id INTEGER,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS colors
(
    id INT NOT NULL,
    sizes_id INT,
    images_id INT,
    color VARCHAR(50),
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS images
(
    id INT NOT NULL,
    products_id INT,
    imageSrc LONGBLOB,
    PRIMARY KEY(id)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE users_has_groups
    ADD    FOREIGN KEY (users_email)
    REFERENCES users(email)
;

ALTER TABLE users_has_groups
    ADD    FOREIGN KEY (groups_id)
    REFERENCES groups(id)
;
    
ALTER TABLE groups_has_services
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;
    
ALTER TABLE groups_has_services
    ADD    FOREIGN KEY (groups_id)
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
    ADD    FOREIGN KEY (orders_id)
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
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE products
    ADD    FOREIGN KEY (categories_id)
    REFERENCES categories(id)
;

ALTER TABLE subcategories
    ADD    FOREIGN KEY (categories_id)
    REFERENCES categories(id)
;
    
ALTER TABLE offers
    ADD    FOREIGN KEY (categories_id)
    REFERENCES categories(id)
;
    
ALTER TABLE offers
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE orders_has_products
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;

ALTER TABLE groups_has_services
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;

ALTER TABLE groups_has_services
    ADD    FOREIGN KEY (groups_id)
    REFERENCES groups(id)
;

ALTER TABLE sizes
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;

ALTER TABLE images
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;

ALTER TABLE colors
    ADD    FOREIGN KEY (images_id)
    REFERENCES images(id)
;

INSERT INTO users (`email`, `shipping_address_id`, `name`, `surname`, `password`, `phone`) VALUE
    ( 'admin@gmail.com', NULL, 'Luigi', 'Visconti','admin', '3921346140');

INSERT INTO `groups` (`id`, `roul`) VALUES(1, 'Admin'),
                                     (2, 'Customer');


INSERT INTO `services` (`id`, `category`, `description`, `script`) VALUES (1, 'home', 'dashboard admin', 'dashboard.php'), 
(2, 'auth', 'login customer-admin', 'login.php'), 
(3, 'auth', 'logout customer-admin', 'logout.php'), 
(4, 'auth', 'register customer', 'register.php'), 
(5, 'auth', 'register admin', 'create-user-admin.php'),
(6,'list','user list','user-list.php'),
(7,'home','customer home','index.php');

INSERT INTO `users_has_groups` ( `users_email`, `groups_id`) VALUE ('admin@gmail.com', 1);

INSERT INTO `groups_has_services` ( `services_id`, `groups_id`) VALUES (1, 1),(3,1),(5,1),(2,2),(3,2),(4,2),(6,1),(7,2),(2,1);