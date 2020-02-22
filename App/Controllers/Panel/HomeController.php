<?php
namespace App\Controllers\Panel;

use Fontibus\Facades\Controller;

class HomeController extends Controller {

    public function __construct() {
        $this->middleware(['auth']);
    }

    public function index() {
        return view('panel.home');
    }

}