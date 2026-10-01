<?php
require_once __DIR__.'/../config/auth.php';
require_role('super_admin');
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 if(isset($_POST['toggle'])){
  $pdo->prepare("UPDATE users SET is_active=1-is_active WHERE id=? AND role='owner'")->execute([(int)$_POST['toggle']]);
 }else{
  $id=(int)($_POST['id']??0);$name=trim($_POST['name']);$username=trim($_POST['username']);$bid=(int)$_POST['branch_id'];$password=$_POST['password']??'';
  if($id){$pdo->prepare("UPDATE users SET name=?,username=?,branch_id=? WHERE id=? AND role='owner'")->execute([$name,$username,$bid,$id]);if($password!=='')$pdo->prepare("UPDATE users SET password=? WHERE id=? AND role='owner'")->execute([password_hash($password,PASSWORD_DEFAULT),$id]);}
  else{$pdo->prepare("INSERT INTO users(name,username,password,role,branch_id) VALUES(?,?,?,'owner',?)")->execute([$name,$username,password_hash($password,PASSWORD_DEFAULT),$bid]);}
  flash('success','Akun Admin Cabang disimpan.');
 }
 redirect('/super_admin/admin.php');
}
$rows=$pdo->query("SELECT u.*,b.name branch FROM users u JOIN branches b ON b.id=u.branch_id WHERE u.role='owner' ORDER BY b.name,u.name")->fetchAll();
$branches=$pdo->query('SELECT id,name FROM branches WHERE is_active=1 ORDER BY name')->fetchAll();
$edit=null;if(isset($_GET['edit'])){$s=$pdo->prepare("SELECT * FROM users WHERE id=? AND role='owner'");$s->execute([(int)$_GET['edit']]);$edit=$s->fetch();}
$pageTitle='Admin Cabang';$active='admin';include __DIR__.'/../includes/header.php';
?>
<div class="grid two-col">
 <section class="card"><h2><?=$edit?'Edit':'Tambah'?> Admin Cabang</h2><form method="post" class="form-grid" style="margin-top:16px"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><input type="hidden" name="id" value="<?=$edit['id']??0?>"><label class="field full">Cabang<select name="branch_id" required><?php foreach($branches as $b):?><option value="<?=$b['id']?>" <?=($edit['branch_id']??0)==$b['id']?'selected':''?>><?=e($b['name'])?></option><?php endforeach;?></select></label><label>Nama<input name="name" required value="<?=e($edit['name']??'')?>"></label><label>Username<input name="username" required value="<?=e($edit['username']??'')?>"></label><label class="field full">Kata sandi<input type="password" name="password" <?=$edit?'':'required'?> minlength="6"></label><button class="btn primary">Simpan akun</button></form></section>
 <section class="card"><h2>Daftar Admin Cabang</h2><div class="table-wrap"><table class="table"><tbody><?php foreach($rows as $r):?><tr><td><strong><?=e($r['name'])?></strong><br><small><?=e($r['branch'])?> · <?=e($r['username'])?></small></td><td><span class="badge <?=$r['is_active']?'':'off'?>"><?=$r['is_active']?'Aktif':'Nonaktif'?></span></td><td><div class="actions"><a class="btn soft sm" href="?edit=<?=$r['id']?>">Edit</a><form method="post"><input type="hidden" name="csrf" value="<?=csrf_token()?>"><button class="btn danger sm" name="toggle" value="<?=$r['id']?>"><?=$r['is_active']?'Nonaktifkan':'Aktifkan'?></button></form></div></td></tr><?php endforeach;?></tbody></table></div></section>
</div>
<?php include __DIR__.'/../includes/footer.php';?>
