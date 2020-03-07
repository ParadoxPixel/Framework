<?php
use Fontibus\Facades\Auth;
use Fontibus\Route\Router;

Router::get('/', 'HomeController@index');
Router::get('/home', 'HomeController@index')->name('home');

Router::group('/panel', function() {
    Router::get('/home', 'HomeController@index')->name('panel.home');
}, 'Panel');

Router::get('/logout', function() { Auth::logout(); })->name('logout');
Router::get('/login', 'LoginController@index')->name('login');
Router::post('/login', 'LoginController@login');