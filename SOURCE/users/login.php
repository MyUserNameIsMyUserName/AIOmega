<?php
// required headers
include_once '../included_files/helpers/doc_header.help.php';
set_doc_header();
 
// files needed to connect to database
include_once '../included_files/config/database.php';
include_once '../included_files/objects/user.php';
 
// get database connection
$database = new Database();
$db = $database->getConnection();
 
// instantiate user object
$user = new User($db);
 
// get posted data
$data = json_decode(file_get_contents("php://input"));
 
// set product property values
$user->email = $data->email;
$email_exists = $user->emailExists();
 
// generate json web token
include_once '../included_files/config/jwt_config.php';
include_once '../included_files/vendor/firebase/php-jwt/src/BeforeValidException.php';
include_once '../included_files/vendor/firebase/php-jwt/src/ExpiredException.php';
include_once '../included_files/vendor/firebase/php-jwt/src/SignatureInvalidException.php';
include_once '../included_files/vendor/firebase/php-jwt/src/JWT.php';
use \Firebase\JWT\JWT;
 
// check if email exists and if password is correct
if($email_exists && password_verify($data->password, $user->password)){
    
    $user->get_account_settins();

    $token = array(
       "iss" => $iss,
       "aud" => $aud,
       "iat" => $iat,
       "exp" => $exp,
       "nbf" => $nbf,
       "data" => array(
           "id" => $user->id,
           "firstname" => $user->firstname,
           "lastname" => $user->lastname,
           "email" => $user->email,
           "user_type" => $user->user_type
       )
    );
 
    // set response code
    http_response_code(200);
 
    // generate jwt
    $jwt = JWT::encode($token, $key);
    echo json_encode(
            array(
                "message" => "Successful login.",
                "jwt" => $jwt,
                "data" => $user
            )
        );
 
}
 
// login failed
else{
 
    // set response code
    http_response_code(401);
 
    // tell the user login failed
    echo json_encode(array("message" => "Login failed."));
}
?>