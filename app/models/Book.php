<?php

/**
 * @Entity 
 * @Table(name="books")
 **/
class Book extends AppModel {
  protected static $attr_reader = array('id');
  protected static $attr_accessor = array('name', 'ratings', 'description');

  /** @Id @Column(type="integer") @GeneratedValue **/
  protected $id;
  /** @Column(type="string") **/
  protected $name;
  /** @Column(type="string") **/
  protected $description;
  /**
   * @OneToMany(targetEntity="Rating", mappedBy="book")
   */
  protected $ratings;
  /**
   * @ManyToMany(targetEntity="User", mappedBy="books")
   */
  protected $users;

  protected function _default() {
  }

  protected function _validate() {
    $vm = self::createValidationManager();

    $vm->validate('name', new FunctionValidator(function($name) {
      return !empty($name);
    }, array($this->name)));

    $vm->do_validate();
  }

  /**
   * @throw ValidationException
   */
  static function create($params) {
    $em = self::getEntityMananger();

    $obj = new static();
    $obj->name   = $params->get('name');
    $obj->description = $params->get('description');

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

  /**
   * @throw ResourceNotFoundException
   *        ValidationException
   */
  static function update($id, $params) {
    $em = self::getEntityMananger();

    $obj = static::find($id);
    if ($obj === null) {
      throw new ResourceNotFoundException();
    }

    $obj->description = $params->get('description');
    $obj->name = $params->get('name');

    $em->persist($obj);
    $em->flush();

    return $obj;
  }

  /**
   * @throw ResourceNotFoundException
   *        Exception
   */
  static function delete($id) {
    $book = Book::find($id);
    if ($book === null) {
      throw new ResourceNotFoundException();
    }

    $conn = App::$em->getConnection();
    $conn->beginTransaction();

    try {
      App::$em->remove($book); 
      App::$em->flush();

      $conn->commit();
    } catch(Exception $e) {
      $conn->rollback();
      throw $e;
    }
  }

  /**
   * @return Rating | null
   */
  function ratingByUser($user) {
    if ($user === null)
      return null;
    $rating = Rating::findOneBy(array('user' => $user, 'book' => $this));

    return $rating;
  }

  function meanRating() {
    $ratings = Rating::findBy(array('book' => $this));
    $rating_sum = array_reduce($ratings, function($sum, $rating) {
      return $sum + $rating->value; 
    });

    return $rating_sum / count($ratings);
  }

  function positiveRatingPercent() {
    $ratings = Rating::findBy(array('book' => $this));

    $positive_ratings = array_filter($ratings, function($rating) {
      return ((int) $rating->value) >= 4; 
    });

    return 100 * count($positive_ratings) / count($ratings);
  }

  /**
   * @param int $num top $num related Books
   */
  function getTopRelated($num) {
    $em = self::getEntityMananger();

    // books other than this
    $query = $em->createQuery('SELECT b FROM Book b WHERE b.id != :book_id');
    $query->setParameter(':book_id', $this->id);
    $all_books = $query->getResult();

    // users rated this book
    $query = $em->createQuery('
      SELECT u.id FROM User u JOIN u.books b 
      WHERE b.id = :book_id GROUP BY u
    ');
    $query->setParameter(':book_id', $this->id);
    $uids_this = $query->getResult();
    $uids_this = array_flatten($uids_this);

    // calculate
    $scores = array();
    $books_map = array();
    foreach($all_books as $book) {
      $query = $em->createQuery('
        SELECT u.id FROM User u JOIN u.books b 
        WHERE b.id = :book_id GROUP BY u
      ');
      $query->setParameter(':book_id', $book->id);
      $uids_that = $query->getResult();
      $uids_that = array_flatten($uids_that);

      $uids_both = array_intersect($uids_that, $uids_this);
      $scores[$book->id] = count($uids_both) / count($uids_this);

      // store for later access by id
      $books_map[$book->id] = $book;
    }
  
    // sort with max on top
    arsort($scores);

    // get the books
    $top_related_books = array();
    $i = 0;
    foreach ($scores as $book_id => $score) {
      if ($i++ >= $num)
        break;
      $top_related_books[] = $books_map[$book_id];
    }

    return $top_related_books;
  }

}
