<?php
require_once __DIR__.'/../config/database.php';
$pdo=db();
function hasColumn(PDO $pdo,string $table,string $column):bool{$s=$pdo->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");$s->execute([$table,$column]);return (bool)$s->fetchColumn();}
function hasIndex(PDO $pdo,string $table,string $index):bool{$s=$pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND INDEX_NAME=?");$s->execute([$table,$index]);return (bool)$s->fetchColumn();}
$pdo->exec("CREATE TABLE IF NOT EXISTS branches(id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,code VARCHAR(20) NOT NULL UNIQUE,name VARCHAR(120) NOT NULL,address VARCHAR(255) NULL,phone VARCHAR(30) NULL,is_active TINYINT(1) NOT NULL DEFAULT 1,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB");
$pdo->exec("INSERT IGNORE INTO branches(id,code,name) VALUES(1,'PUSAT','Cabang Pusat')");
$pdo->exec("ALTER TABLE users MODIFY role ENUM('boss','super_admin','owner','kasir') NOT NULL");
foreach(['users','categories','products','toppings','transactions'] as $table){if(!hasColumn($pdo,$table,'branch_id'))$pdo->exec("ALTER TABLE `$table` ADD branch_id INT UNSIGNED NULL");}
$pdo->exec("UPDATE users SET branch_id=1 WHERE branch_id IS NULL AND role IN('owner','kasir')");
foreach(['categories','products','toppings','transactions'] as $table)$pdo->exec("UPDATE `$table` SET branch_id=1 WHERE branch_id IS NULL");
if(hasIndex($pdo,'categories','name'))$pdo->exec('ALTER TABLE categories DROP INDEX name');
if(!hasIndex($pdo,'categories','uq_category_branch'))$pdo->exec('ALTER TABLE categories ADD UNIQUE KEY uq_category_branch(branch_id,name)');
if(hasIndex($pdo,'products','sku'))$pdo->exec('ALTER TABLE products DROP INDEX sku');
if(!hasIndex($pdo,'products','uq_product_branch_sku'))$pdo->exec('ALTER TABLE products ADD UNIQUE KEY uq_product_branch_sku(branch_id,sku)');
if(hasIndex($pdo,'toppings','name'))$pdo->exec('ALTER TABLE toppings DROP INDEX name');
if(!hasIndex($pdo,'toppings','uq_topping_branch'))$pdo->exec('ALTER TABLE toppings ADD UNIQUE KEY uq_topping_branch(branch_id,name)');
$accounts=[['Bos Warkop','bos','bos123','boss'],['Super Admin','superadmin','admin123','super_admin']];
$find=$pdo->prepare('SELECT id FROM users WHERE username=?');$insert=$pdo->prepare('INSERT INTO users(name,username,password,role,branch_id) VALUES(?,?,?,?,NULL)');
foreach($accounts as [$name,$username,$password,$role]){$find->execute([$username]);if(!$find->fetchColumn())$insert->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT),$role]);}
echo "Migrasi multi-cabang selesai.\n";
