<?php
use Fontibus\Facades\Auth;
use Fontibus\Route\Router;

Router::get('/', 'HomeController@index');
Router::get('/home', 'HomeController@index')->name('home');

Router::get('/logout', function() { Auth::logout(); })->name('logout');
Router::get('/login', 'LoginController@index')->name('login');
Router::post('/login', 'LoginController@login');