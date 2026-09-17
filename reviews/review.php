<?php
require_once __DIR__ . '/../includes/auth.php';
$user=require_login();
$productId=(int)($_GET['product_id']??$_POST['product_id']??0);
$pdo=db();
$errors=[];
$stmt=$pdo->prepare('SELECT * FROM product WHERE product_id=? AND is_available=1');
$stmt->execute([$productId]);
$product=$stmt->fetch();
if(!$product){flash('error','Product not found.');
redirect('catalog/products.php');
}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
$rating=(int)($_POST['rating']??0);
$comment=trim($_POST['comment']??'');
if($rating<1||$rating>5)$errors[]='Rating must be between 1 and 5.';
if(mb_strlen($comment)>1000)$errors[]='Review is too long.';
$check=$pdo->prepare('SELECT review_id FROM review WHERE user_id=? AND product_id=?');
$check->execute([$user['user_id'],$productId]);
if($check->fetch())$errors[]='You have already reviewed this product.';
if(!$errors){$pdo->prepare('INSERT INTO review(product_id,user_id,rating,comment) VALUES(?,?,?,?)')->execute([$productId,$user['user_id'],$rating,$comment?:null]);
flash('success','Your review was added.');
redirect('catalog/product.php?id='.$productId);
}}
$page_title='Write a review';
require __DIR__.'/../includes/header.php';

?><div class="container"><div class="form-card"><h1>Review <?=e($product['name'])?></h1><?php foreach($errors as $err): ?><div class="flash error"><?=e($err)?></div><?php endforeach;
 ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><input type="hidden" name="product_id" value="<?=e((string)$productId)?>"><div class="field"><label>Rating</label><select name="rating" required><option value="">Select</option><option value="5">5 — Excellent</option><option value="4">4 — Very good</option><option value="3">3 — Good</option><option value="2">2 — Fair</option><option value="1">1 — Poor</option></select></div><div class="field" style="margin-top:14px"><label>Comment</label><textarea name="comment" rows="5" maxlength="1000"></textarea></div><button class="btn btn-block" style="margin-top:16px">Submit review</button></form></div></div><?php require __DIR__.'/../includes/footer.php';
 ?>
