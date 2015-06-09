<?php
namespace App\Lib\GoogleBook;

/**
 * Represent a collection of Google Book volumes
 */
class BookCollection implements \IteratorAggregate, \Countable
{
  /**
   * @var array<App\Lib\GoogleBook\Book>;
   */
  private $books;

  /**
   * 
   */
  public function __construct(array $books_data = array())
  {
    $this->books = array();

    foreach ($books_data as $data) {
      $this->books[] = new Book($data);
    }
  }

  /**
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->books);
  }

  /**
   * @return int
   */
  public function count()
  {
    return count($this->books);
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return count($this->books) === 0;
  }

}
