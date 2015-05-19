<?php $this->partial('common/header'); ?>

<ul>
  <?php foreach ($products as $product): ?>
  <li style="margin-bottom: 10px;">
    <?=$product->id?>. 
    <a href="<?=webpath('ProductController#show', array($product->id))?>"><?=$product->name?></a>
    [ <a href="<?=webpath('ProductController#edit', array($product->id))?>">Edit</a> ]
    [ <a href="<?=webpath('ProductController#delete', array($product->id))?>">Delete</a> ]


      <?php $rating = $user_ratings[$product->id]; ?>
      <?php if (isset($rating)) { ?>
        <form action="<?=webpath('ProductController#updateRate', array($product->id, $rating['id']))?>" method="post">
      <?php } else { ?>
        <form action="<?=webpath('ProductController#rate', array($product->id))?>" method="post">
      <?php } ?>

      (Average: <?=number_format($product->meanRating(), 2)?>)
      <br />
      (Recommend: <?=number_format($product->positiveRatingPercent(), 1)?>%)
      <br />
      <?php foreach ($rating_values as $val): ?>
      <input type="radio" name="rating[value]" id="rating_<?=$product->id?>_<?=$val?>" value="<?=$val?>" <?=(isset($rating) && $rating['value'] == $val) ? 'checked': ''?> /> 
      <label for="rating_<?=$product->id?>_<?=$val?>"><?=$val?></label>
      <?php endforeach; ?>
        <input type="submit" name="submit" value="Rate!" />
    </form>

  </li>
  <?php endforeach; ?>
</ul>

[ <a href="<?=webpath('ProductController#add') ?>">Add Product</a> ]

<?php $this->partial('common/footer'); ?>
