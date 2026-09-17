<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) redirect('index.php');

$error='';
 $email='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
 $email=trim($_POST['email']??'');
 $password=$_POST['password']??'';

    if (!valid_email($email) || $password==='') $error='Please enter a valid email and password.';

    else { $stmt=db()->prepare('SELECT * FROM `user` WHERE email=?');
 $stmt->execute([$email]);
 $user=$stmt->fetch();
 if (!$user || !password_verify($password,$user['password_hash'])) $error='Invalid email or password. Please try again.';
 else { session_regenerate_id(true);
 $_SESSION['user_id']=(int)$user['user_id'];
 flash('success','Welcome back, ' . $user['name'] . '.');
 $next=$_SESSION['after_login']??'index.php';
 unset($_SESSION['after_login']);
 redirect($next);
 } }
}
$page_title='Login';
 require __DIR__ . '/../includes/header.php';

?><div class="container"><div class="form-card"><h1>Sign in</h1><p class="muted">Access your profile, saved addresses, cart and orders.</p><?php if($error): ?><div class="flash error"><?=e($error)?></div><?php endif;
 ?><form method="post" novalidate><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="field"><label>Email</label><input type="email" name="email" required value="<?=e($email)?>"></div><div class="field" style="margin-top:16px"><label>Password</label><input type="password" name="password" required></div><button class="btn btn-block" style="margin-top:18px" type="submit"><?=icon('lock',18)?> Sign in</button></form><p class="muted">No account? <a href="<?=e(app_url('auth/register.php'))?>" class="back-link">Create one</a></p></div></div><?php require __DIR__ . '/../includes/footer.php';
 ?>
