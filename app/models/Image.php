<?php

namespace App\Models;

use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @Entity
 * @Table(name="images")
 */
class Image extends AppModel
{
  protected static $readable   = ['id'];
  protected static $accessible = ['src', 'width', 'height', 'type', 'uploaded_at', 'product'];

  const TYPE_LOCAL  = 'LOCAL';
  const TYPE_REMOTE = 'REMOTE';
  const STORAGE_PATH = '/Users/hieusun/www/mybookstore/storage/images/';

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;

  /** @Column(type="string") **/
  protected $src;

  /** @Column(type="integer") **/
  protected $width;

  /** @Column(type="integer") **/
  protected $height;

  /** @Column(type="string") **/
  protected $type;

  /** @Column(type="datetime") **/
  protected $uploaded_at;

  /**
   * @ManyToOne(targetEntity="Product", inversedBy="images")
   */
  protected $product;

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
  
  /**
   * @param  Product 
   * @param  UploadedFile
   *
   * @throws FileException if target file cannot be created or not writable
   * @throws ResourceNotFoundException
   */
  static function saveUploadedImage(Product $product, UploadedFile $file)
  {
    if ($product == null) {
      throw new ResourceNotFoundException();
    }

    $em = self::getEntityManager();

    $filename = uniqid("img_").'.'.$file->guessExtension();

    $saved_file = $file->move(Image::STORAGE_PATH, $filename);

    $width = $height = null;
    if ($imginfo = getimagesize($saved_file->getPathname())) {
      $width  = $imginfo[0];
      $height = $imginfo[1];
    }

    $img = new Image($product, $filename, Image::TYPE_LOCAL, $width, $height);
    $em->persist($img);

    $product->images[] = $img;
    $em->persist($product);
    $em->flush();
  }
}
