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

    $studentNo = "";
    $password = "";

    if(!$data)
    {
        return $response->withJson(0);

    }
    if(array_key_exists("student_no",$data))
    {
        $studentNo = $data["student_no"];
        $password = $data["password"];

    }
    else
    {
        return $response->withJson(0);

    }

    $sql = "SELECT id,first_name FROM `student` WHERE student_no = :studentNo AND password = :password";

    try{
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "studentNo" => $studentNo,
            "password" => $password
        ]);

        $result = $stmt->fetchAll();

        if(!$result){
            $this->logger->info("paper does not exits");
            return $response->withJson(0);
        }
        else{
            if($result[0] == null){

                return $response->withJson(0);
            }
            else{
                return $response->withJson($result[0]) ;
            }
        }



    } catch (\PDOException $e){
        $this->logger->info("Cannot get requested paper Information from DB " . $e);
        return $response->withJson($result[0]) ;
    }
    //$response->write($data["body"]);
});

$app->get('/parent/{studentId}/get-data', function(Request $request, Response $response, array $args){
    $studentId = $request->getAttribute("studentId");

    $sql = "SELECT (SELECT subjects.code FROM subjects WHERE subjects.id = sr.subject_id) as subject, sr.start_time, sr.end_time, sr.date, sa.isAttended FROM stu_attendance as sa, subject_record as sr
                WHERE sa.subject_rec_id = sr.id
                and sa.stu_id = :studentId";

    try{
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            "studentId" => $studentId,
        ]);

        $result = $stmt->fetchAll();

        if(!$result){
            $this->logger->info("user does not exits");
            return $response->withJson(0);
        }
        else{
            if($result[0] == null){

                return $response->withJson(0);
            }
            else{
                return $response->withJson($result) ;
            }
        }



    } catch (\PDOException $e){
        $this->logger->info("Cannot get requested user Information from DB " . $e);
        return $response->withJson(false) ;
    }

    //return $response->withJson($userId);
});