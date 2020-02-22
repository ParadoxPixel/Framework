<?php
namespace App\Controllers;

use Fontibus\Collection\Collection;
use Fontibus\Facades\Auth;
use Fontibus\Facades\Controller;
use Fontibus\Facades\Validator;
use Fontibus\Route\Redirect;

class LoginController extends Controller {

    public function __construct() {
        $this->middleware(['guest']);
    }

    public function index() {
        return view('login');
    }

    public function login(Collection $collection) {
        $vallidator = new Validator($collection->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'password', 'min:6']
        ]);

        if(!$vallidator->passed())
            return view('login', [
                'error' => 'Validator Failed'
            ]);

        if(!Auth::login($collection->get('email'), $collection->get('password')))
            return view('login', [
                'error' => 'Validator Failed'
            ]);

        return Redirect::route('home');
    }

}