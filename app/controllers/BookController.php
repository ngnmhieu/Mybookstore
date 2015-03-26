<?php
use Markzero\Mvc\View;
use Markzero\Mvc\AppController;
use Markzero\Auth\Exception\AuthenticationFailedException;
use Markzero\Auth\Exception\ActionNotAuthorizedException;
use Markzero\Http\Exception\ResourceNotFoundException;
use Markzero\Validation\Exception\ValidationException;

class BookController extends AppController {

  public function index() {
    $books = Book::findAll();

    $user_ratings = array();
    $user = UserSession::getUser();
    foreach ($books as $book) {
      $rating = $book->ratingByUser($user);
      $user_ratings[$book->id] = !$rating ? null : array(
        'id' => $rating->id,
        'value' => $rating->value
      );
    }

    $this->respond_to('html', function() use ($books, $user_ratings) {
      $data['books'] = $books;
      $data['rating_values'] = Rating::$VALID_VALUES;
      $data['user_ratings'] = $user_ratings;

      $this->render(new View\HtmlView($data, 'book/index'));
    });

    $this->respond_to('json', function() use ($books) {
      $data = array_map(function($book) {
        return $book->to_array();
      }, $books);
      $this->render(new View\JsonView($data));
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
    try {
      $book = Book::find($id);
      $ratings = $book->ratings;
      $top_related = $book->getTopRelated(5);

      $this->respond_to('html', function() use($book, $ratings, $top_related) {
        $data['book'] = $book;
        $data['ratings'] = array();
        $data['top_related'] = $top_related;

        foreach (Rating::$VALID_VALUES as $value) {
          $data['ratings'][$value] = array();
        }

        foreach ($ratings as $rating) {
          $data['ratings'][(int) $rating->value][] = $rating;
        }
        $this->render(new View\HtmlView($data, 'book/show'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respond_to('html', function() {
        $this->response()->redirect('book','index');
      });

    }

  }

  public function add() {

    $this->respond_to('html', function() {
      $this->render(new View\HtmlView(array(), 'book/add'));
    });

  }

  public function edit($id) {
    try {
      $book = Book::find($id);

      $this->respond_to('html', function() use($book) {
        $data['book'] = $book;
        $this->render(new View\HtmlView($data, 'book/edit'));
      });

    } catch(ResourceNotFoundException $e) {

      $this->respond_to('html', function() use($id) {
        $this->response()->redirect('book', 'index');
      });

    }
    
  }

  public function update($id) {
    try {

      $book = Book::update($id, $this->request()->request);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

    } catch(ResourceNotFoundException $e) {

      $this->respond_to('html', function() use($id) {
        $this->response()->redirect('book', 'edit', array($id));
      });

    } catch(ValidationException $e) {

      $this->respond_to('html', function() use($id) {
        $this->response()->redirect('book', 'edit', array($id));
      });

    }
  }

  function delete($id) {
    try {
      Book::delete($id);
      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

      $this->respond_to('json', function() {
        $this->response()->setStatusCode(Response::HTTP_OK, 'Transaction deleted');
      });

    } catch(ResourceNotFoundException $e) {
      
      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

    } catch(\Exception $e) {
      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

      $this->respond_to('json', function() use($e) {
        $this->response()->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR, '[Error] Transaction could not be deleted: '.$e->getMessage());
      });
    }
  }

  function updateRate($book_id, $id) {
    if (UserSession::isSignedIn()) {
      try {
        $rating = Rating::find($id);
        if ($rating === null)
          throw new ResourceNotFoundException();

        if ($rating->user !== UserSession::getUser()) {
          throw new ActionNotAuthorizedException();
        }

        Rating::update($id, $this->request()->request);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

      } catch(ActionNotAuthorizedException $e) {

        $this->respond_to('html', function() {
          $this->response()->redirect('book', 'index');
        });
      } catch(ValidationException $e) {
        
        $this->respond_to('html', function() {
          $this->response()->redirect('book', 'index');
        });

      } catch(ResourceNotFoundException $e) {

        $this->respond_to('html', function() {
          $this->response()->redirect('book', 'index');
        });

      }
    } else {
     
      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

    }
  }

  function rate($id) {
    if (UserSession::isSignedIn()) {
      try {
        $book = Book::find($id);
        $user = UserSession::getUser();

        Rating::create($user, $book, $this->request()->request);

      $this->respond_to('html', function() {
        $this->response()->redirect('book', 'index');
      });

      } catch(ValidationException $e) {
        
        $this->respond_to('html', function() {
          $this->response()->redirect('book', 'index');
        });

      } catch(ResourceNotFoundException $e) {

        // Book or User not found
        $this->respond_to('html', function() {
          $this->response()->redirect('book', 'index');
        });

      }
    } else {

      $this->respond_to('html', function() {
        // User not signed in
        $this->response()->redirect('book', 'index');
      });

    }
  }

}
