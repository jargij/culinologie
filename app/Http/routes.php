<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'RecipesController@index');
Route::get('/recipes', 'RecipesController@index');

Route::group(['prefix' => 'cookbooks/{slug}'], function () {
    Route::resource('recipes', 'RecipesController', ['only' =>
        ['index', 'show']]);
});

Route::group(['prefix' => 'cookbooks/{slug}', 'middleware' => 'auth'], function () {
    Route::resource('recipes', 'RecipesController', ['only' =>
        ['create', 'edit', 'store', 'update', 'destroy']]);
    Route::get('recipes/{recipes}/fork', 'RecipesController@fork');
    Route::post('recipes/{recipes}/bookmark', 'BookmarksController@bookmark');
    Route::post('recipes/{recipes}/unbookmark', 'BookmarksController@unbookmark');
});

Route::group(['middleware' => 'auth'], function () {
    Route::resource('recipes', 'RecipesController', ['only' =>
        ['create', 'edit', 'store', 'update', 'destroy']]);

    Route::get('recipes/{recipes}/fork', 'RecipesController@fork');
    Route::post('recipes/{recipes}/bookmark', 'BookmarksController@store');
    Route::delete('recipes/{recipes}/unbookmark', 'BookmarksController@destroy');

    Route::resource('cookbooks', 'CookbooksController', ['only' =>
        ['create', 'edit', 'store', 'update', 'destroy']]);
    Route::post('recipes/{recipe}/comments', ['as'=>'recipes.postComment', 'uses'=>'RecipesController@store']);
    Route::delete('recipes/{recipe}/comments/{comment_id}', ['as'=>'recipes.deleteComment', 'uses'=>'RecipesController@destroy']);
});

Route::group(['middleware' => 'admin'], function () {
    Route::resource('users', 'UsersController', ['only' =>
        ['index', 'store', 'destroy']]);
    Route::post('users/{users}/approve', 'UsersController@approve');
});

Route::get('recipes/random', 'RecipesController@random');
Route::resource('recipes', 'RecipesController', ['only' => ['index', 'show']]);

Route::resource('cookbooks', 'CookbooksController', ['only' => ['index', 'show']]);

// Help/docs
Route::get('/help', 'DocsController@index');
Route::get('/help/{path?}', 'DocsController@show')->where('path', '.+');

Route::controllers([
    'auth' => 'Auth\AuthController',
    'password' => 'Auth\PasswordController',
]);
