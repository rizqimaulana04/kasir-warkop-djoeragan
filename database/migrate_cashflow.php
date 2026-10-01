<?php
require_once __DIR__.'/../config/database.php';
db()->exec("CREATE TABLE IF NOT EXISTS cash_movements(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,branch_id INT UNSIGNED NOT NULL,user_id INT UNSIGNED NOT NULL,type ENUM('income','expense') NOT NULL,category VARCHAR(80) NOT NULL,amount DECIMAL(14,2) NOT NULL,description VARCHAR(255) NOT NULL,movement_date DATE NOT NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX idx_cash_branch_date(branch_id,movement_date),CONSTRAINT fk_cash_branch FOREIGN KEY(branch_id) REFERENCES branches(id),CONSTRAINT fk_cash_user FOREIGN KEY(user_id) REFERENCES users(id)) ENGINE=InnoDB");
echo "Tabel arus kas siap.\n";
