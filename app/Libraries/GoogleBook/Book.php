<?php
namespace App\Libraries\GoogleBook;

use Markzero\Http\Response;

/**
 * Represent a Google Book Volume
 */
class Book 
{

  /**
   * @var string
   */
  private $id;

  /**
   * @var string
   */
  private $etag;

  /**
   * @var string
   */
  private $selfLink;

  /**
   * @var string
   */
  private $title;

  /**
   * @var string
   */
  private $subtitle;

  /**
   * @var array
   */
  private $authors;

  /**
   * @var array
   */
  private $categories;

  /**
   * @var string
   */
  private $mainCategory;

  /**
   * @var string
   */
  private $publisher;

  /**
   * @var string
   */
  private $description;

  /**
   * @var string
   */
  private $printType;

  /**
   * @var \DateTime
   */
  private $publishedDate;

  /**
   * @var int
   */
  private $pageCount;

  /**
   * @var string
   */
  private $isbn10;

  /**
   * @var string
   */
  private $isbn13;

  /**
   * @var string
   */
  private $issn;

  /**
   * @var string
   */
  private $language;

  /**
   * @var string
   */
  private $other_identifier;

  /**
   * @var string
   */
  private $thumbnail;

  /**
   * @var string
   */
  private $image;

  /**
   * @var float
   */
  private $listPrice;

  /**
   * @var string
   */
  private $listPriceCurrency;

  /**
   * @var float
   */
  private $retailPrice;

  /**
   * @var string
   */
  private $retailPriceCurrency;

  /**
   * @var string
   */
  private $previewLink;

  /**
   * @var string
   */
  private $infoLink;

  /**
   * @var string
   */
  private $canonicalVolumeLink;

  const ISBN10 = 'ISBN_10';
  const ISBN13 = 'ISBN_13';
  const ISSN   = 'ISSN';
  const OTHER  = 'OTHER';
  
  /**
   * @param stdClass Raw data from Google Book API v1 that represents a volume
   */
  public function __construct(\stdClass $data)
  {
    $this->id                  = $data->id;
    $this->etag                = $data->etag;
    $this->selfLink            = $data->selfLink;
    $volume_info               = $data->volumeInfo;
    $this->title               = $volume_info->title;
    $this->subtitle            = $this->getAttribute($volume_info, 'subtitle', '');
    $this->authors             = $this->getAttribute($volume_info, 'authors', []);
    $this->categories          = $this->getAttribute($volume_info, 'categories', []);
    $this->mainCategory        = $this->getAttribute($volume_info, 'mainCategories');
    $this->publisher           = $this->getAttribute($volume_info, 'publisher', '');
    $this->publishedDate       = $this->parseDate($this->getAttribute($volume_info, 'publishedDate'));
    $this->description         = $this->getAttribute($volume_info, 'description', '');
    $this->printType           = $this->getAttribute($volume_info, 'printType');
    $this->pageCount           = $this->getAttribute($volume_info, 'pageCount');
    $this->language            = $this->getAttribute($volume_info, 'language');
    $this->previewLink         = $this->getAttribute($volume_info, 'previewLink');
    $this->infoLink            = $this->getAttribute($volume_info, 'infoLink');
    $this->canonicalVolumeLink = $this->getAttribute($volume_info, 'canonicalVolumeLink');

    $this->setIdentifiers($this->getAttribute($volume_info, 'industryIdentifiers', []));
    $this->setImages($this->getAttribute($volume_info, 'imageLinks'));
    $this->setPrices($this->getAttribute($data, 'saleInfo'));
  }


  /**
   * @param \stdClass
   * @return $this 
   */
  public function setPrices(\stdClass $sale_info = null)
  {
    $list_price = $this->getAttribute($sale_info, 'listPrice');
    if ($list_price) {
      $this->listPrice = $this->getAttribute($list_price, 'amount');
      $this->listPriceCurrency = $this->getAttribute($list_price, 'currencyCode');
    }
    
    $retail_price = $this->getAttribute($sale_info, 'retailPrice');
    if ($retail_price) {
      $this->retailPrice = $this->getAttribute($retail_price, 'amount');
      $this->retailPriceCurrency = $this->getAttribute($retail_price, 'currencyCode');
    }

    return $this;
  }

  /**
   * @param \stdClass
   * @return $this
   */
  private function setImages(\stdClass $images = null)
  {
    if ($images) {
      $thumbnailPriority = [
        $this->getAttribute($images, 'thumbnail'),
        $this->getAttribute($images, 'small'),
        $this->getAttribute($images, 'medium'),
        $this->getAttribute($images, 'large'),
        $this->getAttribute($images, 'smallThumbnail'),
        $this->getAttribute($images, 'extraLarge'),
      ];

      foreach ($thumbnailPriority as $image) {
        if ($image) {
          $this->thumbnail = $image; break;
        }
      }

      $imagesPriority = [
        $this->getAttribute($images, 'medium'),
        $this->getAttribute($images, 'large'),
        $this->getAttribute($images, 'extraLarge'),
        $this->getAttribute($images, 'small'),
        $this->getAttribute($images, 'thumbnail'),
        $this->getAttribute($images, 'smallThumbnail'),
      ];

      foreach ($imagesPriority as $image) {
        if ($image) {
          $this->image = $image; break;
        }
      }
    }
    
    return $this;
  }

  /**
   * @param array
   * @return $this 
   */
  private function setIdentifiers(array $identifiers)
  {
    
    foreach ($identifiers as $identifier) {
      $value = $identifier->identifier;

      switch($identifier->type) {
        case self::ISBN10:
          $this->isbn10 = $value; break;
        case self::ISBN13:
          $this->isbn13 = $value; break;
        case self::ISSN:
          $this->issn   = $value; break;
        case self::OTHER:
          $this->other_identifier = $value; break;
      }

    }

    return $this;
  }

  /**
   * @param string yyyy | yyyy-mm-dd
   * @return \DateTime
   */
  private function parseDate($datestr)
  {
    if (preg_match("/^\d{4}$/", $datestr))
      return \DateTime::createFromFormat('Y-m-d', $datestr.'-01-01'); 

    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $datestr))
      return \DateTime::createFromFormat('Y-m-d', $datestr); 

    return null;
  }

  /**
   * Helper function for getting (unknown) attrbute in $data object 
   * @return mixed
   */
  private function getAttribute($data, $attribute, $default = null)
  {
    return isset($data->{$attribute}) ? $data->{$attribute} : $default;
  }

  /**
   * @return string
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getEtag()
  {
    return $this->etag;
  }

  /**
   * @return string
   */
  public function getSelfLink()
  {
    return $this->selfLink;
  }

  /**
   * @return string
   */
  public function getTitle()
  {
    return $this->title;
  }

  /**
   * @return string
   */
  public function getSubtitle()
  {
    return $this->subtitle;
  }

  /**
   * @return array
   */
  public function getAuthors()
  {
    return $this->authors;
  }

  /**
   * @return array
   */
  public function getCategories()
  {
    return $this->categories;
  }

  /**
   * @return array
   */
  public function getMainCategory()
  {
    return $this->mainCategory;
  }

  /**
   * @return string
   */
  public function getPublisher()
  {
    return $this->publisher;
  }

  /**
   * @return string
   */
  public function getDescription()
  {
    return $this->description;
  }

  /**
   * @return string
   */
  public function getPrintType()
  {
    return $this->printType;
  }

  /**
   * @return \DateTime
   */
  public function getPublishedDate()
  {
    return $this->publishedDate;
  }

  /**
   * @return int
   */
  public function getPageCount()
  {
    return $this->pageCount;
  }

  /**
   * @return string
   */
  public function getIsbn10()
  {
    return $this->isbn10;
  }

  /**
   * @return string
   */
  public function getIsbn13()
  {
    return $this->isbn13;
  }

  /**
   * @return string
   */
  public function getIssn()
  {
    return $this->issn;
  }

  /**
   * @return string
   */
  public function getLanguage()
  {
    return $this->language;
  }

  /**
   * @return string
   */
  public function getOtherIdentifier()
  {
    return $this->other_identifier;
  }

  /**
   * @return string
   */
  public function getThumbnail()
  {
    return $this->thumbnail;
  }

  /**
   * @return string
   */
  public function getImage()
  {
    return $this->image;
  }

  /**
   * @return float
   */
  public function getListPrice()
  {
    return $this->listPrice;
  }

  /**
   * @return string
   */
  public function getListPriceCurrency()
  {
    return $this->listPriceCurrency;
  }

  /**
   * @return float
   */
  public function getRetailPrice()
  {
    return $this->retailPrice;
  }

  /**
   * @return string
   */
  public function getRetailPriceCurrency()
  {
    return $this->retailPriceCurrency;
  }

  public function getPreviewLink() {
    return $this->previewLink;
  }

  public function getInfoLink() {
    return $this->infoLink;
  }

  public function getCanonicalVolumeLink() {
    return $this->canonicalVolumeLink;
  }
}
