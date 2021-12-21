CREATE TABLE IF NOT EXISTS `Ratings` (
    `id` INT NOT NULL AUTO_INCREMENT,
    product_id int,
    user_id    int,
    rating tinyint,
   comment TEXT(1000),
   created    datetime       default current_timestamp,
    modified   datetime       default current_timestamp on update current_timestamp,
    UNIQUE KEY (id)
) 
