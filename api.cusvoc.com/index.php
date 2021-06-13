<?php

header ('Access-Control-Allow-Origin: *');
header ('Access-Control-Allow-Headers: *');
header ('Access-Control-Allow-Methods: *');
header ('Access-Control-Allow-Credentials: true');
header('Content-type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'connect.php';
require 'functions.php';

$method = $_SERVER['REQUEST_METHOD'];

$q = $_GET['q'];
$params = explode('/',$q);


$type = $params[0];

if (isset($params[2])){
    $stype = $params[2];
}

if (count($params) > 1) {
    $id = $params[1];
}

if ($method === 'GET') {
    if ($type === 'users') {
        if(isset($id)){
            if(isset($stype) && $stype === 'photo'){
                getPhoto($connect, $id);
            }
            elseif (isset($stype) && $stype === 'likes'){
                getLike($connect, $id);
            }
            else {
            getUser($connect, $id);
            }
        }else {
            getUsers($connect);
        }
    }
} elseif ($method === 'POST') {
    if ($type === 'users' && !$stype) {
        addUser($connect, $_POST, $_FILES);
    }
    elseif ($type === 'users' && $stype === 'likes'){
        addLike ($connect, $_POST, $id);
    }
    elseif ($type === 'auth'){
        auth ($connect, $_POST);
    }

} elseif ($method === 'PATCH') {
    if ($type === 'users') {
        if (isset($id)) {
            $data = file_get_contents('php://input');
            $data = json_decode($data, true);
            updateUser($connect, $id, $data);
        }
    }
}  elseif ($method === 'DELETE') {
    if ($type === 'users') {
        if (isset($id)) {
            deleteUser($connect, $id);
        }
    }
}


