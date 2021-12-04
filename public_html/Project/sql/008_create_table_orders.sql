CREATE TABLE IF NOT EXISTS Orders(
    id int AUTO_INCREMENT PRIMARY KEY,
    user_id int,
    total_price INT,
    address VARCHAR(100),
    created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payment_method VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES Users(id)
    
)