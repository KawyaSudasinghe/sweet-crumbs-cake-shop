<?php
require_once __DIR__ . '/../includes/auth.php';
 $pdo=db();

$category=trim($_GET['category']??'');
 $search=trim($_GET['search']??'');
 $where=['p.is_available=1'];
 $params=[];

if($category!==''){ $where[]='c.name=?';
 $params[]=$category;
 }
if($search!==''){ $where[]='(p.name LIKE ? OR p.description LIKE ? OR c.name LIKE ?)';
 $term='%'.$search.'%';
 $params=array_merge($params,[$term,$term,$term]);
 }
$sql='SELECT p.*,c.name AS category_name FROM product p JOIN category c ON c.category_id=p.category_id WHERE '.implode(' AND ',$where).' ORDER BY p.product_id';
 $stmt=$pdo->prepare($sql);
$stmt->execute($params);
$products=$stmt->fetchAll();
$categories=$pdo->query('SELECT * FROM category ORDER BY category_id')->fetchAll();

$page_title='Products';
 require __DIR__ . '/../includes/header.php';

?><div class="container section"><div class="section-head"><div><span class="eyebrow">Catalogue</span><h2>75+ products</h2><p class="muted">Browse by category or search by product name.</p></div></div><form class="toolbar" method="get"><div class="search-box"><?=icon('search')?><input name="search" data-search-filter placeholder="Search cakes, cookies..." value="<?=e($search)?>"></div><select class="select" name="category" onchange="this.form.submit()"><option value="">All categories</option><?php foreach($categories as $cat): ?><option value="<?=e($cat['name'])?>" <?=$category===$cat['name']?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;
 ?></select></form><?php if(!$products): ?><div class="empty"><h3>No products found</h3><p class="muted">Try another search or category.</p></div><?php else: ?><div class="product-grid"><?php foreach($products as $p): ?><article class="product-card" data-product-card><img src="<?=e(safe_image($p['image_url']))?>" alt="<?=e($p['name'])?>"><div class="product-body"><span class="product-category"><?=e($p['category_name'])?></span><h3><?=e($p['name'])?></h3><p><?=e(mb_strimwidth($p['description'],0,95,'…'))?></p><div class="product-bottom"><span class="price"><?=e(money((float)$p['price']))?></span><a class="btn btn-small" href="<?=e(app_url('catalog/product.php?id='.$p['product_id']))?>">View</a></div></div></article><?php endforeach;
 ?></div><?php endif;
 ?></div><?php require __DIR__ . '/../includes/footer.php';
 ?>
