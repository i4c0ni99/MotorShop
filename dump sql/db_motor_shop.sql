Drop table if exists users;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE users(
                      idUser int(10) primary key auto_increment,

                      name varchar(10) not null,

                      surname varchar(10) not null,

                      email varchar(10) not null,

                      password   varchar(10) not null,

                      sex varchar(10)   not null,

                      clientOrOpp varchar(15) not null,

                      addressClient varchar(50) not null,

                      houseNumberClient  varchar(10) not null,

                      state boolean not null default 1,

                      userName varchar(20) ,

                      idProduct int(10),

                      idOrder int(10),






                      constraint users_ibfk_2  foreign key(idOrder) references orders(idOrders)on delete cascade on update cascade,
                      constraint users_ibfk_4 foreign key(idProduct) references product(idProduct)on delete cascade on update cascade

)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;;
Drop table if exists product;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;
CREATE TABLE product(
                        idProduct INT(10) primary key auto_increment,
                        codeProduct varchar(10) NOT NULL,
                        nameProduct  varchar(5) not null,
                        categoryProduct  varchar(50) not null,
                        priceProduct   float(4)  not null,
                        quantityProduct int(4)  not null,
                        backColor varchar(6) not null,
                        imageSrc varchar(100),
                        idClient int(4),
                        idStateProduct int(4),
                        constraint product_ibfk_1 foreign key(idClient) references users(idUser)on delete cascade on update cascade,
                        constraint product_ibfk_2 foreign key(idStateProduct) references state(idState)on delete cascade on update cascade

)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
Drop table if exists orders ;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS;
SET FOREIGN_KEY_CHECKS = 0;
create table orders(
                       idOrders int(10)auto_increment primary key ,

                       stateOrderId int(10) ,

                       idUser int(10),

                       idProduct int(10),

                       deliveryDate date not null,

                       totalPriceOrder float(4) not null,

                       constraint orders_ibfk_1 foreign key(idUser) references users(idUser)on delete cascade on update cascade,

                       constraint orders_ibfk_2 foreign key(idProduct) references product(idProduct)on delete cascade on update cascade
)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


Drop table if exists state;
create table state(
                      idState int(4) primary key auto_increment,
                      state enum( 'in attesa di conferma','in preparazione','in consegna","consegnato')

)engine=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
