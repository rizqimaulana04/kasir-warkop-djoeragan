<?php
require_once __DIR__.'/../config/auth.php';require_role('kasir');header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit;}verify_csrf();
$data=json_decode(file_get_contents('php://input'),true);$items=$data['items']??[];$discount=max(0,(float)($data['discount']??0));$method=$data['method']??'';$amount=(float)($data['amount']??0);
if(!$items||!in_array($method,['tunai','qris'],true)){http_response_code(422);echo json_encode(['message'=>'Metode pembayaran harus Tunai atau QRIS.']);exit;}
$pdo=db();
try{
 $shift=current_shift();if(!$shift)throw new RuntimeException('Buka shift kasir terlebih dahulu.');
 $pdo->beginTransaction();$details=[];$subtotal=0;$profit=0;
 $find=$pdo->prepare('SELECT id,name,selling_price,purchase_price,stock FROM products WHERE id=? AND branch_id=? AND is_active=1 FOR UPDATE');
 $findTopping=$pdo->prepare('SELECT t.id,t.name,t.price,t.purchase_price,t.stock FROM toppings t JOIN product_toppings pt ON pt.topping_id=t.id WHERE pt.product_id=? AND t.id=? AND t.branch_id=? AND t.is_available=1 FOR UPDATE');
 foreach($items as $item){
  $qty=max(1,(int)($item['quantity']??0));$find->execute([(int)$item['id'],branch_id()]);$p=$find->fetch();if(!$p||$p['stock']<$qty)throw new RuntimeException('Stok produk tidak mencukupi.');
  $toppings=[];$extra=0;$topCost=0;foreach(array_unique(array_map('intval',$item['toppings']??[])) as $toppingId){$findTopping->execute([$p['id'],$toppingId,branch_id()]);$t=$findTopping->fetch();if(!$t||$t['stock']<$qty)throw new RuntimeException('Stok topping tidak mencukupi.');$toppings[]=$t;$extra+=(float)$t['price'];$topCost+=(float)$t['purchase_price'];}
  $unit=(float)$p['selling_price']+$extra;$line=$unit*$qty;$subtotal+=$line;$profit+=(($p['selling_price']-$p['purchase_price']+$extra-$topCost)*$qty);$note=mb_substr(trim((string)($item['note']??'')),0,255);$details[]=[$p,$qty,$unit,$line,$note,$toppings];
 }
 $discount=min($discount,$subtotal);$total=$subtotal-$discount;$profit-=$discount;if($amount<$total)throw new RuntimeException('Jumlah pembayaran kurang.');
 $invoice=next_invoice($pdo,branch_id());
 $pdo->prepare('INSERT INTO transactions(invoice,cashier_id,branch_id,shift_id,subtotal,discount,total,profit) VALUES(?,?,?,?,?,?,?,?)')->execute([$invoice,user()['id'],branch_id(),$shift['id'],$subtotal,$discount,$total,$profit]);$tid=(int)$pdo->lastInsertId();
 $ins=$pdo->prepare('INSERT INTO transaction_details(transaction_id,product_id,product_name,price,purchase_price,quantity,note,subtotal) VALUES(?,?,?,?,?,?,?,?)');$insTop=$pdo->prepare('INSERT INTO transaction_detail_toppings(transaction_detail_id,topping_id,topping_name,price) VALUES(?,?,?,?)');$stock=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?');$move=$pdo->prepare("INSERT INTO stock_movements(product_id,user_id,type,quantity,note,reference_id) VALUES(?,?,'sale',?,'Penjualan',?)");
 $topStock=$pdo->prepare('UPDATE toppings SET stock=stock-? WHERE id=? AND branch_id=?');foreach($details as [$p,$qty,$unit,$line,$note,$toppings]){$ins->execute([$tid,$p['id'],$p['name'],$unit,$p['purchase_price'],$qty,$note?:null,$line]);$detailId=(int)$pdo->lastInsertId();foreach($toppings as $t){$insTop->execute([$detailId,$t['id'],$t['name'],$t['price']]);$topStock->execute([$qty,$t['id'],branch_id()]);}$stock->execute([$qty,$p['id']]);$move->execute([$p['id'],user()['id'],-$qty,$tid]);}
 $change=$amount-$total;$pdo->prepare('INSERT INTO payments(transaction_id,method,amount,change_amount) VALUES(?,?,?,?)')->execute([$tid,$method,$amount,$change]);$pdo->commit();audit_log('transaction_paid','transaction',$tid,[],['invoice'=>$invoice,'total'=>$total,'method'=>$method]);echo json_encode(compact('invoice','total','change'));
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log($e->getMessage());http_response_code(422);echo json_encode(['message'=>$e instanceof RuntimeException?$e->getMessage():'Transaksi gagal disimpan.']);}
