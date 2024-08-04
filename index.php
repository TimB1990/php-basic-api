<?php

declare(strict_types=1);

// set autoload
spl_autoload_register(function($class){
    require __DIR__ . "/src/$class.php";
});

// set exception handler, using class ErrorHandler
set_exception_handler("ErrorHandler::handleException");

// define header to have content type application/json by default
header("Content-type: application/json; charset=UTF-8");

// take the parts from the url and explode them
$parts = explode("/", $_SERVER['REQUEST_URI']);

// the fourth index in this case is products for the products endpoint (localhost/projects/phprest/products)
// in this situation an 404 is returned when that index is not products 
if($parts[3] != "products"){
    http_response_code(404);
    exit;
}

// id can be a parameter after products to get the id, in this case e.g. localhost/projects/phprest/products/1
$id = $parts[4] ?? null;

// Set up database logic
$database = new Database("localhost", "product_db", "root", "");

// Create gateway that contains all MySQL operations for products
$gateway = new ProductGateway($database);

// The controller for products is responsible for when an id is given 
// it processes resource requests (products/1), otherwise it processes a collection request (products)
$controller = new ProductController($gateway);
$controller->processRequest($_SERVER["REQUEST_METHOD"], $id);