CREATE TABLE IF NOT EXISTS products(
    id int AUTO_INCREMENT PRIMARY  KEY,
    name varchar(30) UNIQUE, -- alternatively you'd have a SKU that's unique
    description text,
    category text, 
    stock int DEFAULT  0,
    quantity int DEFAULT 0, 
    unit_price int DEFAULT  99999,
    visibility TINYINT(1),
    image text, -- this col type can't have a default value
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    modified TIMESTAMP DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP
   
)
  