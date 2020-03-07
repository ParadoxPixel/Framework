<?php
namespace App\Controllers;

use Fontibus\Facades\Controller;

class HomeController extends Controller {

    public function index() {
        return view('home');
    }

}