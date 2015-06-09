<?php
namespace App\Lib\GoogleBook;

/**
 * 
 */
class BookRequestParameter 
{
  /**
   * @var string
   */
  private $keywords;
  /**
   * @var array
   */
  private $special_keywords;
  /**
   * @var string
   */
  private $language;
  /**
   * @var int
   */
  private $limit;
  /**
   * @var int
   */
  private $offset;
  /**
   * @var array
   */
  private $fields;

  /**
   * @param string Main keywords 
   * @param array Special keywords
   * @param string Language code
   * @param int Max results
   * @param int Start index
   */
  public function __construct($keywords, $special_keywords = array(), $language = "", $limit = 20, $offset = 0)
  {
    $this->keywords         = $keywords;
    $this->special_keywords = $special_keywords;
    $this->language         = $language;
    $this->limit            = (int) $limit;
    $this->offset           = (int) $offset;
  }

  /**
   * return array
   */
  public function getFields() {
    return $this->fields;
  }

  public function setFields(array $fields) {
    $this->fields = $fields;
    return $this;
  }

  /**
   * @return string
   */
  public function getKeywords() {
    return $this->keywords;
  }

  public function setKeywords($keywords) {
    $this->keywords = $keywords;
    return $this;
  }

  /**
   * @return array
   */
  public function getSpecialKeywords() {
    return $this->special_keywords;
  }

  public function setSpecialKeywords($special_keywords) {
    $this->special_keywords = $special_keywords;
    return $this;
  }

  /**
   * @return string
   */
  public function getLanguage() {
    return $this->language;
  }

  public function setLanguage($language) {
    $this->language = $language;
    return $this;
  }

  /**
   * @return int
   */
  public function getLimit() {
    return $this->limit;
  }

  public function setLimit($limit) {
    $this->limit = (int) $limit;
    return $this;
  }

  /**
   * @return int
   */
  public function getOffset() {
    return $this->offset;
  }

  public function setOffset($offset) {
    $this->offset = (int) $offset;
    return $this;
  }

}
