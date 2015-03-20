<?php $this->partial('book/header'); ?>

<ul>
  <?php foreach ($books as $book): ?>
  <li><?=$book->id?>. <?=$book->name?></li>
  <?php endforeach; ?>
</ul>

<?php $this->partial('book/footer'); ?>
