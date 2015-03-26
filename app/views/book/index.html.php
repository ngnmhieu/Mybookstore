<?php $this->partial('common/header'); ?>

<ul>
  <?php foreach ($books as $book): ?>
  <li style="margin-bottom: 10px;">
    <?=$book->id?>. 
    <a href="<?=webpath('book#show', array($book->id))?>"><?=$book->name?></a>
    [ <a href="<?=webpath('book#edit', array($book->id))?>">Edit</a> ]
    [ <a href="<?=webpath('book#delete', array($book->id))?>">Delete</a> ]


      <?php $rating = $user_ratings[$book->id]; ?>
      <?php if (isset($rating)) { ?>
        <form action="<?=webpath('book#updateRate', array($book->id, $rating['id']))?>" method="post">
      <?php } else { ?>
        <form action="<?=webpath('book#rate', array($book->id))?>" method="post">
      <?php } ?>

      (Average: <?=number_format($book->meanRating(), 2)?>)
      <br />
      (Recommend: <?=number_format($book->positiveRatingPercent(), 1)?>%)
      <br />
      <?php foreach ($rating_values as $val): ?>
      <input type="radio" name="rating[value]" id="rating_<?=$book->id?>_<?=$val?>" value="<?=$val?>" <?=(isset($rating) && $rating['value'] == $val) ? 'checked': ''?> /> 
      <label for="rating_<?=$book->id?>_<?=$val?>"><?=$val?></label>
      <?php endforeach; ?>
        <input type="submit" name="submit" value="Rate!" />
    </form>

  </li>
  <?php endforeach; ?>
</ul>

[ <a href="<?=webpath('book#add') ?>">Add Book</a> ]

<?php $this->partial('common/footer'); ?>
