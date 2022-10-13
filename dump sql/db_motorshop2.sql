CREATE DATABASE IF NOT EXISTS db_motorShop;

USE db_motorShop;

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(25) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `feedbacks` (
  `id` int(11) NOT NULL,
  `users_email` varchar(100) DEFAULT NULL,
  `products_id` int(11) DEFAULT NULL,
  `rate` int(11) DEFAULT NULL,
  `review` varchar(1000) DEFAULT NULL,
  `date` date DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `roul` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `groups_has_services` (
  `services_id` int(11) NOT NULL,
  `groups_id` int(11) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `sub_products_id` int(11) DEFAULT NULL,
  `imgsrc` longblob DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `offers` (
  `id` int(11) NOT NULL,
  `categories_id` int(11) DEFAULT NULL,
  `products_id` int(11) DEFAULT NULL,
  `sub_products_id` int(11) DEFAULT NULL,
  `activation_date` date DEFAULT NULL,
  `expiration` date DEFAULT NULL,
  `percentage` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `users_email` varchar(100) DEFAULT NULL,
  `orders_has_products_id` int(11) DEFAULT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `totalPrice` double DEFAULT NULL,
  `state` varchar(25) DEFAULT NULL,
  `paymentMethod` varchar(25) DEFAULT NULL,
  `details` varchar(200) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `number` mediumtext NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `orders_has_products` (
  `id` int(11) NOT NULL,
  `sub_products_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `code` varchar(11) DEFAULT NULL,
  `title` varchar(150) DEFAULT NULL,
  `description` varchar(2000) DEFAULT NULL,
  `availability` tinyint(1) DEFAULT NULL,
  `specification` varchar(2000) DEFAULT NULL,
  `information` varchar(2000) DEFAULT NULL,
  `mediumRate` int(11) DEFAULT NULL,
  `categories_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `description` varchar(200) DEFAULT NULL,
  `script` varchar(200) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `shipping_address` (
  `id` int(11) NOT NULL,
  `users_email` varchar(50) DEFAULT NULL,
  `name` varchar(150) DEFAULT NULL,
  `surname` varchar(150) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `streetAddress` varchar(200) DEFAULT NULL,
  `cap` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `sizes` (
  `id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT NULL,
  `size` varchar(10) DEFAULT NULL,
  `availability` tinyint(1) DEFAULT NULL,
  `sub_products_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `name` varchar(25) DEFAULT NULL,
  `categories_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `sub_products` (
  `id` int(11) NOT NULL,
  `products_id` int(11) DEFAULT NULL,
  `color` varchar(25) DEFAULT NULL,
  `price` float DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `availability` tinyint(1) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `users` (
  `email` varchar(100) NOT NULL,
  `shipping_address_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `surname` varchar(100) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `phone` varchar(10) DEFAULT NULL,
  `avatar` longblob DEFAULT NULL,
  `verified` tinyint(1) NOT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `users_has_groups` (
  `users_email` varchar(100) NOT NULL,
  `groups_id` int(11) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `sub_products_id` int(11) DEFAULT NULL,
  `user_email` varchar(50) DEFAULT NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

ALTER TABLE
  `categories`
ADD
  PRIMARY KEY (`id`);

ALTER TABLE
  `feedbacks`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `users_email` (`users_email`),
ADD
  KEY `products_id` (`products_id`);

ALTER TABLE
  `groups`
ADD
  PRIMARY KEY (`id`);

ALTER TABLE
  `groups_has_services`
ADD
  KEY `services_id` (`services_id`),
ADD
  KEY `groups_id` (`groups_id`);

ALTER TABLE
  `images`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `sub_products_id` (`sub_products_id`);

ALTER TABLE
  `offers`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `categories_id` (`categories_id`),
ADD
  KEY `products_id` (`products_id`),
ADD
  KEY `sub_products_id` (`sub_products_id`);

ALTER TABLE
  `orders`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `users_email` (`users_email`),
ADD
  KEY `orders_has_products_id` (`orders_has_products_id`);

ALTER TABLE
  `orders_has_products`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `products_id` (`sub_products_id`);

ALTER TABLE
  `products`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `categories_id` (`categories_id`);

ALTER TABLE
  `services`
ADD
  PRIMARY KEY (`id`);

ALTER TABLE
  `shipping_address`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `orders_id` (`orders_id`),
ADD
  KEY `users_email` (`users_email`);

ALTER TABLE
  `sizes`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `sub_products_id` (`sub_products_id`);

ALTER TABLE
  `subcategories`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `categories_id` (`categories_id`);

ALTER TABLE
  `sub_products`
ADD
  PRIMARY KEY (`id`);

ALTER TABLE
  `users`
ADD
  PRIMARY KEY (`email`),
ADD
  KEY `shipping_address_id` (`shipping_address_id`);

ALTER TABLE
  `users_has_groups`
ADD
  KEY `users_email` (`users_email`),
ADD
  KEY `groups_id` (`groups_id`);

ALTER TABLE
  `wishlist`
ADD
  PRIMARY KEY (`id`),
ADD
  KEY `user_email` (`user_email`),
ADD
  KEY `sub_products_id` (`sub_products_id`);

ALTER TABLE
  `categories`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `feedbacks`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `images`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `offers`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `orders`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `products`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `shipping_address`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `sizes`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `subcategories`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `sub_products`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `wishlist`
MODIFY
  `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE
  `feedbacks`
ADD
  CONSTRAINT `feedbacks_ibfk_1` FOREIGN KEY (`users_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `feedbacks_ibfk_2` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `groups_has_services`
ADD
  CONSTRAINT `groups_has_services_ibfk_1` FOREIGN KEY (`groups_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `groups_has_services_ibfk_2` FOREIGN KEY (`services_id`) REFERENCES `services` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `images`
ADD
  CONSTRAINT `images_ibfk_1` FOREIGN KEY (`sub_products_id`) REFERENCES `sub_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `offers`
ADD
  CONSTRAINT `offers_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `offers_ibfk_2` FOREIGN KEY (`products_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `offers_ibfk_3` FOREIGN KEY (`sub_products_id`) REFERENCES `sub_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `orders`
ADD
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`users_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`orders_has_products_id`) REFERENCES `orders_has_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_address` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `orders_has_products`
ADD
  CONSTRAINT `orders_has_products_ibfk_1` FOREIGN KEY (`sub_products_id`) REFERENCES `sub_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `products`
ADD
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `shipping_address`
ADD
  CONSTRAINT `shipping_address_ibfk_2` FOREIGN KEY (`users_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `sizes`
ADD
  CONSTRAINT `sizes_ibfk_1` FOREIGN KEY (`sub_products_id`) REFERENCES `sub_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `subcategories`
ADD
  CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`categories_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `users`
ADD
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`shipping_address_id`) REFERENCES `shipping_address` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `users_has_groups`
ADD
  CONSTRAINT `users_has_groups_ibfk_1` FOREIGN KEY (`users_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `users_has_groups_ibfk_2` FOREIGN KEY (`groups_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE
  `wishlist`
ADD
  CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_email`) REFERENCES `users` (`email`) ON DELETE CASCADE ON UPDATE CASCADE,
ADD
  CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`sub_products_id`) REFERENCES `sub_products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

INSERT INTO
  users (
    `email`,
    `shipping_address_id`,
    `name`,
    `surname`,
    `password`,
    `phone`
  ) VALUE (
    'admin@gmail.com',
    NULL,
    'Luigi',
    'Visconti',
    MD5(MD5('admin')),
    '3921346140'
  );

INSERT INTO
  `groups` (`id`, `roul`)
VALUES
  (1, 'Admin'),
  (2, 'Customer');

INSERT INTO
  `services` (`id`, `category`, `description`, `script`)
VALUES
  (1, 'home', 'dashboard admin', 'dashboard.php'),
  (2, 'auth', 'login customer-admin', 'login.php'),
  (3, 'auth', 'logout customer-admin', 'logout.php'),
  (4, 'auth', 'register customer', 'register.php'),
  (5, 'auth','register admin','create-user-admin.php'),
  (6, 'list', 'user list', 'user-list.php'),
  (7, 'home', 'customer home', 'index.php'),
  (8, 'edit', 'edite prifile', 'editProfile.php'),
  (9, 'add', 'add products', 'addProduct.php'),
  (10,'add','add sub product','add-subProduct.php'),
  (11,'product','product details','product-detail.php');

INSERT INTO
  `users_has_groups` (`users_email`, `groups_id`) VALUE ('admin@gmail.com', 1);

INSERT INTO
  `groups_has_services` (`services_id`, `groups_id`)
VALUES
  (1, 1),
  (3, 1),
  (5, 1),
  (2, 2),
  (3, 2),
  (4, 2),
  (6, 1),
  (7, 2),
  (2, 1),
  (8, 1),
  (8, 2),
  (9, 1),
  (10, 1),
  (11, 1),
  (11, 2);

INSERT INTO
  `categories` (`id`, `name`)
VALUES
  (1, 'CASCHO'),
  (2, 'GUANTO');

INSERT INTO
  `subcategories` (`id`, `name`, `categories_id`)
VALUES
  (1, 'GUANTO INVERNALE', 2);