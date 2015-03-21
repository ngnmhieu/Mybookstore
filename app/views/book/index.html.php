<?php $this->partial('common/header'); ?>

<ul>
  <?php foreach ($books as $book): ?>
  <li>
    <?=$book->id?>. <?=$book->name?>
    [ <a href="<?=webpath('book#edit', array($book->id))?>">Edit</a> ]
    [ <a href="<?=webpath('book#delete', array($book->id))?>">Delete</a> ]
  </li>
  <?php endforeach; ?>
</ul>

<?php $this->partial('common/footer'); ?>
