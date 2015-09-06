<?php
namespace App\Store\Controllers;

use App\Controllers\ApplicationController;
use App\Store\Models\UserSession; 
use App\Store\Models\User; 
use App\Store\Models\Basket; 

class StoreController extends ApplicationController 
{

  protected function getCommonData()
  {
    $userSession = UserSession::getInstance();

    $signedIn = $userSession->isSignedIn();

    $replacements = [
      'user'         => $signedIn ?  $userSession->getUser() : new User(),
      'is_signed_in' => $signedIn,
      'basket'       => Basket::getInstance($userSession),
    ];

    return $replacements;
  }

}
