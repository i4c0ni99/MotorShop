

# Create tables
CREATE TABLE IF NOT EXISTS users
(
    id INT NOT NULL,
    shipping_address_id INT,
    email VARCHAR(100),
    name VARCHAR(100),
    surname VARCHAR(100),
    email VARCHAR(100),
    password VARCHAR(32),
    INT,
    province VARCHAR(100),
    city VARCHAR(100),
    cap INT,
    streetAddress VARCHAR(200),
    phone VARCHAR(10),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS users_has_groups
(
    users_id INT NOT NULL,
    groups_id INT,
    PRIMARY KEY(users_id)
);

CREATE TABLE IF NOT EXISTS groups
(
    id INT NOT NULL,
    username INT,
    name VARCHAR(50),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS services
(
    id INT NOT NULL,
    category VARCHAR(100),
    description VARCHAR(200),
    script VARCHAR(200),
    link VARCHAR(200),
    route VARCHAR(200),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS groups_has_services
(
    services_id INT NOT NULL,
    groups_id INT,
    PRIMARY KEY(services_id)
);

CREATE TABLE IF NOT EXISTS products
(
    id INT NOT NULL,
    column INT,
    services_id INT,
    users_id INT,
    categorys_id INT,
    name VARCHAR(150),
    description VARCHAR(2000),
    quantity INT,
    availability TINYINT(1),
    imageSrc LONGBLOB,
    categoty VARCHAR(100),
    size VARCHAR(5),
    color VARCHAR(25),
    specification VARCHAR(2000),
    information VARCHAR(2000),
    tag INT,
    tags_id INT,
    category_id INT,
    date DATE,
    categorys_id INT,
    imageSrc LONGBLOB,
    mediumRate INT,
    INT,
    column2 INT,
    quantity INT,
    code VARCHAR(25),
    title VARCHAR(200),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS Orders
(
    users_id INT,
    Products_id INT,
    id INT    
);

CREATE TABLE IF NOT EXISTS orders
(
    id INT NOT NULL,
    services_id INT,
    column INT,
    users_id INT,
    products_id INT,
    shipping_address_id INT,
    totalPrice DOUBLE,
    quantityProduct INT,
    state VARCHAR(25),
    paymentMethod VARCHAR(25),
    date DATE,
    column5 INT,
    column6 INT,
    column7 INT,
    details VARCHAR(200),
    orders_has_products_id INT,
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS shipping_address
(
    id INT NOT NULL,
    services_id INT,
    Orders_id INT,
    users_id INT,
    name VARCHAR(150),
    surname VARCHAR(150),
    users_id INT,
    province VARCHAR(100),
    city VARCHAR(100),
    streetAddress VARCHAR(200),
    cap INT,
    phone VARCHAR(10),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS feedbacks
(
    id INT NOT NULL,
    users_id INT,
    Products_id INT,
    services_id INT,
    rate INT,
    review VARCHAR(1000),
    mediumRate INT,
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS categorys
(
    id INT NOT NULL,
    name VARCHAR(25),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS offers
(
    id INT NOT NULL,
    category_id INT,
    products_id INT,
    activation_date DATE,
    expiration DATE,
    percentage UNSIGNED INTEGER,
    sub-products_id INT,
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS orders_has_products
(
    id INT NOT NULL,
    services_id INT,
    column INT,
    users_id INT,
    products_id INT,
    shipping_address_id INT,
    totalPrice DOUBLE,
    quantityProduct INT,
    state VARCHAR(25),
    paymentMethod VARCHAR(25),
    date DATE,
    column5 INT,
    column6 INT,
    column7 INT,
    orders_id INT,
    products_id INT,
    products_id INT,
    orders_id INT,
    quantity INT,
    column2 INT,
    column3 INT,
    id INT NOT NULL,
    products_id INT,
    quantity INT,
    PRIMARY KEY(id, id)
);

CREATE TABLE IF NOT EXISTS images
(
    id INT NOT NULL,
    file VARCHAR(100),
    products_id INT,
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS cart
(
    users_email VARCHAR(100) NOT NULL,
    INT,
    users_id INT,
    products_id INT,
    PRIMARY KEY(users_email)
);

CREATE TABLE IF NOT EXISTS subCategories
(
    name VARCHAR(100) NOT NULL,
    categorys_id INT,
    column INT,
    PRIMARY KEY(name)
);

CREATE TABLE IF NOT EXISTS Dettagli
(
    products_id INT NOT NULL,
    column INT,
    INT,
    PRIMARY KEY(products_id)
);

CREATE TABLE IF NOT EXISTS dettagli
(
    
);

CREATE TABLE IF NOT EXISTS ``image & colors``
(
    
);

CREATE TABLE IF NOT EXISTS image
(
    id INT NOT NULL,
    products_id INT,
    colors_id INT,
    imageSrc LONGBLOB,
    products_id INT,
    sub-products_products_id INT,
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS colors
(
    id INT NOT NULL,
    image_id INT,
    size INT,
    image_id INT,
    color VARCHAR(50),
    quantity INT,
    availability TINYINT(1),
    PRIMARY KEY(id)
);

CREATE TABLE IF NOT EXISTS size
(
    size VARCHAR(5) NOT NULL,
    products_id INT,
    colors_id INT,
    size VARCHAR(5),
    quantity INT,
    availability TINYINT(1),
    products_id INT,
    sub-products_id INT,
    quantity INT,
    availability TINYINT(1),
    PRIMARY KEY(size)
);

CREATE TABLE IF NOT EXISTS sub-products
(
    id INT NOT NULL,
    products_id INT,
    imgsrc LONGBLOB,
    color VARCHAR(25),
    price FLOAT(10),
    title VARCHAR(200),
    column INT,
    PRIMARY KEY(id)
);


# Create FKs
ALTER TABLE users_has_groups
    ADD    FOREIGN KEY (users_id)
    REFERENCES users(id)
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
    
ALTER TABLE products
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;
    
ALTER TABLE Orders
    ADD    FOREIGN KEY (users_id)
    REFERENCES users(id)
;
    
ALTER TABLE Orders
    ADD    FOREIGN KEY (Products_id)
    REFERENCES products(id)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (users_id)
    REFERENCES users(id)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE shipping_address
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
;
    
ALTER TABLE shipping_address
    ADD    FOREIGN KEY (Orders_id)
    REFERENCES orders(id)
;
    
ALTER TABLE shipping_address
    ADD    FOREIGN KEY (users_id)
    REFERENCES users(id)
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
    ADD    FOREIGN KEY (users_id)
    REFERENCES users(id)
;
    
ALTER TABLE feedbacks
    ADD    FOREIGN KEY (Products_id)
    REFERENCES products(id)
;
    
ALTER TABLE feedbacks
    ADD    FOREIGN KEY (services_id)
    REFERENCES services(id)
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
    
ALTER TABLE orders_has_products
    ADD    FOREIGN KEY (orders_id)
    REFERENCES orders(id)
;
    
ALTER TABLE products
    ADD    FOREIGN KEY (categorys_id)
    REFERENCES categorys(id)
;
    
ALTER TABLE orders_has_products
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE orders
    ADD    FOREIGN KEY (orders_has_products_id)
    REFERENCES orders_has_products(id)
;
    
ALTER TABLE subCategories
    ADD    FOREIGN KEY (categorys_id)
    REFERENCES categorys(id)
;
    
ALTER TABLE Dettagli
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE image
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE size
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE colors
    ADD    FOREIGN KEY (image_id)
    REFERENCES image(id)
;
    
ALTER TABLE size
    ADD    FOREIGN KEY (colors_id)
    REFERENCES colors(id)
;
    
ALTER TABLE colors
    ADD    FOREIGN KEY (size)
    REFERENCES size(size)
;
    
ALTER TABLE image
    ADD    FOREIGN KEY (colors_id)
    REFERENCES colors(id)
;
    
ALTER TABLE colors
    ADD    FOREIGN KEY (image_id)
    REFERENCES image(id)
;
    
ALTER TABLE size
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE image
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE sub-products
    ADD    FOREIGN KEY (products_id)
    REFERENCES products(id)
;
    
ALTER TABLE size
    ADD    FOREIGN KEY (sub-products_id)
    REFERENCES sub-products(id)
;
    
ALTER TABLE image
    ADD    FOREIGN KEY (sub-products_products_id)
    REFERENCES sub-products(products_id)
;
    
ALTER TABLE offers
    ADD    FOREIGN KEY (sub-products_id)
    REFERENCES sub-products(id)
;
    

# Create Indexes

