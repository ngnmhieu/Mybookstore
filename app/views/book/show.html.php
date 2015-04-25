<?php $this->partial('common/header'); ?>

<h1><?= $book->name ?></h1>
<p><?= $book->description ?></p>

<h3>People rate this book, also rate these books: </h3>
<ul>
<?php foreach($top_related as $book) { ?>
  <li><?=$book->name?></li>
<?php } ?>
</ul>

<h3>Ratings</h3>
<ul>
  
<?php foreach ($ratings as $value => $ratings) { ?>
  <li><?=$value?> Stars: <?=count($ratings)?></li>
<?php } ?>
</ul>

[ <a href="<?=webpath('BookController#index') ?>">Back to book list</a> ]

<?php $this->partial('common/footer'); ?>
