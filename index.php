<?php require_once __DIR__.'/config/auth.php'; if(!user()) redirect('/login.php'); redirect(dashboard_path());
