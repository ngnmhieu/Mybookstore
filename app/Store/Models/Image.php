<?php
namespace App\Store\Models;

use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="images")
 */
class Image extends \App\Models\Image 
{
  /**
   * @param Product $product the product the image belongs to 
   * @param string  $src source of the image (could be a filename or an URL)
   * @param string  $type must be either Image::TYPE_LOCAL or Image::TYPE_REMOTE
   *                      indicates where the image file is stored
   * @param int     $width width of image
   * @param int     $height optional height
   */
  function __construct(Product $product, $src, $type, $width = null, $height = null)
  {
    $this->product     = $product;
    $this->src         = $src;
    $this->type        = $type;
    $this->width       = $width;
    $this->height      = $height;
    $this->uploaded_at = new \DateTime("now");
  }

  protected function _validate()
  {
    // _TODO: validate url Image::TYPE_REMOTE
    // _TODO: validate Filename
  }

  /**
   * @return string full url of the image
   */
  public function getUrl()
  {
    if ($this->type == Image::TYPE_LOCAL) {

      return "http://mybookstore.local/images/".$this->src;

    } else if($this->type == Image::TYPE_REMOTE) {

      return $this->src;
    }

    return "";
  }
}
