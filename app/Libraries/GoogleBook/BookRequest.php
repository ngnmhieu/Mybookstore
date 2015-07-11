<?php
namespace App\Libraries\GoogleBook;

use GuzzleHttp\Exception\GuzzleException;
use Markzero\Http\Response;


/**
 * Interact with Google Book API
 * BookManager is responsible for performing book search and fetching books
 */
class BookRequest 
{

  /**
   * API Endpoint
   */
  const VOLUME_URI = 'https://www.googleapis.com/books/v1/volumes/';

  private static $AVAILABLE_LANGUAGES = array(
    'en' => 'English',
    'de' => 'German',
    'vi' => 'Vietnamese'
  );

  public static function getAvailableLanguages() 
  {
    return self::$AVAILABLE_LANGUAGES;
  }

  private static function getHttpClient()
  {
    return new \GuzzleHttp\Client(); 
  }

  /**
   * @param string Google Book ID
   * @return App\Lib\GoogleBook\Book
   * @throw \RuntimeException
   */
  public static function get($id) 
  {
    $client = self::getHttpClient();

    $response = $client->get(self::VOLUME_URI.$id);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) {
      return new Book(json_decode($response->getBody()));
    } 

    return null;
  }

  /**
   * @param App\Lib\GoogleBook\BookRequestParameter
   * @return App\Lib\GoogleBook\BookCollection
   */
  public static function search(BookRequestParameter $param) 
  {
    $result = self::sendSearchRequest($param);
    return $result && isset($result->items) ? new BookCollection($result->items) : new BookCollection();
  }
  
  /**
   * @param BookRequestParameter
   * @return int
   */
  public static function getTotal(BookRequestParameter $request_param) 
  {
    $param = clone $request_param;

    $param->setFields(array('totalItems'));

    $result = self::sendSearchRequest($param);

    return $result ? $result->totalItems : 0;
  }

  /**
   * @param App\Lib\GoogleBook\BookRequestParameter
   * @return stdClass | null
   */
  private static function sendSearchRequest(BookRequestParameter $param) 
  {

    /** construct query **/
    $query_parts = array();

    $keywords = $param->getKeywords();
    if ($keywords != '') {
      $query_parts[] = $param->getKeywords();
    }

    foreach ($param->getSpecialKeywords() as $field => $value) {
      if ($value) {
        $query_parts[] = "$field:$value";
      }
    }
    $query = implode('+', $query_parts);

    if (trim($query) === '')
      return null;

    $http_params = array(
      'q'          => $query,
      'maxResults' => $param->getLimit(),
      'startIndex' => $param->getOffset()
    );

    $language = $param->getLanguage();
    if ($language != '') {
      $http_params['langRestrict'] = $language;
    }

    $fields = $param->getFields();
    if (!empty($fields)) {
      $http_params['fields'] = implode(',', $fields);
    }

    $client = self::getHttpClient();
    $response = $client->get(self::VOLUME_URI, [
      'query' => $http_params
    ]);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) {
      $result = json_decode($response->getBody());
      return $result;
    }
    
    return null;
  }

}
