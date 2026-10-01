<?php
require_once __DIR__.'/../config/auth.php';
require_login();
http_response_code(403);
exit('Akses profil kasir dinonaktifkan. Perubahan akun hanya dapat dilakukan oleh owner.');
