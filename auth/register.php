<?php
require_once __DIR__ . '/../includes/auth.php';

if (current_user()) redirect('index.php');

$errors=[];
 $name=$email=$phone='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();

    $name=trim($_POST['name']??'');
 $email=trim($_POST['email']??'');
 $phone=trim($_POST['phone']??'');
 $password=$_POST['password']??'';
 $confirm=$_POST['confirm_password']??'';

    if ($name==='' || mb_strlen($name)>100) $errors[]='Please enter your name.';

    if (!valid_email($email)) $errors[]='Please enter a valid email address.';

    if ($phone!=='' && !valid_phone($phone)) $errors[]='Enter a valid Sri Lankan mobile number.';

    if (!valid_password($password)) $errors[]='Password must be at least 8 characters and contain letters and numbers.';

    if ($password!==$confirm) $errors[]='Passwords do not match.';

    if (!$errors) {
        $check=db()->prepare('SELECT user_id FROM `user` WHERE email=?');
 $check->execute([$email]);

        if ($check->fetch()) $errors[]='An account already exists with that email.';

        else { $stmt=db()->prepare('INSERT INTO `user` (name,email,password_hash,phone,role) VALUES (?,?,?,?,\'customer\')');
 $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT),$phone?:null]);
 $_SESSION['user_id']=(int)db()->lastInsertId();
 flash('success','Account created successfully.');
 redirect('index.php');
 }
    }
}
$page_title='Create account';
 require __DIR__ . '/../includes/header.php';

?><div class="container"><div class="form-card"><h1>Create your account</h1><p class="muted">Register to save addresses, manage your cart and place orders.</p><?php foreach($errors as $err): ?><div class="flash error"><?=e($err)?></div><?php endforeach;
 ?><form method="post" novalidate><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="form-grid"><div class="field full"><label>Full name</label><input name="name" maxlength="100" required value="<?=e($name)?>"></div><div class="field"><label>Email</label><input type="email" name="email" maxlength="150" required value="<?=e($email)?>"></div><div class="field"><label>Phone</label><input name="phone" maxlength="20" placeholder="0771234567" value="<?=e($phone)?>"></div><div class="field"><label>Password</label><input type="password" name="password" required minlength="8"></div><div class="field"><label>Confirm password</label><input type="password" name="confirm_password" required minlength="8"></div></div><p class="help">Use at least 8 characters with letters and numbers.</p><button class="btn btn-block" type="submit">Create account</button></form><p class="muted">Already registered? <a href="<?=e(app_url('auth/login.php'))?>" class="back-link">Sign in</a></p></div></div><?php require __DIR__ . '/../includes/footer.php';
 ?>
