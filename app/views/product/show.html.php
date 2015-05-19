<?php $this->partial('common/header'); ?>

<h1><?= $product->name ?></h1>
<p><?= $product->description ?></p>

<h3>People rate this product, also rate these products: </h3>
<ul>
<?php foreach($top_related as $product) { ?>
  <li><?=$product->name?></li>
<?php } ?>
</ul>

<h3>Ratings</h3>
<ul>
  
<?php foreach ($ratings as $value => $ratings) { ?>
  <li><?=$value?> Stars: <?=count($ratings)?></li>
<?php } ?>
</ul>

[ <a href="<?=webpath('ProductController#index') ?>">Back to product list</a> ]

<?php $this->partial('common/footer'); ?>
