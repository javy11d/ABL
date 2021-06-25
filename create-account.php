<?php  include ( "./inc/header.inc.php" ); ?>
<?php
include('./classes/DB.php');
include('./classes/Mail.php');

if (isset($_POST['signup'])){
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Registration error checking to ensure unique user with valid username and email
    if (!DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))){
        if(strlen($username) >= 3 && strlen($username) <= 32){

            if(preg_match('/^[A-Za-z][A-Za-z0-9]{5,31}$/', $username)){

                if(strlen($password) >= 6 && strlen($password) <= 60){

                if(filter_var($email, FILTER_VALIDATE_EMAIL)){

                    if(!DB::query('SELECT email FROM users WHERE email=:email', array(':email'=>$email))){

                    DB::query('INSERT INTO users VALUES (\'\', :firstname, :lastname, :username, :email, :password)', array(':firstname'=>$firstname,':lastname'=>$lastname,':username'=>$username,':email'=>$email,':password'=>password_hash($password, PASSWORD_BCRYPT)));
                    echo "Success! You should receive a confirmation email shortly.";
                    Mail::sendMail('Welcome to Breadloaf\'s own Social Media Network!', 'Your account has been successfully registered, enjoy the site!', $email);
                    }else{
                        echo "Email in use.";

                    }

                }else{
                    echo "Invalid email.";
                }
                }else{
                    echo "Invalid Password. Passwords must be between 6 and 60 characters in length.";
                }

            }else{
                echo "Invalid Username. Usernames at the moment can only use a-z and numbers and must be between 3 and 32 characters in length.";
            }

        }else{
            echo "Invalid Username. Usernames at the moment can only use a-z and numbers and must be between 3 and 32 characters in length.";
        }
    }else{
        echo "User already exists.";
    }
}
?>
</br /></br />
    <div style="width: 1000px; margin: 0px auto 0px auto;">
        <table>
            <tr>
                <td width=60% valign="top">
                    <h2>Be a Part of Andover Breadloaf's Online Community!</h2>
                </td>
                <td width=40% valign="top">
                <h2>Sign up Below:</h2>
                    <form action="create-account.php" method="POST">
                        <input type="text" name="firstname" size="25" placeholder="First Name">
                        <input type="text" name="lastname" size="25" placeholder="Last Name"> </br /></br />
                        <input type="text" name="username" size="25" placeholder="Username"> </br /></br />
                        <input type="text" name="email" size="25" placeholder="Email">
                        <input type="text" name="email2" size="25" placeholder="Confirm Email"> </br /></br />
                        <input type="text" name="password" size="25" placeholder="Password">
                        <input type="text" name="password2" size="25" placeholder="Confirm Password"> </br /></br />
                        <input type="submit" name="signup" value="Sign Up!">

                    </form>


                </td>

            </tr>

        </table>


     <?php include ("./inc/footer.inc.php"); ?>