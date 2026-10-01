<?php
$role=user()['role'];
$boss=[['dashboard','Dasbor Pemilik','speedometer2'],['laporan','Laporan Semua Cabang','bar-chart'],['stok','Stok Semua Cabang','boxes'],['audit','Audit Aktivitas','shield-check']];
$superAdmin=[['dashboard','Dasbor','speedometer2'],['cabang','Cabang','shop'],['admin','Admin Cabang','people']];
$owner=[['dashboard','Dasbor Cabang','speedometer2'],['produk','Produk','box-seam'],['kategori','Kategori','tags'],['stok','Stok','boxes'],['keuangan','Pemasukan & Pengeluaran','wallet2'],['transaksi','Transaksi','receipt'],['laporan','Laporan','bar-chart'],['kasir','Akun Kasir','people'],['profil','Profil','person']];
$cashier=[['dashboard','Dasbor','speedometer2'],['shift','Shift Kasir','cash-coin'],['kasir','Kasir','cart3'],['pesanan','Pesanan Aktif','clipboard-check'],['riwayat','Riwayat','clock-history']];
$items=match($role){'boss'=>$boss,'super_admin'=>$superAdmin,'owner'=>$owner,default=>$cashier};
$folder=match($role){'boss'=>'boss','super_admin'=>'super_admin','owner'=>'owner',default=>'kasir'};
?>
<div class="drawer-backdrop"></div><aside class="sidebar"><div class="brand"><img class="brand-logo" src="<?=BASE_URL?>/assets/images/logo.png" alt="Logo Warkop Djoeragan"><div><strong>Warkop Djoeragan</strong><small>Point of Sale</small></div><button class="icon-btn close-menu"><i class="bi bi-x-lg"></i></button></div><div class="user-card"><div class="avatar"><?=strtoupper(substr(user()['name'],0,1))?></div><div><strong><?=e(user()['name'])?></strong><small><?=e(role_label($role))?></small></div></div><nav><?php foreach($items as [$slug,$label,$icon]): ?><a class="<?=$active===$slug?'active':''?>" href="<?=BASE_URL?>/<?=$folder?>/<?=$slug?>.php"><i class="bi bi-<?=$icon?>"></i><span><?=$label?></span></a><?php endforeach; ?></nav><a class="logout" href="<?=BASE_URL?>/logout.php"><i class="bi bi-box-arrow-left"></i> Keluar</a></aside>
