CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,        
    username VARCHAR(100) NOT NULL,                 
    email VARCHAR(255) NOT NULL,                    
    password VARCHAR(255) NOT NULL,                 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP   
);

CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,            
    user_id INT NOT NULL,                           
    title VARCHAR(255) NOT NULL,                   
    description TEXT,                               
    status ENUM('pending', 'completed', 'in-progress') DEFAULT 'pending', 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,  
    FOREIGN KEY (user_id) REFERENCES users(user_id) 
);


ALTER TABLE tasks
ADD CONSTRAINT fk_user_id FOREIGN KEY (user_id) REFERENCES users(user_id);