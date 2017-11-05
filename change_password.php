<?php
    session_start();

    if (!isset($_SESSION['logged']))
    {
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['new_password']))
    {
        //Udana walidacja?
        $ok = true;

        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $repeat_new_password = $_POST['repeat_new_password'];
        
        
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

                //Czy wpisane zostało poprawne hasło?
                $logged_user_id = $_SESSION['id'];
                $result = $connection->query("SELECT * FROM users WHERE id='$logged_user_id'");

                if (!$result)
                {
                    throw new Exception($connection->error);
                }
                else
                {
                    $row = $result->fetch_assoc();
                    if(password_verify($old_password, $row['password']))
                    {
                        $result->close();
                    }
                    else 
                    {
                        if ($ok == true)
                        {
                            $_SESSION['err_password'] = '<span style="color:red">Wpisano nieprawidłowe stare hasło</span>';  
                            $ok = false;
                        }
                    }
                }

                //Sprawdzenie czy wpisane hasła są takie same
                if ($new_password != $repeat_new_password)
                {
                    $ok = false;
                    $_SESSION['err_password'] = '<span style="color:red">Wpisane hasła się różnią</span>';
                }

                //Sprawdzenie czy hasło zawiera odpowiednią ilość znaków
                if ((strlen($new_password) < 6) || (strlen($new_password) > 20))
                {
                    $ok = false;
                    $_SESSION['err_password'] = '<span style="color:red">Hasło musi składać się z 6 do 20 znaków.</span>';
                }

                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

                //Wszystkie testy zaliczone, zmiana hasła
                if ($ok == true)
                {
                    if ($connection->query("UPDATE users SET password='$password_hash' WHERE id='$logged_user_id'"))
                    {
                        $_SESSION['info'] = '<span style="color:green">Hasło zostało zmienione!</span>';  
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
			
			var slide = "<img style=\"width: 404px; height: 320px;\" src=\"slide_show/slide" + slide_number + ".jpg\" />"
			
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

            <div id=account>
                <?php
                    echo "Zalogowany: ".$_SESSION['user']."<br />";
                ?>
                <ul style="margin-top: 5px;">
                    <li>Twoje konto
                        <ul>
                            <a href="user_sounds.php"><li>Twoje dźwięki</li></a>
                            <a href="change_password.php"><li>Zmień hasło</li></a>
                            <a href="delete_account.php"><li>Usuń konto</li></a>
                        </ul>
                    </li>
                    <li><a href="logout.php">Wyloguj się</a></li>
                </ul>
            </div>

            <div id="add_sound">
            <a href="add_sound.php">DODAJ DŹWIĘK</a>
            </div>

            <div style="clear: both;"></div>

            <div id="nav">
                <ol>
					<a href="main.php"><li>Efekty dźwiękowe</li></a>
					<a href="users.php"><li>Użytkownicy</li></a>
                    <a href="about.php"><li>O stronie</li></a>
				</ol>
            </div>

            <div id=info style="margin-top: 10px;">
                <?php
                        if(isset($_SESSION['info']))
                        {
                        echo $_SESSION['info'];
                        unset($_SESSION['info']);
                        }
                    ?>

                    <?php
                        if(isset($_SESSION['err_password']))
                        {
                            echo '<div class="error">'.$_SESSION['err_password'].'</div>';
                            unset($_SESSION['err_password']);
                        }
                    ?>

                    <?php
                        if(isset($_SESSION['info']))
                        {
                        echo $_SESSION['info'];
                        unset($_SESSION['info']);
                        }
                    ?>
            </div>

            <div style="clear: both;"></div>

        </div>

        <div id="content">
            <div id=content_box>
                <form method="post">

                    <input type="password" name="old_password" placeholder="Stare hasło" /><br />
                    <input type="password" name="new_password" placeholder="Nowe hasło" /><br />
                    <input type="password" name="repeat_new_password" placeholder="Powtórz nowe hasło" /><br />
                    <input type="submit" value="Zmień hasło" />
                    <?php
                        if(isset($_SESSION['info']))
                        {
                        echo $_SESSION['info'];
                        unset($_SESSION['info']);
                        }
                    ?>

                    <?php
                        if(isset($_SESSION['err_password']))
                        {
                            echo '<div class="error">'.$_SESSION['err_password'].'</div>';
                            unset($_SESSION['err_password']);
                        }
                    ?>

                </form>
            </div>

            <div id="slide_show"></div>

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