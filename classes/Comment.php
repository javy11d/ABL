<?php
class Comment{

    public static function createComment($commentBody, $postId, $userId){
        
        

        if (strlen($commentBody) > 200 || strlen($commentBody) < 1) {
                die('Invalid length!');
        }

        if (!DB::query('SELECT id FROM posts WHERE id=:postid', array(':postid'=>$postId))) {
            echo 'Invalid post ID';
        } else {
            DB::query('INSERT INTO comments VALUES (\'\', :comment, :userid, NOW(), :postid)', array(':comment'=>$commentBody, ':userid'=>$userId, ':postid'=>$postId));
        }

        

    }

    public static function displayComments($postId) {
        echo "Comments: <br><br>";
        $comments = DB::query('SELECT comments.comment, users.username FROM comments, users WHERE post_id = :postid AND comments.user_id = users.id', array(':postid'=>$postId));
        foreach($comments as $comment) {
                echo $comment['comment']." ~ ".$comment['username']."<br>";
        }
        echo "<br><hr />";
    }



}

?>