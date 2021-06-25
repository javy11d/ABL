<?php
require_once("DB.php");
require_once("Mail.php");

$db = new DB("localhost", "socialnetwork", "root", "");

if ($_SERVER['REQUEST_METHOD'] == "GET") {

        if ($_GET['url'] == "auth") {

        } else if ($_GET['url'] == "users") {

        } else if ($_GET['url'] == "search") {
                $tosearch = explode(" ", $_GET['query']);
                if (count($tosearch) == 1) {
                        $tosearch = str_split($tosearch[0], 2);
                }

                $whereclause = "";
                $paramsarray = array(':body'=>'%'.$_GET['query'].'%');
                for ($i = 0; $i < count($tosearch); $i++) {
                        if ($i % 2) {
                        $whereclause .= " OR body LIKE :p$i ";
                        $paramsarray[":p$i"] = $tosearch[$i];
                        }
                }
                $posts = $db->query('SELECT posts.id, posts.body, users.username, posts.posted_at FROM posts, users WHERE users.id = posts.user_id AND posts.body LIKE :body '.$whereclause.' LIMIT 10', $paramsarray);
                echo json_encode($posts);

        } else if ($_GET['url'] == "comments" && isset($_GET['postid'])) {
                $output = "";
                $comments = $db->query('SELECT comments.comment, users.username FROM comments, users WHERE post_id = :postid AND comments.user_id = users.id', array(':postid' => $_GET['postid']));
                //   $output .= json_encode($comments);
                $output .= "[";
                $output .= "{";
                $output .= '"Comment": "' . "Automatic Comment, I already know it's awesome! Great job :)" . '",';
                $output .= '"CommentedBy": "' . "Javy" . '"';
                $output .= "},";
                foreach ($comments as $comment) {
                        $output .= "{";
                        $output .= '"Comment": "' . $comment['comment'] . '",';
                        $output .= '"CommentedBy": "' . $comment['username'] . '"';
                        $output .= "},";
                }
                $output = substr($output, 0, strlen($output) - 1);
                $output .= "]";
                echo $output;
        } else if ($_GET['url'] == "posts") {

                $token = $_COOKIE['SNID'];

                $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($token)))[0]['user_id'];
                $username = $db->query('SELECT username FROM users WHERE id=:userid', array(':userid' => $userid))[0]['username'];
                $allposts = $db->query('SELECT posts.id, posts.body, posts.posted_at, users.`username`, users.`firstname`, users.`lastname` FROM users, posts
                        WHERE posts.user_id = users.id
                        ORDER BY posts.posted_at DESC;');
                $response = "[";
                foreach ($allposts as $post) {

                        $response .= "{";
                        $response .= '"PostId": ' . $post['id'] . ',';
                        $response .= '"PostBody": "' . $post['body'] . '",';
                        $response .= '"PostedBy": "' . $post['firstname'] . ' ' . $post['lastname'] . '",';
                        $response .= '"Username": "' . $username . '",';
                        $response .= '"PostDate": "' . $post['posted_at'] . '"';
                        $response .= "},";
                }
                $response = substr($response, 0, strlen($response) - 1);
                $response .= "]";

                http_response_code(200);
                echo $response;
        } else if ($_GET['url'] == "profileposts") {



                $userid = $db->query('SELECT id FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['id'];

                $allposts = $db->query('SELECT posts.id, posts.body, posts.posted_at, users.`username`, users.`firstname`, users.`lastname` FROM users, posts
                        WHERE users.id= posts.user_id
                        AND users.id = :userid
                        ORDER BY posts.posted_at DESC;', array(':userid' => $userid));
                $response = "[";
                foreach ($allposts as $post) {

                        $response .= "{";
                        $response .= '"PostId": ' . $post['id'] . ',';
                        $response .= '"PostBody": "' . $post['body'] . '",';
                        $response .= '"PostedBy": "' . $post['firstname'] . ' ' . $post['lastname'] . '",';
                        $response .= '"PostDate": "' . $post['posted_at'] . '"';
                        $response .= "},";
                }
                $response = substr($response, 0, strlen($response) - 1);
                $response .= "]";

                http_response_code(200);
                echo $response;
        }
} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

        if ($_GET['url'] == "users") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $username = $postBody->username;
                $password = $postBody->password;
                $email = $postBody->email;
                $firstname = $postBody->firstname;
                $lastname = $postBody->lastname;

                if (!$db->query('SELECT username FROM users WHERE username=:username', array(':username' => $username))) {
                        if (strlen($username) >= 3 && strlen($username) <= 32) {

                                if (preg_match('/^[A-Za-z][A-Za-z0-9]{5,31}$/', $username)) {

                                        if (strlen($password) >= 6 && strlen($password) <= 60) {

                                                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                                                        if (!$db->query('SELECT email FROM users WHERE email=:email', array(':email' => $email))) {

                                                                $db->query('INSERT INTO users VALUES (\'\', :firstname, :lastname, :username, :email, :password)', array(':firstname' => $firstname, ':lastname' => $lastname, ':username' => $username, ':email' => $email, ':password' => password_hash($password, PASSWORD_BCRYPT)));
                                                                Mail::sendMail('Welcome to Breadloaf\'s own Social Media Network!', 'Your account has been successfully registered, enjoy the site!', $email);
                                                                echo '{ "Success": "Account Created" }';
                                                                http_response_code(200);
                                                        } else {
                                                                echo '{ "Error": "Email in use." }';
                                                                http_response_code(409);
                                                        }
                                                } else {
                                                        echo '{ "Error": "Invalid Email." }';
                                                        http_response_code(409);
                                                }
                                        } else {
                                                echo '{ "Error": "Invalid Password. Passwords must be between 6 and 60 characters in length." }';
                                                http_response_code(409);
                                        }
                                } else {
                                        echo '{ "Error": "Invalid Username. Usernames at the moment can only use a-z and numbers and must be between 3 and 32 characters in length." }';
                                        http_response_code(409);
                                }
                        } else {
                                echo '{ "Error": "Invalid Username. Usernames at the moment can only use a-z and numbers and must be between 3 and 32 characters in length." }';
                                http_response_code(409);
                        }
                } else {
                        echo '{ "Error": "User already exists!" }';
                        http_response_code(409);
                }
        }
        if ($_GET['url'] == "postcomment") {
                $token = $_COOKIE['SNID'];

                $commentData = file_get_contents("php://input");
                $commentData = json_decode($commentData);

                $commentBody = $commentData->commentbody;

                $userId = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($token)))[0]['user_id'];
                $postId = $commentData->postid;

                if (strlen($commentBody) > 200 || strlen($commentBody) < 1) {
                        die('Invalid length!');
                }

                if (!$db->query('SELECT id FROM posts WHERE id=:post_id', array(':post_id' => $postId))) {
                        echo $postId;
                } else {
                        $db->query('INSERT INTO comments VALUES (\'\', :comment, :userid, NOW(), :postid)', array(':comment' => $commentBody, ':userid' => $userId, ':postid' => $postId));
                }
        }

        if ($_GET['url'] == "auth") {
                $postBody = file_get_contents("php://input");
                $postBody = json_decode($postBody);

                $username = $postBody->username;
                $password = $postBody->password;

                if ($db->query('SELECT username FROM users WHERE username=:username', array(':username' => $username))) {
                        if (password_verify($password, $db->query('SELECT password FROM users WHERE username=:username', array(':username' => $username))[0]['password'])) {
                                $cstrong = True;
                                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username' => $username))[0]['id'];
                                $db->query('INSERT INTO login_tokens VALUES (\'\', :token, :user_id)', array(':token' => sha1($token), ':user_id' => $user_id));
                                echo '{ "Token": "' . $token . '" }';
                        } else {
                                echo '{ "Error": "Invalid username or password!" }';
                                http_response_code(401);
                        }
                } else {
                        echo '{ "Error": "Invalid username or password!" }';
                        http_response_code(401);
                }
        }
} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {
        if ($_GET['url'] == "auth") {
                if (isset($_GET['token'])) {
                        if ($db->query("SELECT token FROM login_tokens WHERE token=:token", array(':token' => sha1($_GET['token'])))) {
                                $db->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_GET['token'])));
                                echo '{ "Status": "Success" }';
                                http_response_code(200);
                        } else {
                                echo '{ "Error": "Invalid token" }';
                                http_response_code(400);
                        }
                } else {
                        echo '{ "Error": "Malformed request" }';
                        http_response_code(400);
                }
        }
} else {
        http_response_code(405);
}
