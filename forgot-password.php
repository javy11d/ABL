<?php   
    include ( "./inc/header.inc.php" );
    include ('./classes/DB.php'); 
    include ('./classes/Mail.php');
?>
<?php 

    if(isset($_POST['resetpassword'])){
        $cstrong = True;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
        $email = $_POST['email'];
        $user_id = DB::query('SELECT id FROM users WHERE email=:email', array(':email'=>$email))[0]['id'];
        DB::query('INSERT INTO password_tokens VALUES (\'\', :token, :user_id)', array(':token'=>$token, ':user_id'=>$user_id));
        echo 'Email sent!';
        Mail::sendMail('Forgot Password', "<a href='http://localhost/ABLDev/change-password.php?token=$token'>Reset Your Password Here!</a>",$email);
        



    }



?>

<h1>Forgot Password</h1>
<form action="forgot-password.php" method="post">
    <input type="text" name="email" value="" placeholder="Email ..."><br><br>
    <input type="submit" name="resetpassword" value="Reset Password">

</form>


<?php include ("./inc/footer.inc.php"); ?>