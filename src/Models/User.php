<?php

namespace App\Models;

class User extends Model
{
    public function __construct($c)
    {
        parent::__construct($c);
    }

    public function getUserByEmail($email){
        $sql = "SELECT u.user_id, u.email
                FROM users u
                WHERE u.email = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "email" => $email
            ]);
            if($result && $stmt->rowCount() == 1){
                return $stmt->fetchAll()[0];
            } else {
                return false;
            }
        } catch (\PDOException $e){
            $this->logger->info("cannot get user by email from DB " . $e);
            return false;
        }
    }

    public function getUserById($user_id)
    {
        $sql = "SELECT u.user_id, u.name, u.email, u.mobile, u.role_id, u.is_deleted
                FROM users u
                WHERE u.user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $user_id
            ]);
            if($result && $stmt->rowCount() == 1){
                return $stmt->fetchAll()[0];
            } else {
                return false;
            }
        } catch(\PDOException $e){
            $this->logger->info("cannot get user by id from DB " . $e);
        }
    }

    public function addUser($name, $email, $passwordHash, $mobile, $annonymousUserId = null)
    {
        $user = [
            'name' => $name,
            'email' => $email,
            'password' => $passwordHash,
            'mobile' => $mobile,
            'signup_at' => date('Y-m-d H:i:s')
        ];

        if($annonymousUserId){
             $sql = "UPDATE `users`
                     SET `name` = :name, `email` = :email, `password` = :password, `mobile` = :mobile, `signup_at`= :signup_at 
                     WHERE `user_id` = :user_id";

             $user['user_id'] = $annonymousUserId;
        } else {
            $sql = "INSERT INTO `users`(`name`, `email`, `password`, `mobile`, `signup_at`)

                    VALUES (:name, :email, :password, :mobile, :signup_at)";

        }

        try {

           // $this->logger->info($sql);

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($user);

            return boolval($result);

        } catch (\PDOException $e){
            $this->logger->info("cannot add annonymous user to db " .$e);
            return false;
        }
        
    }

    public function checkUserPassword($email, $password){
        $sql = "SELECT u.user_id, u.name, u.email, u.password
                FROM users u
                WHERE u.email = :email";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $email
            ]);
            $result = $stmt->fetchAll();
            $hashedPassword = $result[0]['password'];
            if(password_verify($password, $hashedPassword)){
                return $result[0];
            } else {
                return false;
            }
        } catch (\PDOException $e){
            $this->logger->info("cannot get users password from the DB " . $e);
        }
    }

    public function addAnnonymousUser()
    {
        $sql = "INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `mobile`, `role_id`, `is_deleted`, `created_by`, `created_dtm`, `updated_by`, `updated_dtm`, `signup_at`) 
                VALUES (NULL, 'ano', 'ano', 'ano', 'ano', '5', '0', '-99', CURRENT_TIME(), NULL, NULL, NULL)";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute();

            // if the user is inserted, get the user id created
                if(boolval($result)){

                    return $this->db->lastInsertId(); // get the user id created
                }
                else{
                    return false;
                }


        } catch (\PDOException $e){
            $this->logger->info("cannot add annonymous user to db " .$e);
            return false;
        }
    }

    public function getUserInfo($userId){
        $sql = "SELECT u.user_id, u.name, u.email, u.mobile, u.notification_active, u.email_active, u.signup_at, u.role_id
                FROM users u
                WHERE u.user_id = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $userId
            ]);
            $result = $stmt->fetchAll();
            return $result[0];

        } catch (\PDOException $e){
            $this->logger->info("cannot get users information from the DB " .$e);
        }
    }

    public function removeExamCategories($userId){
        $sql = "UPDATE user_exam_categories
                SET `status` = 0
                WHERE `user_id` = :user_id";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId
            ]);

            return boolval($result);
        } catch (\PDOException $e){
            $this->logger->info("cannot update(delete) users exam categories to DB " . $e);
            return false;
        }
    }

    public function addExamCategory($userId, $examCategoryId){
        $sql = "INSERT INTO user_exam_categories (`user_id`,`exam_category_id`,`status`)
                VALUES (:user_id, :exam_category_id, 1)
                ON DUPLICATE KEY UPDATE `status` = 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'user_id' => $userId,
                'exam_category_id' => $examCategoryId
            ]);
            return boolval($result);
        } catch (\PDOStatement $e){
            $this->logger->info("cannot update(insert) users exam categories to DB " . $e);
            return false;
        }

    }

    public function checkExamCategoriesOfUser($user_id, $cat_id) 
    {
        $sql = "SELECT `user_exam_categories`.`exam_category_id`
                FROM `user_exam_categories`
                WHERE `user_exam_categories`.`user_id` = :user_id 
                    AND `user_exam_categories`.`exam_category_id` = :cat_id
                    AND `user_exam_categories`.`status` != 0";
            
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'cat_id' => $cat_id
            ]);
            $result = $stmt->fetchAll();
            if($result){
                return $result;
            }
            return boolval($result);
        } catch (\PDOException $e){
            $this->logger->info("cannot get users exam categories from DB " . $e);
            return false;
        }
    }

    public function getExamCategoriesOfUser($user_id) 
    {
        $sql = "SELECT `user_exam_categories`.`exam_category_id`
                FROM `user_exam_categories`
                WHERE `user_exam_categories`.`user_id` = :user_id 
                    AND `user_exam_categories`.`status` != 0";
            
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
            ]);
            $result = $stmt->fetchAll();
            if($result){
                return $result;
            }
            return boolval($result);
        } catch (\PDOException $e){
            $this->logger->info("cannot get users exam categories from DB " . $e);
            return false;
        }
    }


    public function isUserExists($email)
    {
        $sql = "SELECT * 
                FROM `users`
                WHERE `users`.`email` = :email";
        
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "email" => $email
            ]);
            if($result && $stmt->rowCount() == 1){
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e){
            $this->logger->info("cannot run isUserExists function " . $e);
            return false;
        }
    }

    public function checkApiKey($api_key)
    {
        $sql = "SELECT `interface` FROM `api_keys` WHERE `api_key` = :api_key";
        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "api_key" => $api_key,
            ]);

            if ($result && $stmt->rowCount() == 1) {
                $this->interface_mode = $stmt->fetchAll()[0]['interface'];
                return $this->interface_mode;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            $this->logger->error('cannot check api key from DB : ' . $e);
            return false;
        }
    }

    public function addUserFcmToken($token, $user_id)
    {
        // $this->logger->info("token : " . $token . " ----- user_id : " . $user_id);
        $sql = "INSERT INTO `user_fcm_token`(`token`, `user_id`, `create_at`) 
                VALUES (:token, :user_id, :time)
                ON DUPLICATE KEY UPDATE `user_id`= :user_id;

                UPDATE `user_fcm_token` 
                SET `create_at` = :time 
                WHERE `token` = :token;";

        try {
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                "user_id" => $user_id,
                "token" => $token,
                "time" => date('Y-m-d H:i:s', time())
            ]);

            return boolval($result);

        } catch (\PDOException $e) {
            $this->logger->error('cannot add user\'s token to the DB : ' . $e);
            return false;
        }
    }

    public function checkPermitedPaperCategory($user_id,$category_id){

        $sql = "SELECT status FROM `user_exam_categories` 
                  WHERE user_id = :user_id 
                  AND exam_category_id=:category_id 
                  AND status = 1";

        try{
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                "category_id"=>$category_id,
                "user_id" => $user_id
            ]);

            $result = $stmt->fetchAll();

            return $result;

        } catch(\PDOException $e){
            $this->logger->info("unable to find user related data". $e);
            return false;
        }
    }



    
}
