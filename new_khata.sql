DROP TABLE IF EXISTS khata;

CREATE TABLE khata (
    id INT AUTO_INCREMENT PRIMARY KEY,
    party_id INT NOT NULL,
    date DATE NOT NULL,
    description VARCHAR(255),
    new_amount DECIMAL(12,2) DEFAULT 0,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    remaining_amount DECIMAL(12,2) GENERATED ALWAYS AS (new_amount - paid_amount) STORED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (party_id) REFERENCES parties(id) ON DELETE CASCADE
);