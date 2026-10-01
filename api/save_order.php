<?php
require_once __DIR__.'/../config/auth.php';require_role('kasir');header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}verify_csrf();
$data=json_decode(file_get_contents('php://input'),true);$items=$data['items']??[];$name=mb_substr(trim((string)($data['order_name']??'')),0,100);$table=mb_substr(trim((string)($data['table_number']??'')),0,30);$discount=max(0,(float)($data['discount']??0));
if(!$items||($name===''&&$table==='')){http_response_code(422);echo json_encode(['message'=>'Isi nama pesanan atau nomor meja.']);exit;}
$pdo=db();
try{
 $shift=current_shift();if(!$shift)throw new RuntimeException('Buka shift kasir terlebih dahulu.');
 $pdo->beginTransaction();$details=[];$subtotal=0;$profit=0;
 $find=$pdo->prepare('SELECT id,name,selling_price,purchase_price,stock FROM products WHERE id=? AND branch_id=? AND is_active=1 FOR UPDATE');
 $findTop=$pdo->prepare('SELECT t.id,t.name,t.price,t.purchase_price,t.stock FROM toppings t JOIN product_toppings pt ON pt.topping_id=t.id WHERE pt.product_id=? AND t.id=? AND t.branch_id=? AND t.is_available=1 FOR UPDATE');
 foreach($items as $item){$qty=max(1,(int)($item['quantity']??0));$find->execute([(int)$item['id'],branch_id()]);$p=$find->fetch();if(!$p||$p['stock']<$qty)throw new RuntimeException('Stok produk tidak mencukupi.');$tops=[];$extra=0;$topCost=0;foreach(array_unique(array_map('intval',$item['toppings']??[])) as $topId){$findTop->execute([$p['id'],$topId,branch_id()]);$t=$findTop->fetch();if(!$t||$t['stock']<$qty)throw new RuntimeException('Stok topping tidak mencukupi.');$tops[]=$t;$extra+=(float)$t['price'];$topCost+=(float)$t['purchase_price'];}$unit=(float)$p['selling_price']+$extra;$line=$unit*$qty;$subtotal+=$line;$profit+=(($p['selling_price']-$p['purchase_price']+$extra-$topCost)*$qty);$details[]=[$p,$qty,$unit,$line,mb_substr(trim((string)($item['note']??'')),0,255),$tops];}
 $discount=min($discount,$subtotal);$total=$subtotal-$discount;$profit-=$discount;
 $invoice=next_invoice($pdo,branch_id());
 $pdo->prepare("INSERT INTO transactions(invoice,cashier_id,branch_id,shift_id,subtotal,discount,total,profit,order_name,table_number,status) VALUES(?,?,?,?,?,?,?,?,?,?,'open')")->execute([$invoice,user()['id'],branch_id(),$shift['id'],$subtotal,$discount,$total,$profit,$name?:null,$table?:null]);$tid=(int)$pdo->lastInsertId();
 $ins=$pdo->prepare('INSERT INTO transaction_details(transaction_id,product_id,product_name,price,purchase_price,quantity,note,subtotal) VALUES(?,?,?,?,?,?,?,?)');$insTop=$pdo->prepare('INSERT INTO transaction_detail_toppings(transaction_detail_id,topping_id,topping_name,price) VALUES(?,?,?,?)');$stock=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=? AND branch_id=?');$move=$pdo->prepare("INSERT INTO stock_movements(product_id,user_id,type,quantity,note,reference_id) VALUES(?,?,'sale',?,'Pesanan berjalan',?)");
 $topStock=$pdo->prepare('UPDATE toppings SET stock=stock-? WHERE id=? AND branch_id=?');foreach($details as [$p,$qty,$unit,$line,$note,$tops]){$ins->execute([$tid,$p['id'],$p['name'],$unit,$p['purchase_price'],$qty,$note?:null,$line]);$did=(int)$pdo->lastInsertId();foreach($tops as $t){$insTop->execute([$did,$t['id'],$t['name'],$t['price']]);$topStock->execute([$qty,$t['id'],branch_id()]);}$stock->execute([$qty,$p['id'],branch_id()]);$move->execute([$p['id'],user()['id'],-$qty,$tid]);}
 $pdo->commit();audit_log('order_saved','transaction',$tid,[],['invoice'=>$invoice,'total'=>$total]);echo json_encode(['invoice'=>$invoice,'total'=>$total]);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log($e->getMessage());http_response_code(422);echo json_encode(['message'=>$e instanceof RuntimeException?$e->getMessage():'Pesanan gagal disimpan.']);}
