<p>CSRF-Token: <?php echo session()->has('X-CSRF') ? "TRUE" : "FALSE"; ?></p>
<?php
$user = \App\User::find(1);
$user->ip = 'Thing';
echo 'IP: '.$user->ip;
?>