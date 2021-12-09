CREATE TABLE IF NOT EXISTS `orders` (
	`id` int auto_increment not null,
	`user_id` int,
	`total_price` decimal(12, 2),
    `payment_method` varchar(100),
    `address` varchar(120),
    `modified`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP on update current_timestamp,
    `created`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	primary key (id),
	foreign key (user_id) references Users(id)
)
