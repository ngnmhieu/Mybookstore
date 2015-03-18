<?php
class BookController extends AppController {

  public function index() {
    $books = Book::findAll();

    $this->response()->respond_to('html', function() use ($books) {
      print_r($books);
    });
  }

  public function create() {
    try {
      $book = Book::create($this->request()->request);

      $this->response()->respond_to('html', function() {
        $this->response()->redirect(array("controller" => 'book', 'action' => 'index'));
      });
    } catch(ValidationException $e) {

    }
  }

  public function show($id) {
  }

  public function createform() {
    $this->response()->respond_to('html', function() {
      
    });
  }

}
