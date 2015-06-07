<?php
namespace App\Lib\GoogleBook;
use GuzzleHttp\Exception\GuzzleException;
use Markzero\Http\Response;


/**
 * Interact with Google Book API
 * BookManager is responsible for performing book search and fetching books
 */
class BookRequest 
{

  const VOLUME_URI = 'https://www.googleapis.com/books/v1/volumes/';

  public function __construct()
  {
  }

  private function getHttpClient() 
  {
    return new \GuzzleHttp\Client(); 
  }

  /**
   * @param string Google Book ID
   * @return App\Lib\GoogleBook\Book
   * @throw \RuntimeException
   */
  public function get($id) 
  {
    $client = $this->getHttpClient();

    $response = $client->get(self::VOLUME_URI.$id);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) {
      return new Book(json_decode($response->getBody()));
    } 

    return null;
  }

  /**
   * @param string
   * @return App\Lib\GoogleBook\BookCollection
   */
  public function search($keywords) 
  {
    if ($keywords === null || trim($keywords) === '')
      return new BookCollection(array());

    $client = $this->getHttpClient();
    $response = $client->get(self::VOLUME_URI, [
      'query' => ['q' => $keywords]
    ]);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) {
      $result = json_decode($response->getBody());
      $books_data = $result->totalItems != 0 ? $result->items : array() ; 
      return new BookCollection($books_data);
    }
    
    return null;
  }
  
}
