<?php
namespace App\Twigs;

/**
 * @param int $star number of star to be highlighted
 *            if 0 < $star, $star = 0;
 *            if 5 > $star, $star = 5;
 *
 * &#9733; - active star
 * &#9734; - inactive star
 *
 * @return array [
 *    ['value' => *star*, 'active' => bool],
 *    ['value' => *star*, 'active' => bool]
 * ]
 */
function get_stars($star)
{
  if ($star < 0) 
    $star = 0;

  if ($star > 5) 
    $star = 5;
  $active = $star;
  $inactive = 5 - $star;

  $stars = [];
  for ($i = 0; $i < $active; $i++) 
    $stars[] = [ 'value' => "&#9733;", 'active' => true ];

  for ($i = 0; $i < $inactive; $i++)
    $stars[] = [ 'value' => "&#9734;", 'active' => false ];

  return $stars;
}
