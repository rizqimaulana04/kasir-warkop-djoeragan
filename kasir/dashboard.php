<?php
require_once __DIR__.'/../config/auth.php';
require_role('kasir');
$pdo=db();
$q=$pdo->prepare("SELECT COALESCE(SUM(total),0) omzet,COUNT(*) transaksi,COALESCE(SUM(profit),0) laba FROM transactions WHERE cashier_id=? AND status='paid' AND DATE(created_at)=CURDATE()");
$q->execute([user()['id']]); $s=$q->fetch();
$q=$pdo->prepare("SELECT COALESCE(SUM(d.quantity),0) FROM transaction_details d JOIN transactions t ON t.id=d.transaction_id WHERE t.cashier_id=? AND t.status='paid' AND DATE(t.created_at)=CURDATE()");
$q->execute([user()['id']]); $sold=(int)$q->fetchColumn();
$pageTitle='Dasbor'; $active='dashboard'; $bodyClass='cashier-home';
include __DIR__.'/../includes/header.php';
$menus=[
 ['kasir.php','cart3','Kasir'], ['pesanan.php','receipt','Daftar Pesanan'],
 ['riwayat.php','arrow-counterclockwise','Riwayat Penjualan'], ['kasir.php','box-seam','Produk'],
 ['riwayat.php?from='.date('Y-m-d').'&to='.date('Y-m-d'),'bar-chart','Laporan Saya'],
 [BASE_URL.'/logout.php','box-arrow-right','Keluar'],
];
?>
<div class="cashier-dashboard">
  <section class="welcome-banner">
    <div class="welcome-icon"><i class="bi bi-cup-hot-fill"></i></div>
    <div><h2>Selamat bekerja, <?=e(explode(' ',user()['name'])[0])?>!</h2><p>Semoga pelayanan hari ini lancar dan pelanggan puas. Pastikan pesanan sudah benar sebelum menerima pembayaran.</p></div>
  </section>
  <section class="sales-wallet">
    <div class="wallet-main"><div><small>Penjualan hari ini</small><strong><?=rupiah($s['omzet'])?></strong></div><a class="wallet-button" href="riwayat.php">Riwayat</a></div>
    <div class="wallet-stats"><a href="kasir.php"><span><i class="bi bi-arrow-down"></i></span><strong>Transaksi Baru</strong></a><a href="riwayat.php"><span><i class="bi bi-arrow-up"></i></span><strong><?=$s['transaksi']?> Transaksi</strong></a><div><span><i class="bi bi-bag-check"></i></span><strong><?=$sold?> Produk</strong></div></div>
  </section>
  <nav class="dashboard-menu" aria-label="Menu utama kasir">
    <?php foreach($menus as [$url,$icon,$label]): ?><a href="<?=$url?>"><i class="bi bi-<?=$icon?>"></i><span><?=$label?></span></a><?php endforeach; ?>
  </nav>
  <section class="quick-summary"><div><small>Status kasir</small><strong><span class="online-dot"></span> Aktif</strong></div><div><small>Jam sekarang</small><strong><?=date('H:i')?> WIB</strong></div><div><small>Laba kotor hari ini</small><strong><?=rupiah($s['laba'])?></strong></div></section>
</div>
<?php include __DIR__.'/../includes/footer.php'; ?>
