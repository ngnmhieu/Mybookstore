<?php

namespace App\Models;

use Markzero\Mvc\AppModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Markzero\Http\Exception\ResourceNotFoundException;

/**
 * @MappedSuperclass
 */
abstract class Image extends AppModel
{
  protected static $readable   = ['id'];
  protected static $accessible = ['src', 'width', 'height', 'type', 'uploaded_at', 'product'];

  const TYPE_LOCAL   = 'LOCAL';
  const TYPE_REMOTE  = 'REMOTE';
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
}
