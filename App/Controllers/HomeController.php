<?php
namespace App\Controllers;

use Fontibus\Facades\Controller;

class HomeController extends Controller {

    public function __construct() {
        $this->middleware(['auth']);
    }

    public function index() {
        return view('home');
    }

}