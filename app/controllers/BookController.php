<?php
class BookController extends AppController {

  public function index() {
    $books = Book::findAll();

    $this->respond_to('html', function() use ($books) {
      $data['books'] = $books;
      $this->render(new HtmlView($data, 'book/index', App::$VIEW_PATH));
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
        $this->response()->redirect(array("controller" => 'book', 'action' => 'index'));
      });
    } catch(ValidationException $e) {
      $this->respond_to('html', function() {
        $this->response()->setStatusCode(Response::HTTP_BAD_REQUEST, 'Bad Request (Validation Error)');
        $this->response()->redirect(array("controller" => 'book', 'action' => 'add'));
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
    $this->respond_to('html', function() {
    });
  }

  public function update($id) {
  }

  public function destroy() {
  }

}
