<?php
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\WarehouseTransactionController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Artisan;

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

    $router->group(['prefix' => 'api'], function () use ($router) {

        $router->group(['middleware' => 'auth'], function () use ($router) {
            $router->get('profile', 'UserController@profile');
            $router->get('logout', 'AuthController@logout');
            $router->get('/category/dropdown', 'CategoryController@getCategoryDropdown');
            $router->get('/warehouse/dropdown', 'WarehouseController@dropdown');
            $router->get('/product/dropdown', 'ProductController@dropdown');
            $router->get('/transaction', 'WarehouseTransactionController@index');

        });

        //warehouse routes
        $router->group(['middleware' => 'admin'], function () use ($router) {

            //warehouse routes
            $router->post('/warehouse', 'WarehouseController@store');
            $router->put('/warehouse/{id}', 'WarehouseController@update');
            $router->get('/warehouse', 'WarehouseController@index');
            $router->get('/warehouse/{id}', 'WarehouseController@single');
            $router->delete('/warehouse/{id}', 'WarehouseController@delete');

            //category routes
            $router->post('/category', 'CategoryController@store');
            $router->put('/category/{id}', 'CategoryController@update');
            $router->delete('/category/{id}', 'CategoryController@delete');
            $router->get('/category', 'CategoryController@index');
            $router->get('/category/{id}', 'CategoryController@single');
            //product
            $router->post('/product', 'ProductController@store');
            $router->get('/product', 'ProductController@index');
            $router->get('/product/{id}', 'ProductController@single');
            $router->put('/product/{id}', 'ProductController@update');
            $router->delete('/product/{id}', 'ProductController@delete');
            //Roles
            $router->post('/roles', 'RoleController@store');
            $router->get('/roles', 'RoleController@index');
            $router->put('/roles/{id}', 'RoleController@update');
            $router->get('/roles/dropdown', 'RoleController@dropdown');
            $router->get('/roles/{id}', 'RoleController@single');
            $router->delete('/roles/{id}', 'RoleController@delete');
            //user Roles
            $router->post('/userroles', 'UserRoleController@store');
            $router->get('/userroles', 'UserRoleController@index');
            $router->put('/userroles/{id}', 'UserRoleController@update');
            $router->get('/userroles/{id}', 'UserRoleController@single');
            //users
            $router->post('register', 'AuthController@register');
            $router->put('update/{id}', 'AuthController@update');
            $router->get('users/{id}', 'UserController@singleUser');
            $router->get('users', 'UserController@allUsers');
            $router->get('dropdown', 'UserController@dropdown');
            $router->get('logs', 'LogController@index');
            $router->get('logs/dropdown', 'LogController@dropdown');
            $router->get('logs/deleted', 'LogController@deleted');
            $router->get('/transaction/type', 'WarehouseTransactionController@typeDropdown');


        });

        $router->group(['middleware' => 'from'], function () use ($router) {
            $router->post('/transaction/create', 'WarehouseTransactionController@create');

        });
        $router->group(['middleware' => 'stores'], function () use ($router) {
            $router->get('/transaction/report', 'WarehouseTransactionController@checkStore');

        });

        $router->group(['middleware' => 'to'], function () use ($router) {
            $router->post('/transaction/store', 'WarehouseTransactionController@store');
            $router->post('/transaction/registration/{id}', 'WarehouseTransactionController@registrToWarehouse');

        });


        $router->post('login', 'AuthController@login');
        //test routes
        $router->post('/test/post', 'TestController@post');

        $router->get('/test/get', 'TestController@get');



    });


