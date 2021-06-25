<?php  include ( "./inc/header.inc.php" );

include('./classes/DB.php'); 
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Comment.php');

$showTimeline = False;

?>

<form action="index.php" method="post"> 
    <input type="text" name="searchbox" value="">
    <input type="submit" name="search" value="Search">
</form>





<?php


    if(Login::isLoggedIn()){
        $userid = Login::isLoggedIn();
        $showTimeline = True;
    }else{
        die('Not Logged In.');
    }

    if(isset($_POST['searchbox'])){
        $tosearch = explode(" ", $_POST['searchbox']);
        if (count($tosearch) == 1){
            $tosearch = str_split($tosearch[0], 2);
        }

        $whereclause = "";
        $paramsarray = array(':username'=>'%'.$_POST['searchbox'].'%');
        for($i = 0; $i < count($tosearch); $i++){
            $whereclause .= " OR username LIKE :u$i";
            $paramsarray[":u$i"] = $tosearch[$i];
        }
        $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username'.$whereclause.'', $paramsarray);
        print_r($users);

        $whereclause = "";
        $paramsarray = array(':body'=>'%'.$_POST['searchbox'].'%');
        for($i = 0; $i < count($tosearch); $i++){
            if($i % 2){
            $whereclause .= " OR body LIKE :p$i";
            $paramsarray[":p$i"] = $tosearch[$i];
            }
        }

        
        $posts = DB::query('SELECT posts.body FROM posts WHERE posts.body LIKE :body'.$whereclause.'', $paramsarray);
        print_r($posts);
    }

    if(isset($_POST['comment'])){
        Comment::createComment($_POST['commentbody'], $_GET['postid'], $userid);
    }



$allposts = DB::query('SELECT posts.id, posts.body, posts.posted_at, users.`firstname`, users.`lastname` FROM users, posts
WHERE posts.user_id = users.id;');

foreach($allposts as $post){
    echo $post['body']."<br><br>~ ".$post['firstname']." ".$post['lastname']."<br>".$post['posted_at']."<br><br>";
    echo "<form action='index.php?postid=".$post['id']."' method='post'>
        <textarea name='commentbody' rows='4' cols='40'></textarea>
        <input type='submit' name='comment' value='Comment'>
    </form>"."<hr />";
    Comment::displayComments($post['id']);
}

?>





<?php include ("./inc/footer.inc.php"); ?>