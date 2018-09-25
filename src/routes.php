<?php

use Slim\Http\Request;
use Slim\Http\Response;


// Routes

//$app->get('/[{name}]', function (Request $request, Response $response, array $args) {
//    // Sample log message
//    $this->logger->info("Slim-Skeleton '/' route");
//
//    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
//});


$app->get('/test', function (Request $request, Response $response, array $args) {
    // Sample log message
    $this->logger->info("test app");

    // Render index view
    //return $this->renderer->render($response, 'index.phtml', $args);
});

$app->post('/visitor/login', function(Request $request, Response $response, array $args)
{
    $data = $request->getParsedBody();

    $userName = "";
    $password = "";

    if(!$data)
    {
        $response->write("Please enter valid credentials....");
    }
    if(array_key_exists("user_name",$data))
    {
        $userName = $data["user_name"];
        $password = $data["password"];

    }
    else
    {
        $response->write("User name or password cannot be null....");
    }


    $sql = "SELECT id,userName FROM `users` WHERE userName = :userName AND password = :password";

    try{
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "userName" => $userName,
            "password" => $password
        ]);

        $result = $stmt->fetchAll();

        if(!$result){
            $this->logger->info("paper does not exits");
            return false;
        }
        else{
            return $response->withJson($result[0]) ;
        }



    } catch (\PDOException $e){
        $this->logger->info("Cannot get requested paper Information from DB " . $e);
        return false;
    }
    //$response->write($data["body"]);
});


$app->get('/{userId}/get-data', function(Request $request, Response $response, array $args){
    $userId = $request->getAttribute("userId");



    return $response->withJson($userId);
});


$app->post('/parent/login', function(Request $request, Response $response, array $args)
{
    $data = $request->getParsedBody();

    $userName = "";
    $password = "";

    if(!$data)
    {
        $response->write("Please enter valid credentials....");
    }
    if(array_key_exists("user_name",$data))
    {
        $userName = $data["user_name"];
        $password = $data["password"];

    }
    else
    {
        $response->write("User name or password cannot be null....");
    }


    $sql = "SELECT id,userName FROM `users` WHERE userName = :userName AND password = :password";

    try{
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "userName" => $userName,
            "password" => $password
        ]);

        $result = $stmt->fetchAll();

        if(!$result){
            $this->logger->info("paper does not exits");
            return false;
        }
        else{
            return $response->withJson($result[0]) ;
        }



    } catch (\PDOException $e){
        $this->logger->info("Cannot get requested paper Information from DB " . $e);
        return false;
    }
    //$response->write($data["body"]);
});