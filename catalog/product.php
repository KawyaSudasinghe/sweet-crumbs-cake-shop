<?php
require_once __DIR__ . '/../includes/auth.php';
 $id=(int)($_GET['id']??0);
 $pdo=db();
 $stmt=$pdo->prepare('SELECT p.*,c.name category_name FROM product p JOIN category c ON c.category_id=p.category_id WHERE p.product_id=? AND p.is_available=1');
$stmt->execute([$id]);
$product=$stmt->fetch();
if(!$product){http_response_code(404);
$page_title='Product not found';
require __DIR__.'/../includes/header.php';
echo '<div class="container section"><div class="empty"><h2>Product not found</h2><a class="btn" href="'.e(app_url('catalog/products.php')).'">Back to products</a></div></div>';
require __DIR__.'/../includes/footer.php';
exit;
}
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();
$qty=max(1,min(MAX_CART_QTY,(int)($_POST['quantity']??1)));
if($product['stock_qty']<1){flash('error','This product is currently out of stock.');
redirect('catalog/product.php?id='.$id);
}if(!current_user()){$_SESSION['after_login']='catalog/product.php?id='.$id;
flash('info','Please sign in before adding items to your cart.');
redirect('auth/login.php');
}$cartId=get_or_create_cart((int)$_SESSION['user_id']);
$stmt=$pdo->prepare('SELECT cart_item_id,quantity FROM cart_item WHERE cart_id=? AND product_id=?');
$stmt->execute([$cartId,$id]);
$existing=$stmt->fetch();
$newQty=$existing?(int)$existing['quantity']+$qty:$qty;
if($newQty>$product['stock_qty']){flash('error','The requested quantity is not available in stock.');
redirect('catalog/product.php?id='.$id);
}if($existing){$pdo->prepare('UPDATE cart_item SET quantity=? WHERE cart_item_id=?')->execute([$newQty,$existing['cart_item_id']]);
}else{$pdo->prepare('INSERT INTO cart_item(cart_id,product_id,quantity) VALUES(?,?,?)')->execute([$cartId,$id,$qty]);
}flash('success','Product added to your cart.');
redirect('cart/cart.php');
}
$reviews=$pdo->prepare('SELECT r.*,u.name FROM review r JOIN `user` u ON u.user_id=r.user_id WHERE r.product_id=? ORDER BY r.created_at DESC');
$reviews->execute([$id]);
$reviews=$reviews->fetchAll();
$page_title=$product['name'];
require __DIR__.'/../includes/header.php';

?><div class="container"><div class="detail-grid"><div class="detail-image"><img src="<?=e(safe_image($product['image_url']))?>" alt="<?=e($product['name'])?>"></div><div class="detail-content"><span class="eyebrow"><?=e($product['category_name'])?></span><h1><?=e($product['name'])?></h1><div class="price"><?=e(money((float)$product['price']))?></div><p><?=e($product['description'])?></p><div class="detail-meta"><span class="tag">Size: <?=e($product['size']?:'Standard')?></span><span class="tag">Stock: <?=e((string)$product['stock_qty'])?></span></div><?php if (current_user()): ?><a class="back-link" style="margin-bottom:10px" href="<?=e(app_url('reviews/review.php?product_id='.$id))?>">Write a review <?=icon('arrow',18)?></a><?php endif; ?><?php if((int)$product['stock_qty']>0): ?><form method="post"><input type="hidden" name="csrf" value="<?=e(csrf_token())?>"><div class="field" style="max-width:150px"><label>Quantity</label><input type="number" name="quantity" min="1" max="<?=e((string)min(MAX_CART_QTY,$product['stock_qty']))?>" value="1"></div><button class="btn" style="margin-top:14px">Add to cart <?=icon('cart',18)?></button></form><?php else: ?><div class="notice">Currently out of stock.</div><?php endif;
 ?></div></div><section class="section" style="padding-top:0"><div class="section-head"><div><h2>Customer reviews</h2><p class="muted">One review per customer per product is enforced by the application.</p></div></div><?php if(!$reviews): ?><div class="empty"><p class="muted">No reviews yet.</p></div><?php else: foreach($reviews as $r): ?><div class="review"><strong><?=e($r['name'])?></strong><div class="rating"><?=str_repeat('★',(int)$r['rating']).str_repeat('☆',5-(int)$r['rating'])?></div><p class="muted"><?=e($r['comment'])?></p></div><?php endforeach;
 endif;
 ?></section></div><?php require __DIR__.'/../includes/footer.php';
 ?>
