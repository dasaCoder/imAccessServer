<?php

namespace App\Controllers;

use phpDocumentor\Reflection\Types\Null_;


class UserController extends Controller
{

    public function __construct($c)
    {
        parent::__construct($c);
    }

    //Check App User Login
    public function checkAppUserLogin($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        //Check Body
        if(!$parsedBody){
            return $response->withJSON(true, "request body wrong format");
        }

        //Check Email
        if(array_key_exists('email', $parsedBody)){
            $email = $parsedBody['email'];
            if(!$email){
                return $response->withJSON($this->setResponse(true, "email cannot be null"));
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $this->logger->info("email " . $email);
                return $response->withJSON($this->setResponse(true, "email is invalid"));

            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have email"));
        }

        //Check Password
        if(array_key_exists('password', $parsedBody)){
            $password = $parsedBody['password'];
            if(!$password){
                return $response->withJSON($this->setResponse(true, "password cannot be null"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have password"));
        }

        //Check FCM Token
        if(array_key_exists('fcm_token', $parsedBody)){
            $token = $parsedBody['fcm_token'];
            if(!$token){
                return $response->withJson($this->setResponse(true, "fcm token cannot be null"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have fcm_token"));
        }

        //Check Email For User
        $emailMatch = $this->User->getUserByEmail($email);

        if(!$emailMatch){
            $data = array(
                "can_login" => false,
                "error_code" => "INVALID_USER"
            );
            return $response->withJSON($this->setResponse(true, "cannot find user by email", $data));
        }

        //Check Password & Get User
        $user = $this->User->checkUserPassword($email, $password);

        if(!$user){
            $data = array(
                "can_login" => false,
                "error_code" => "WRONG_PASSWORD"
            );
            return $response->withJSON($this->setResponse(true, "password doesn\'t match", $data));
        }

        //Save User FCM Token
        if($token){
            $tokenResult = $this->User->addUserFcmToken($token, $user['user_id']);
            if(!$tokenResult){
                return $response->withJSON($this->setResponse(true, "cannot save device FCM token"));
            }
        }

        //Valid User
        $userReturnData = array(
            "user_id" => $user['user_id'],
            "name" => $user['name'],
            "email" => $user['email'],
        );

        $data = array(
            "can_login" => true,
            "user" => $userReturnData    
        );

        $this->logger->info($user['user_id'] . " user is logged into vibhaga app");
        return $response->withJSON($this->setResponse(false, null, $data));
    }

    public function createAppUser($request, $response, $args){

        $parsedBody = $request->getParsedBody();

        //Check Body        
        if(!$parsedBody){
            return $response->withJSON($this->setResponse(true, "request body wrong format"));
        }

        //Check Name
        if(array_key_exists('name', $parsedBody)){
            $name = $parsedBody['name'];
            if(!$name){
                return $response->withJSON($this->setResponse(true, "name cannot be null"));
            }
            if(!preg_match('/^[a-zA-Z\s]+$/', $name)){
                return $response->withJSON($this->setResponse(true, "invalid characters in name"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have name"));
        }

        //Check Email
        if (array_key_exists('email', $parsedBody)) {
            $email = $parsedBody['email'];
            if(!$email){
                return $response->withJSON($this->setResponse(true, "email cannot be null"));
            }
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                return $response->withJSON($this->setResponse(true, "invalid email"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have email"));
        }

        //Check Mobile
        if (array_key_exists('mobile', $parsedBody)) {
            $mobile = $parsedBody['mobile'];
            if(!preg_match('/^[0-9]{10}+$/', $mobile)){
                return $response->withJSON($this->setResponse(true, "invalid mobile"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have mobile"));
        }

        

        //Check Password
        if (array_key_exists('password', $parsedBody)) {
            $password = $parsedBody['password'];
            if (!$password) {
                return $response->withJSON($this->setResponse(true, "password cannot be null"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have password"));
        }



        //Check User Exists
        if($this->User->isUserExists($email)){
            return $response->withJson($this->setResponse(true, 'this email already exists'));
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $annonymousUserId = null;

        if (array_key_exists('user_id', $parsedBody)) {
            $annonymousUserId = $parsedBody['user_id'];
        }

        //Add User
        $userId = $this->User->addUser($name, $email, $passwordHash, $mobile, $annonymousUserId);

        if(!$userId){
            $this->logger->error("cannot add $name user using User->addUser");
            return $response->withJson($this->setResponse(true, "cannot add $name user"));
        }

        $data = [
            "name" => $name,
            "email" => $email
        ];

        return $response->withJSON($this->setResponse(false, "user added succesully", $data));

    }

    public function createGoogleUser($request, $response, $args)
    {
       
    }

    public function getAnnonymousUser($request, $response, $args){
        $userId = $this->User->addAnnonymousUser();

        if(!$userId){
            return $response->withJSON($this->setResponse(true, "cannot get annonymous user id"));
        }

        $data = array(
            "user_id" => $userId
        );

        return $response->withJSON($this->setResponse(false, "annonymous user added", $data));
    }

    public function getUserInfo($request, $response, $args)
    {
        $userId = $request->getAttribute('user_id');

        $user = $this->User->getUserInfo($userId);

        if(!$user){
            $this->logger->info("Cannot get User Information from User->getUserInfo");
            return $response->withJSON($this->setResponse(true, "cannot get user info"));
        }
        
        $data = array(
            "name" => $user['name'],
            "email" => $user['email'],
            "mobile" => $user['mobile'],
            "role" => $user['role_id'],
            "notification_active" => boolval($user['notification_active']),
            "email_active" => boolval($user['email_active'])
        );

        return $response->withJSON($this->setResponse(false, null, $data));
    }

    public function getUserExamCategories($request, $response, $args)
    {
        $userId = $request->getAttribute('user_id');

        if(!$this->User->getUserById($userId)){
            return $response->withJSON($this->setResponse(true, "cannot find user by id"));
        }

        $userCategories = $this->User->getExamCategoriesOfUser($userId);

        if(!$userCategories){
            $this->logger->info("Cannot get User Information from User->getExamCategoriesOfUser");
            return $response->withJSON($this->setResponse(true, "cannot get user exam categories"));
        }

        $cat_array = [];

        foreach ($userCategories as $category){
                $cat_array[] = $category['exam_category_id'];
            }
        
        $data['exam_categories'] = $cat_array;

        return $response->withJSON($this->setResponse(false, null, $data));
    }

    public function SaveUserExamCategories($request, $response, $args){
        $parsedBody = $request->getParsedBody();

        //check body
        if(!$parsedBody){
            return $response->withJSON($this->setResponse(true, "request body wrong format"));
        }

        //check user id
        if(array_key_exists('user_id', $parsedBody)){
            $userId = $parsedBody['user_id'];
            if(!$userId){
                return $response->withJSON($this->setResponse(true, "user_id cannot be null"));
            }

            //Check user exists
            //Check Email For User
            $userIdMatch = $this->User->getUserById($userId);

            if(!$userIdMatch){
                $data = array(
                    "error_code" => "INVALID_USER"
                );
                return $response->withJSON($this->setResponse(true, "cannot find user by user id", $data));
            }

        } else {
            return $response->withJSON($this->setResponse(true, "body should have user_id"));
        }
        //check exam categories
        if(array_key_exists('exam_categories', $parsedBody)){
            $examCategories = $parsedBody['exam_categories'];
            if(!$examCategories){
                return $response->withJSON($this->setResponse(true, "exam_categories cannot be null"));
            } 
        } else {
            return $response->withJSON($this->setResponse(true, "body should have exam_categories"));
        }

        //remove users' exam categories
        $removeExamCategoriesResult = $this->User->removeExamCategories($userId);

        if(!$removeExamCategoriesResult){
            return $response->withJSON($this->setResponse(true, "cannot update exam categories"));
        }

        $invalidExamCategories = '';

        foreach ($examCategories as $examCategoryId) {
            $examCategoryAddResult = $this->User->addExamCategory($userId, $examCategoryId);
            $this->logger->info("examCategoryAddResult " . json_encode($examCategoryAddResult));

            if(!$examCategoryAddResult){
                $invalidExamCategories .= $examCategoryId . ', ';
            }
        }

        if($invalidExamCategories != ''){
            return $response->withJSON($this->setResponse(true, "exam categories wasn\'t added"));
        }

        $data = array(
            'user_id' => $userId
        );

        return $response->withJSON($this->setResponse(false, "user\'s Exam Categories successfully added", $data));
    }

    public function SavePaperResponses($request, $response, $args)
    {
        $parsedBody = $request->getParsedBody();

        //Check Body        
        if(!$parsedBody){
            return $response->withJSON($this->setResponse(true, "request body wrong format"));
        }

        //Check User id
        if(array_key_exists('user_id', $parsedBody)){
            $user_id = $parsedBody['user_id'];
            if(!$user_id){
                return $response->withJSON($this->setResponse(true, "user_id cannot be null"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have user_id"));
        }

        //Check Paper id
        if (array_key_exists('paper_id', $parsedBody)) {
            $paper_id = $parsedBody['paper_id'];
            if(!$paper_id){
                return $response->withJSON($this->setResponse(true, "paper_id cannot be null"));
            }
        } else {
            return $response->withJSON($this->setResponse(true, "body should have paper_id"));
        }

        //Check responses
        if (array_key_exists('responses', $parsedBody)) {
            $responses = $parsedBody['responses'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have responses"));
        }

        //check attempt

        //Check marks
        if (array_key_exists('marks', $parsedBody)) {
            $marks = $parsedBody['marks'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have marks"));
        }

        //Check answered
        if (array_key_exists('answered', $parsedBody)) {
            $answered = $parsedBody['answered'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have answered"));
        }

        //Check not answered
        if (array_key_exists('not_answered', $parsedBody)) {
            $not_answered = $parsedBody['answered'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have not_answered"));
        }

        //Check correct
        if (array_key_exists('correct', $parsedBody)) {
            $correct = $parsedBody['correct'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have correct"));
        }

        //Check incorrect
        if (array_key_exists('incorrect', $parsedBody)) {
            $incorrect = $parsedBody['incorrect'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have correct"));
        }

        //Check time consumed
        if (array_key_exists('time_consumed', $parsedBody)) {
            $time_consumed = $parsedBody['time_consumed'];
        } else {
            return $response->withJSON($this->setResponse(true, "body should have time_consumed"));
        }

        //check Responses length with Numebr of answers in the paper
        $paper = $this->Paper->getPaperInfo($paper_id);

        if(!$paper){
            return $response->withJSON($this->setResponse(true, "canot get paper from the db"));
        }

        if($paper['number_of_questions'] != sizeof($responses)){
            $this->logger->info('sizeof($responses) : ' . sizeof($responses));
            return $response->withJSON($this->setResponse(true, "number of questions mis-match"));
        }
        
        $addedAnswers = $this->Paper->AddUserAnswers($user_id, $paper_id, $responses, $marks, $paper['number_of_questions'], $answered, $not_answered, $correct, $incorrect, $time_consumed);

        if(!$addedAnswers){
            return $response->withJSON($this->setResponse(true, "cannot add users answers to database"));
        }

        return $response->withJSON($this->setResponse(false, "user answers added succesully", $addedAnswers));
        //return $response->withJSON($this->setResponse(false, "user answers added succesully", $data));
    }

    

}