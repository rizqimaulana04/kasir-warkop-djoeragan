document.addEventListener('DOMContentLoaded',()=>{
 const sidebar=document.querySelector('.sidebar'),backdrop=document.querySelector('.drawer-backdrop');
 const toggle=open=>{sidebar?.classList.toggle('open',open);backdrop?.classList.toggle('open',open)};
 document.querySelector('.menu-toggle')?.addEventListener('click',()=>toggle(true));
 document.querySelector('.close-menu')?.addEventListener('click',()=>toggle(false));backdrop?.addEventListener('click',()=>toggle(false));
 document.querySelector('.toggle-password')?.addEventListener('click',e=>{const p=document.querySelector('#password');p.type=p.type==='password'?'text':'password';e.currentTarget.innerHTML=`<i class="bi bi-eye${p.type==='password'?'':'-slash'}"></i>`});
 if('serviceWorker' in navigator) navigator.serviceWorker.register(`${window.APP_BASE||'/warkop-djoeragan'}/service-worker.js`).catch(()=>{});
 const status=document.createElement('div');status.className='connection-status';status.innerHTML='<span></span><b>Memeriksa koneksi</b>';document.body.append(status);let online=true;async function checkConnection(){try{const r=await fetch(`${window.APP_BASE||'/warkop-djoeragan'}/api/health.php`,{cache:'no-store'});if(!r.ok)throw 0;online=true;status.className='connection-status connected';status.querySelector('b').textContent='Terhubung';setTimeout(()=>status.classList.add('compact'),1800)}catch{online=false;status.className='connection-status disconnected';status.querySelector('b').textContent='Server terputus';document.querySelectorAll('#pay-button,#confirm-payment,#hold-button,#finish-order').forEach(b=>b.disabled=true)}}checkConnection();setInterval(checkConnection,12000);window.addEventListener('offline',checkConnection);
});
window.rupiah=n=>'Rp'+Number(n||0).toLocaleString('id-ID');
