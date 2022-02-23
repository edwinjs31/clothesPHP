<?php

require "DB.php";
require "Product.php";

function return_response($status, $statusMessage, $data) {
    header("HTTP/1.1 $status $statusMessage");
    header("Content-Type: application/json; charset=UTF-8");
    //CORS
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

    echo json_encode($data);
}

$connectionURI = explode("/", $_SERVER['REQUEST_URI']);

//Se limpian los elementos en blanco para direcciones del tipo dominio.com/user/1/ que nos daría un último elemento del array en blanco
foreach ($connectionURI as $key => $value) {
    if (empty($value)) {
        unset($connectionURI[$key]);
    }
}

//Obtenemos id (en POST no) y entidad(Product), el id tambien debe ser positivo en el path (../2)
if (end($connectionURI) > 0) {
    $id = $connectionURI[count($connectionURI)];
    $entity = $connectionURI[count($connectionURI) - 1];
} else {
    $entity = $connectionURI[count($connectionURI)];
}

$bodyRequest = file_get_contents("php://input");


switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        $myDb = new DB();
        $newProduct = new Product;
        $newProduct->jsonConstruct($bodyRequest);
        $newProduct->DB_insert($myDb->connection);
        return_response(200, "OK", $newProduct);
        break;

    case 'GET':
        $myDb = new DB();
        if (isset($id)) {
            $productToGet = new Product;
            $productToGet->setId($id);
            $productToGet->DB_selectOne($myDb->connection);
            return_response(200, "OK", $productToGet);
        } else {
            return_response(200, "OK", User::DB_selectAll($myDb->connection));
        }
        break;

    case 'PUT':
        if (isset($id)) {
            $myDb = new DB();
            $productToPut = new Product;
            $productToPut->jsonConstruct($bodyRequest);
            $productToPut->setId($id);
            $productToPut->DB_update($myDb->connection);
            return_response(200, "OK", $productToPut);
        } else {
            return_response(405, "Method Not Allowed", null);
        }
        break;

    case 'DELETE':
        if (isset($id)) {
            $myDb = new DB();
            $productToDelete = new Product;
            $productToDelete->setId($id);
            $productToDelete->DB_delete($myDb->connection);
            return_response(200, "OK", $productToDelete);
        } else {
            return_response(405, "Method Not Allowed", null);
        }
        break;

    default:
        return_response(405, "Method Not Allowed", null);
        break;
}

