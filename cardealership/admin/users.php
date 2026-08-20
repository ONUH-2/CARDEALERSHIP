<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/layout.php'; require_once __DIR__ . '/../../form/inc/connect.php'; require_admin();
$users=[];
if($con&&$con!==false){$rs=$con->query('SELECT id,username,email FROM user_data ORDER BY id DESC');if($rs)while($r=$rs->fetch_assoc())$users[]=$r;}
render_page_start('Users | Admin');render_navbar('Admin');
echo '<div class="page"><div class="card"><h2>Registered Users</h2><p class="muted">Monitor accounts created on the dealership site. Passwords are intentionally not displayed.</p><div class="table-wrap" style="margin-top:20px"><table><thead><tr><th>ID</th><th>Username</th><th>Email</th></tr></thead><tbody>';
if(!$users)echo '<tr><td colspan="3">No users found.</td></tr>';else foreach($users as $u)echo '<tr><td>#'.(int)$u['id'].'</td><td>'.esc($u['username']).'</td><td>'.esc($u['email']).'</td></tr>';
echo '</tbody></table></div></div></div>';render_page_end();
