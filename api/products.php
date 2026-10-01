<?php
require_once __DIR__.'/../config/auth.php';require_role('kasir');header('Content-Type: application/json');
$sql="SELECT p.id,p.name,p.sku,p.selling_price,p.stock,p.image,c.id category_id,c.name category FROM products p JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 AND p.stock>0 AND p.branch_id=? AND c.branch_id=?";$args=[branch_id(),branch_id()];
if(!empty($_GET['q'])){$sql.=" AND (p.name LIKE ? OR p.sku LIKE ?)";$v='%'.trim($_GET['q']).'%';$args[]=$v;$args[]=$v;}
if(!empty($_GET['category'])){$sql.=" AND c.id=?";$args[]=(int)$_GET['category'];}
$sql.=' ORDER BY p.name';$s=db()->prepare($sql);$s->execute($args);echo json_encode(['products'=>$s->fetchAll()],JSON_UNESCAPED_UNICODE);
