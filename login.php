<?php include('./classes/DB.php'); 

if(isset($_POST['login'])){
    $username = $_POST['username'];
    $password = $_POST['password'];

    

    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$username))){
            
        if(password_verify($password,  DB::query('SELECT password FROM users WHERE username=:username', array(':username'=>$username))[0]['password'])){
            echo "Logged in! <br>";
            echo '<a href="./profile.php?username='.$username.'">My Profile</a>';

            
            $cstrong = True;
            $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
            
            $user_id = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$username))[0]['id'];
            DB::query('INSERT INTO login_tokens VALUES (null, :token, :user_id)', array(':token'=>sha1($token), ':user_id'=>$user_id));

            setcookie("SNID", $token, time() + 60 * 60 * 24 * 7, '/', NULL, TRUE, TRUE);
            setcookie("SNID_", '1', time() + 60 * 60 * 24 * 3, '/', NULL, TRUE, TRUE);

        }else{
            echo "Incorrect Password.";
        }

    }else{
        echo "User not registered. Please signup to log in";
    }

}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Breadloaf</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<header class="hidden-sm hidden-md hidden-lg">
        <div class="searchbox">
            <form>
                <h1 class="text-left">Breadloaf</h1>
                <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                    <input class="form-control" type="text">
                </div>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                        type="button">MENU <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li role="presentation" class="account"><a href="<?php echo "./profile.php?username=".$username."" ?>">My Profile</a></li>
                        <li class="divider" role="presentation"></li>
                        <li role="presentation"><a href="./index.html">Timeline </a></li>
                        <li role="presentation"><a href="./login.php">Login </a></li>
                        <li role="presentation"><a href="#">Logout </a></li>
                    </ul>
                </div>
            </form>
        </div>
        <hr>
    </header>
    <div>
        <nav class="navbar navbar-default hidden-xs navigation-clean">
            <div class="container">
                <div class="navbar-header"><a class="navbar-brand navbar-link" href="./index.html"><i
                            class="fa fa-home"></i></a>
                    <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navcol-1"><span
                            class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span
                            class="icon-bar"></span><span class="icon-bar"></span></button>
                </div>
                <div class="collapse navbar-collapse" id="navcol-1">
                    <form class="navbar-form navbar-left">
                        <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                            <input class="form-control" type="text">
                        </div>
                    </form>
                    <ul class="nav navbar-nav hidden-md hidden-lg navbar-right">
                        <li role="presentation"><a href="./index.html">My Timeline</a></li>
                        <li class="dropdown open"><a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="true"
                                href="#">User <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li role="presentation" class="account"><a href="<?php echo "./profile.php?username=".$username."" ?>">My Profile</a></li>
                                <li class="divider" role="presentation"></li>
                                <li role="presentation"><a href="./index.html">Timeline </a></li>
                                <li role="presentation"><a href="./login.php">Login </a></li>
                                <li role="presentation"><a href="./logout.php">Logout </a></li>
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav hidden-xs hidden-sm navbar-right">
                        <li role="presentation"><a href="./index.html">Timeline</a></li>
                        <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"
                                href="#">User <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li role="presentation" class="account"><a href="<?php echo "./profile.php?username=".$username."" ?>">My Profile</a></li>
                                <li class="divider" role="presentation"></li>
                                <li role="presentation"><a href="./index.html">Timeline </a></li>
                                <li role="presentation"><a href="./login.php">Login </a></li>
                                <li role="presentation"><a href="./logout.php">Logout </a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>

<body>
    <div class="login-clean">
        <form method="post">
            <h2 class="sr-only">Login Form</h2>
            <div class="illustration"><img src="assets/img/ABL_logo2018.jpg" style="width: 223px;"></div>
            <div class="form-group">
                <input class="form-control" type="text" id="username" name="username" placeholder="Username">
            </div>
            <div class="form-group">
                <input class="form-control" type="password" id="password" name="password" placeholder="Password">
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" name="login" id="login" type="submit" onclick="update_links()" data-bs-hover-animate="shake">Log In</button>
            </div><a href="#" class="forgot">Forgot your email or password?</a></form>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <!-- <script src="assets/js/bs-animation.js"></script> -->

    <script type="text/javascript">
        $('#login').click(function() {
            ('.account').html('<a href="./profile.php?username=' + $("#username").val() + '">My Profile</a>');
                $.ajax({

                        type: "POST",
                        url: "api/auth",
                        processData: false,
                        contentType: "application/json",
                        data: '{ "username": "'+ $("#username").val() +'", "password": "'+ $("#password").val() +'" }',
                        success: function(r) {
                                $('.account').html('<a href="./profile.php?username=' + $("#username").val() + '">My Profile</a>');
                                console.log(r)
                                
                        },
                        error: function(r) {
                                setTimeout(function() {
                                $('[data-bs-hover-animate]').removeClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'));
                                }, 2000)
                                $('[data-bs-hover-animate]').addClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'))
                                console.log(r)
                        }

                });

        });
        function update_links(){
            $('.account').php('<a href="./profile.php?username=' + $("#username").val() + '">My Profile</a>');

        }
    </script>
</body>

</html>
