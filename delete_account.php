<?php
    session_start();

    if (!isset($_SESSION['logged']))
    {
        header('Location: index.php');
        exit();
    }

    if (isset($_POST['password']))
    {
        //Udana walidacja?
        $ok = true;

        //Sprawdzenie czy wpisane hasła są takie same

        $password = $_POST['password'];
        $repeat_password = $_POST['repeat_password'];

        if ($password != $repeat_password)
        {
            $ok = false;
            $_SESSION['err_password'] = '<span style="color:red">Wpisane hasła się różnią</span>';
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
                //Czy wpisane zostało poprawne hasło?
                $user_id = $_SESSION['id'];
                $result = $connection->query("SELECT * FROM users WHERE id='$user_id'");

                if (!$result)
                {
                    throw new Exception($connection->error);
                }
                else
                {
                    $row = $result->fetch_assoc();
                    if(password_verify($password, $row['password']))
                    {
                        $result->close();
                    }
                    else 
                    {
                        if ($ok == true)
                        {
                            $_SESSION['err_password'] = '<span style="color:red">Wpisano nieprawidłowe hasło</span>';  
                            $ok = false;
                        }
                    }
                }

                //Wszystkie testy zaliczone, usunięcie z bazy
                if ($ok == true)
                {
                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_id=$user_id");
                    $how_many_rows = $request_result->num_rows;
                    for ($i=0; $i<$how_many_rows; $i++)
                    {
                        $row = $request_result->fetch_assoc();
                        $file_name = html_entity_decode($row['name'], ENT_QUOTES, "UTF-8");
                        $localization = 'uploads/';
                        if (!unlink($localization.$file_name))
                        {
                            echo ("Error deleting $file_name");
                        }
                        else
                        {
                            echo ("Deleted $file_name");
                        }
                    }
                    if (!($connection->query("DELETE FROM sounds WHERE user_id=$user_id")))
                    {
                        throw new Exception($connection->error);
                    }

                    if ($connection->query("DELETE FROM users WHERE id='$user_id'"))
                    {
                        if (!($connection->query("DELETE FROM downloads WHERE user_id=$user_id")))
                        {
                            throw new Exception($connection->error);
                        }
                        session_unset(); 
                        $_SESSION['info'] = '<span style="color:green">Konto zostało usunięte</span>'; 
                        header('Location: index.php');
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

            <div id=search>
                <?php
                    if(isset($_SESSION['err_password']))
                    {
                        echo '<div class="error">'.$_SESSION['err_password'].'</div>';
                        unset($_SESSION['err_password']);
                    }
                ?>
            </div>

            <div style="clear: both;"></div>

        </div>

        <div id="content">
            <div id=content_box>
                <form method="post">

                    <input type="password" name="password" placeholder="Hasło" /><br />
                    <input type="password" name="repeat_password" placeholder="Powtórz hasło" /><br />
                    <input type="submit" value="Usuń konto" style="background-color: #ff2100;" /> 

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