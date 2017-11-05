<?php
    session_start();

    if (isset($_POST['email']))
    {
        //Udana walidacja?
        $ok = true;

        //Sprawdzenie nickname'a
        $nick = $_POST['nick'];

        if ((strlen($nick) < 3) || (strlen($nick) > 20))
        {
            $ok = false;
            $_SESSION['err_nick'] = "Nick musi posiadać od 3 do 20 znaków";
        }

        if (ctype_alnum($nick) == false)
        {
            $ok = false;
            $_SESSION['err_nick'] = "Nick może składać się tylko z liter i cyfr (bez polskich znaków)";
        }

        //Sprawdzenie poprawności adresu email
        $email = $_POST['email'];
        $email_san = filter_var($email, FILTER_SANITIZE_EMAIL);

        if ((filter_var($email_san, FILTER_VALIDATE_EMAIL) == false) || ($email_san != $email))
        {
            $ok = false;
            $_SESSION['err_email'] = "Podaj poprawny adres email";
        }

        //Sprawdzenie poprawności hasła

        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        if ((strlen($password) < 6) || (strlen($password) > 20))
        {
            $ok = false;
            $_SESSION['err_password'] = "Hasło musi składać się z 6 do 20 znaków";
        }

        if ($password != $repeat_password)
        {
            $ok = false;
            $_SESSION['err_password'] = "Podane hasła się różnią";
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        //Sprawdzanie akceptacji regulaminu
        if (!isset($_POST['rules']))
        {
            $ok = false;
            $_SESSION['err_rules'] = "Musisz zaakceptować regulamin";
        }

        //Sprawdzanie captcha
        $secret = "...";

        $check = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$secret.
        '&response='.$_POST['g-recaptcha-response']);

        $response = json_decode($check);

        if ($response->success == false)
        {
            $ok = false;
            $_SESSION['err_captcha'] = "Potwierdź, że nie jesteś botem";
        }

        //Zapamiętaj wprowadzone dane
        $_SESSION['form_nick'] = $nick;
        $_SESSION['form_email'] = $email;
        $_SESSION['form_password'] = $password;
        $_SESSION['form_repeat_password'] = $repeat_password;
        if(isset($_POST['rules']))
        {
            $_SESSION['form_rules'] = true;
        }

        require_once "connect.php";

        mysqli_report(MYSQLI_REPORT_STRICT);
        try
        {
            $connection = new mysqli($host, $db_user, $db_password, $db_name);
            if ($connection->connect_errno!=0)
            {
                throw new Exception(mysqli_connect_errno());
            }
            else
            {
                //Czy email już istnieje?
                $result = $connection->query("SELECT id FROM users WHERE email='$email'");

                if (!$result)
                {
                    throw new Exception($connection->error);
                }

                $amount_of_emails = $result->num_rows;
                if ($amount_of_emails > 0)
                {
                    $ok = false;
                    $_SESSION['err_email'] = "Ten email jest już zajęty";
                }

                //Czy nick już istnieje?
                $result = $connection->query("SELECT id FROM users WHERE user='$nick'");

                if (!$result)
                {
                    throw new Exception($connection->error);
                }

                $amount_of_users = $result->num_rows;
                if ($amount_of_users > 0)
                {
                    $ok = false;
                    $_SESSION['err_nick'] = "Ta nazwa użytkownika jest już zajęta";
                }

                //Wszystkie testy zaliczone, leci do bazy
                if ($ok == true)
                {
                    $amount_of_sounds = 0;
                    $sum_of_rates = 0;
                    $sum_of_downloads = 0;
                    if ($connection->query("INSERT INTO users VALUES (NULL, '$nick', '$password_hash', '$email', default, '$amount_of_sounds', '$sum_of_rates', '$sum_of_downloads')"))
                    {
                        $_SESSION['info'] = '<span style="color:green">Konto zostało założone!</span>';
                        header('Location: index.php'); //DODANIE INFO O ZAREJESTROWANIU
                    }
                    else
                    {
                        throw new Exception($connection->error);
                    }
                }

                $connection->close();
             }

        }
        catch(Exception $err)
        {
            echo '<span style="color: red;">Błąd serwera. Proszę spróbować później.</span>';
            //echo '<br />Informacja deweloperska: '.$err;
        }
    }


?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <title>efekty-dzwiekowe.pl</title>
    <script src='https://www.google.com/recaptcha/api.js'></script>
    <meta name="description" content="Opis strony" />
    <meta name="keywords" content="słowa, kluczowe" />
    <meta http-equiv="X-UA-Compatibile" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
    
    <script src="jquery-1.12.2.min.js"></script>
    <script type="text/javascript">
		var slide_number = Math.floor(Math.random()*3)+1;
		function hide()
		{
			$("#slide_show").fadeOut(500);
		}
		function change_slide()
		{
			slide_number++; 
			if(slide_number>3) slide_number=1;
			
			var slide = "<img style=\"width: 404px; height: 398px;\" src=\"slide_show/slide" + slide_number + ".jpg\" />"
			
			document.getElementById("slide_show").innerHTML = slide;
			$("#slide_show").fadeIn(800);
			
			setTimeout("change_slide()",5000);
			setTimeout("hide()",4200);
		}
	</script>
</head>

<body onload="change_slide()">
    
    <div id="wrapper">
        <div id="header">

            <div id="logo">
                <a href="index.php"><img src="img/logo.png" width="800" height="170"/></a>
            </div>

            <a href="index.php">
                <div id=return>
                    <div style="padding-top: 55px;">
                        POWRÓT DO STRONY GŁÓWNEJ
                    </div>
                </div>
            </a>
            
        </div>

        <div id="content">
            <div id=sign_up_form>
                <form method="post">

                    <input type="text" value="<?php
                    if (isset($_SESSION['form_nick']))
                    {
                        echo $_SESSION['form_nick'];
                        unset($_SESSION['form_nick']);
                    }
                    ?>" name="nick" placeholder="Nazwa użytkownika" /><br />
                    <?php
                        if(isset($_SESSION['err_nick']))
                        {
                            echo '<div class="error">'.$_SESSION['err_nick'].'</div>';
                            unset($_SESSION['err_nick']);
                        }
                    ?>

                    <input type="text" value="<?php
                    if (isset($_SESSION['form_email']))
                    {
                        echo $_SESSION['form_email'];
                        unset($_SESSION['form_email']);
                    }
                    ?>" name="email" placeholder="E-mail" /><br />

                    <?php
                        if(isset($_SESSION['err_email']))
                        {
                            echo '<div class="error">'.$_SESSION['err_email'].'</div>';
                            unset($_SESSION['err_email']);
                        }
                    ?>

                    <input type="password" value="<?php
                    if (isset($_SESSION['form_password']))
                    {
                        echo $_SESSION['form_password'];
                        unset($_SESSION['form_password']);
                    }
                    ?>" name="password" placeholder="Hasło" /><br />
                    <input type="password" value="<?php
                    if (isset($_SESSION['form_repeat_password']))
                    {
                        echo $_SESSION['form_repeat_password'];
                        unset($_SESSION['form_repeat_password']);
                    }
                    ?>" name="repeat_password" placeholder="Powtórz hasło" /><br />

                    <?php
                        if(isset($_SESSION['err_password']))
                        {
                            echo '<div class="error">'.$_SESSION['err_password'].'</div>';
                            unset($_SESSION['err_password']);
                        }
                    ?>

                    <label style="cursor: pointer;">
                        <input type="checkbox" name="rules"<?php
                        if (isset($_SESSION['form_rules']))
                        {
                            echo "checked";
                            unset($_SESSION['form_rules']);
                        }
                        ?>/>Akceptuję <a href="rules.php" target="blank"><div class="user_id_sound" style="display: inline-block;">regulamin</div></a> <!-- SKASOWAĆ ZMIENNE SESYJNE PO UDANEJ REJESTRACJI I ERR! -->
                    </label>

                    <?php
                        if(isset($_SESSION['err_rules']))
                        {
                            echo '<div class="error">'.$_SESSION['err_rules'].'</div>';
                            unset($_SESSION['err_rules']);
                        }
                    ?>

                    <div class="g-recaptcha" data-sitekey="6LeGxwgUAAAAAMehwGlgJNdHlcBn55lFDJk8EoGo"></div>

                    <?php
                        if(isset($_SESSION['err_captcha']))
                        {
                            echo '<div class="error">'.$_SESSION['err_captcha'].'</div>';
                            unset($_SESSION['err_captcha']);
                        }
                    ?>

                    <br />

                    <input type="submit" value="Zarejestruj się" style="margin-top: 0;" />

                </form>
            </div>

            <div id="slide_show" style="margin-top: 10px;"></div>

        </div>

        <div id="footer">
            <div id="footer_divs">
                <div id="text_footer">
                efekty-dzwiekowe.pl &copy 2017 Strona została wykonana przez Wojciecha Musiała w ramach studiów Inżynieria Akustyczna
                </div>
                <div id="agh_footer">
                        <a href="http://www.agh.edu.pl/" target="blank">
                        <img src="img/logo_agh.png" width="16" height="32"/>
                        </a>
                </div>
            </div>
            <div style="clear: both"></div>
        </div>

    </div>
</body>

</html>
