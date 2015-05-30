<?php
namespace App\Lib;

use Markzero\Http\Response;

class GoogleBook 
{
  const VOLUME_URI = 'https://www.googleapis.com/books/v1/volumes/';

  /**
   * @var stdObject
   */
  private $data = null;

  public function __construct($id) 
  {
    $this->data = null;

    $client = new \GuzzleHttp\Client(); 
    $response = $client->get(self::VOLUME_URI.$id);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) 
    {
      $this->data = json_decode($response->getBody());
    }
  }

  public function getData() 
  {
    return $this->data;
  }

  /**
   * @param string
   * return array Matched Books
   */
  static function search($keywords) 
  {
    if ($keywords === null || trim($keywords) === '')
      return array();

    $client = new \GuzzleHttp\Client(); 
    $response = $client->get(self::VOLUME_URI, [
      'query' => ['q' => $keywords]
    ]);

    $code = $response->getStatusCode();
    if ($code == Response::HTTP_OK) 
    {
      return json_decode($response->getBody());
    }
    
    return array();
  }
  
}
