<?php
class BookController extends AppController {

  public function index() {
    $books = Book::findAll();

    $this->respond_to('html', function() use ($books) {
      $data['books'] = $books;
      $this->render(new HtmlView($data, 'book/index'));
    });

    $this->respond_to('json', function() use ($books) {
      $data = array_map(function($book) {
        return $book->to_array();
      }, $books);
      $this->render(new JsonView($data));
    });
  }

  public function create() {
    try {
      $book = Book::create($this->request()->request);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });
    } catch(ValidationException $e) {

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'add');
      });

    }
  }

  public function show($id) {
  }

  public function add() {

    $this->respond_to('html', function() {
      $this->render(new HtmlView(array(), 'book/add'));
    });

  }

  public function edit($id) {
    try {
      $book = Book::find($id);

      $this->respond_to('html', function() use($book) {
        $data['book'] = $book;
        $this->render(new HtmlView($data, 'book/edit'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respond_to('html', function() use($id) {
        $this->response()->redirect('book', 'index');
      });

    }
    
  }

  public function update($id) {
    try {

      $book = Book::find($id);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

    } catch(ResourceNotFoundException $e) {

      $this->respond_to('html', function() use($id) {
        $this->response()->redirect('book', 'edit', array($id));
      });

    } catch(ValidationException $e) {

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'add');
      });

    }
  }

  public function destroy() {
  }

}
