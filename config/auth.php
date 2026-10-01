<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])]);
    session_start();
}
require_once __DIR__.'/database.php';

function e(?string $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function rupiah(float|int|string $value): string { return 'Rp'.number_format((float)$value, 0, ',', '.'); }
function redirect(string $path): never { header('Location: '.BASE_URL.$path); exit; }
function user(): ?array { return $_SESSION['user'] ?? null; }
function branch_id(): int { return (int)(user()['branch_id'] ?? 0); }
function role_label(?string $role=null): string { return ['boss'=>'Bos / Pemilik','super_admin'=>'Super Admin','owner'=>'Admin Cabang','kasir'=>'Kasir'][$role ?? (user()['role'] ?? '')] ?? 'Pengguna'; }
function dashboard_path(?string $role=null): string { return match($role ?? (user()['role'] ?? '')){'boss'=>'/boss/dashboard.php','super_admin'=>'/super_admin/dashboard.php','owner'=>'/owner/dashboard.php','kasir'=>'/kasir/dashboard.php',default=>'/login.php'}; }
function require_login(): void { if (!user()) redirect('/login.php'); }
function require_role(string $role): void {
    require_login();
    if ((user()['role'] ?? '') !== $role) { http_response_code(403); exit('Akses ditolak.'); }
}
function require_roles(array $roles): void { require_login(); if(!in_array(user()['role'] ?? '',$roles,true)){http_response_code(403);exit('Akses ditolak.');} }
function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void {
    $token = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if (!hash_equals($_SESSION['csrf'] ?? '', (string)$token)) { http_response_code(419); exit('Sesi formulir kedaluwarsa. Muat ulang halaman.'); }
}
function flash(string $type, string $message): void { $_SESSION['flash'] = compact('type','message'); }
function pull_flash(): ?array { $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }
function audit_log(string $action,string $entity,?int $entityId=null,array $before=[],array $after=[]): void { if(!user())return; try{db()->prepare('INSERT INTO audit_logs(branch_id,user_id,action,entity,entity_id,before_data,after_data,ip_address) VALUES(?,?,?,?,?,?,?,?)')->execute([branch_id()?:null,user()['id'],$action,$entity,$entityId,$before?json_encode($before,JSON_UNESCAPED_UNICODE):null,$after?json_encode($after,JSON_UNESCAPED_UNICODE):null,$_SERVER['REMOTE_ADDR']??null]);}catch(Throwable $e){error_log($e->getMessage());} }
function current_shift(): ?array { if(!user()||user()['role']!=='kasir')return null;$s=db()->prepare("SELECT * FROM cash_shifts WHERE cashier_id=? AND branch_id=? AND status='open' ORDER BY id DESC LIMIT 1");$s->execute([user()['id'],branch_id()]);return $s->fetch()?:null; }
function next_invoice(PDO $pdo,int $branchId,string $prefix='WDJ'): string { $s=$pdo->prepare('SELECT code FROM branches WHERE id=? FOR UPDATE');$s->execute([$branchId]);$code=preg_replace('/[^A-Z0-9]/','',strtoupper((string)$s->fetchColumn()))?:'PST';$date=date('Ymd');$pdo->prepare('INSERT INTO invoice_sequences(branch_id,sequence_date,last_number) VALUES(?,CURDATE(),1) ON DUPLICATE KEY UPDATE last_number=LAST_INSERT_ID(last_number+1)')->execute([$branchId]);$number=(int)$pdo->lastInsertId();if($number===0)$number=1;return sprintf('%s-%s-%s-%04d',$prefix,$code,$date,$number); }
