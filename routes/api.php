<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\OrderController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('CreateUserAccount', [RegisterController::class, 'CreateUserAccount']);
Route::post('GetUserAccountById', [RegisterController::class, 'GetUserAccountById']);
Route::post('login', [RegisterController::class, 'login']);
Route::post('SetUserAccountPassword', [RegisterController::class, 'SetUserAccountPassword']);
Route::post('UpdateUserAccount', [RegisterController::class, 'UpdateUserAccount']);
Route::post('FindAllVendors', [ProductController::class, 'FindAllVendors']);
Route::post('FindProductsByIds', [ProductController::class, 'FindProductsByIds']);
Route::post('GetRootCategories', [ProductController::class, 'GetRootCategories']);
Route::post('GetCategoryChildren', [ProductController::class, 'GetCategoryChildren']);
Route::post('GetCategoryById', [ProductController::class, 'GetCategoryById']);
Route::post('GetfeatureProduct', [ProductController::class, 'GetfeatureProduct']);
Route::post('GetCategoryProducts', [ProductController::class, 'GetCategoryProducts']);
Route::post('CreateNewUnplacedOrder', [ProductController::class, 'CreateNewUnplacedOrder']);
Route::post('CheckExistOrder', [ProductController::class, 'CheckExistOrder']);
Route::post('GetOrderById', [ProductController::class, 'GetOrderById']);
Route::post('GetOrderByIdV1', [ProductController::class, 'GetOrderByIdV1']);
Route::post('RemoveLineitem', [ProductController::class, 'RemoveLineitem']);
Route::post('AddLineitem', [ProductController::class, 'AddLineitem']);
Route::post('Inventory', [ProductController::class, 'Inventory']);
Route::post('OrderPlaced', [ProductController::class, 'OrderPlaced']);
Route::post('UpdateAddress', [UserController::class, 'UpdateAddress']);
Route::post('AddAddress', [UserController::class, 'AddAddress']);
Route::post('GetAddress', [UserController::class, 'GetAddress']);
Route::post('RemoveOrder', [ProductController::class, 'RemoveOrder']);
Route::post('UpdateLineItem', [ProductController::class, 'UpdateLineItem']);
Route::get('getPurchaseHistory', [ProductController::class, 'getPurchaseHistory']);
Route::post('forgetPassword', [RegisterController::class, 'forgetPassword']);
Route::post('addToCartV1', [ProductController::class, 'addToCart']);
Route::post('cartList', [ProductController::class, 'cartList']);
Route::post('addToFavourite', [ProductController::class, 'addToFavourite']);
Route::post('removeFavourite', [ProductController::class, 'removeFavourite']);
Route::get('listFavourite', [ProductController::class, 'listFavourite']);
Route::get('predictNextOrder', [OrderController::class, 'predictNextOrder']);
Route::post('GetRootCategoriesV1', [CategoryController::class, 'rootList']);
Route::post('GetCategoryChildrenV1', [CategoryController::class, 'getCategoryChild']);
Route::post('addToInventory', [InventoryController::class, 'addToInventory']);
Route::get('inventoryList', [InventoryController::class, 'inventoryList']);
Route::post('orderPlacedV1', [OrderController::class, 'orderPlacedV1']);
Route::get('getPurchaseHistoryV1', [OrderController::class, 'getPurchaseHistory']);
Route::get('getOrderDetail', [OrderController::class, 'getOrderDetail']);
Route::post('scanOut', [InventoryController::class, 'scanOut']);
