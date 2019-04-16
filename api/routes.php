<?php
/*
TEST URLS
get, post - phonebook/api/contact/
get, put, delete - phonebook/api/contact/1
terms accepted name, lastname, email, phone 
get - phonebook/api/contact/?term[name]=someName&term[email]=someEmail

get, post - phonebook/api/email/
get, put, delete - phonebook/api/email/1

get, post - phonebook/api/phone/
get, put, delete - phonebook/api/phone/1
*/

require_once 'dbConnection.php';

$requestUri = strtolower($_REQUEST['uri']);
$serverMethod  = strtolower($_SERVER['REQUEST_METHOD']);
$acceptedMethods  = [ 'put', 'post', 'delete', 'get'];

$routes = [
  'contact',
  'email',
  'phone'
];

if(!in_array($serverMethod, $acceptedMethods)) {
  return http_response_code(405);
}

if(in_array($serverMethod, ['put', 'delete'] ) && empty($_REQUEST['id'])) {
  return http_response_code(400);
}

if(!in_array($requestUri, $routes )) {
  return http_response_code(404);
}

$id = !empty($_REQUEST['id']) ? $_REQUEST['id'] : null;

require_once "{$requestUri}.php";

$dbClient = new DBConnection();
$className = ucfirst($requestUri);
$model = new $className($dbClient);

if($serverMethod == 'get' && empty($id)) {
  $response = $model->index();
} elseif(!empty($id) && in_array($serverMethod, ['get', 'put', 'delete'])) {
  $response = $model->{$serverMethod}($id);
} else {
  $response = $model->{$serverMethod}();
}

header('Content-type: application/json');
echo json_encode($response);