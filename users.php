<?php
    session_start();

    if (!isset($_SESSION['logged']))
    {
        header('Location: index.php');
        exit();
    }

    if (!(isset($_GET['sort_by'])))
    {
        header('Location: users.php?sort_by=liczba_dzwiekow');
        exit();
    }

    if (!isset($_GET['page']))
    {
        header('Location: users.php?sort_by='.$_GET['sort_by'].'&page=1');
        exit();
    }
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <title>efekty-dzwiekowe.pl</title>
    <meta name="description" content="Opis strony" />
    <meta name="keywords" content="słowa, kluczowe" />
    <meta http-equiv="X-UA-Compatibile" content="IE=edge,chrome=1" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
</head>

<body>
        

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
                <?php
                    if(isset($_SESSION['info']))
                    {
                    echo $_SESSION['info'];
                    unset($_SESSION['info']);
                    }
                ?>
            </div>

            
            <a href="add_sound.php">
                <div id="add_sound">DODAJ DŹWIĘK</div>
            </a>

            <div style="clear: both;"></div>

            <div id="nav">
                <ol>
					<a href="main.php"><li>Efekty dźwiękowe</li></a>
					<a href="users.php"><li>Użytkownicy</li></a>
                    <a href="about.php"><li>O stronie</li></a>
				</ol>
            </div>

            <div id=search>
                <form action="search.php" method="get">
                    <input type="text" name="search" placeholder="Wyszukaj dźwięk..." style=" padding: 5px; margin: 0px;" />
                    <input type="submit" value="Szukaj" style="width:100px; padding: 6px; margin: 0px;" />
                </form>
            </div>

            <div style="clear: both;"></div>

        </div>

        <div id=content>
            <div id="content_box">
                <?php
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
                            $user_id = $_SESSION['id'];
                            $user_name = $_SESSION['user'];

                            if ($_GET['sort_by']=='liczba_dzwiekow')
                            {
                                $request_result = $connection->query("SELECT * FROM users ORDER BY amount_of_sounds DESC");
                            }
                            if ($_GET['sort_by']=='suma_ocen_dzwiekow')
                            {
                                $request_result = $connection->query("SELECT * FROM users ORDER BY sum_of_rates DESC");
                            }
                            if ($_GET['sort_by']=='liczba_pobran')
                            {
                                $request_result = $connection->query("SELECT * FROM users ORDER BY sum_of_downloads DESC");
                            }
                            $how_many_rows = $request_result->num_rows;
                            if ($how_many_rows==0)
                            {
                                echo 'Brak użytkowników';
                            }

                            $how_many_users_on_page = 10;
                            $how_many_pages = $how_many_rows / $how_many_users_on_page;
                            for ($i=0; $i<$how_many_rows; $i++)
                            {
                                $row = $request_result->fetch_assoc();
                                if ( $i>=(($_GET['page']-1)*$how_many_users_on_page) && ($i<$_GET['page']*10) && ($i<$how_many_rows) ) //wyswietlanie wybranych do page
                                {
                                    echo '<div class="user">';
                                    $user_name = html_entity_decode($row['user'], ENT_QUOTES, "UTF-8");

                                    $amount_of_sounds = $row['amount_of_sounds'];
                                    $sum_of_rates = $row['sum_of_rates'];
                                    $sum_of_downloads = $row['sum_of_downloads'];
                                    echo '<a href="user.php?user='.$user_name.'"><label id="user_name" style="font-size:24px; cursor: pointer;"><label style="color: #ff6d0b;">'.($i+1).'. </label>'.$user_name.'</label></a><br />';
                                    echo 'Dodanych dźwięków: '.$amount_of_sounds.'<br />';
                                    echo 'Suma otrzymanych ocen: '.$sum_of_rates.'<br />';
                                    echo 'Suma pobrań dźwięków: '.$sum_of_downloads.'<br />';

                                    echo '</div>';
                                }
                            }
                            if ($_GET['page'] > 1)
                            {
                                $previous = $_GET['page'] - 1;
                                echo '<a href="users.php?sort_by='.$_GET['sort_by'].'&page='.$previous.'" ><div class="page"><<</div></a>';
                            }

                            $page = $_GET['page'];
                            for ($i = $page-2; $i <= $page+2 ; $i++)
                            {
                                if ($i>0 && $i<=round($how_many_pages+0.49))
                                {
                                    if ($_GET['page'] == $i)
                                    {
                                        echo '<a href="users.php?sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page" style="color: #ff6d0b;">'.$i.'</div></a>';
                                    }
                                    else
                                    {
                                        echo '<a href="users.php?sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page">'.$i.'</div></a>';
                                    }
                                }
                            }
                            
                            if ($_GET['page'] < $how_many_pages)
                            {
                                $next = $_GET['page'] + 1;
                                echo '<a href="users.php?sort_by='.$_GET['sort_by'].'&page='.$next.'" ><div class="page">>></div></a>';
                            }
                            $connection->close();
                        }
                    }
                    catch(Exception $err)
                    {
                        echo '<span style="color: red;">Błąd serwera. Proszę spróbować później.</span>';
                        //echo '<br />Informacja deweloperska: '.$err;
                    }
                ?>
            </div>

            <div id="content_list">
                SORTOWANIE<br />
                <div style="width: 210px; margin-left:auto; margin-right:auto;">
                <ol style="margin-top: 20px;">
                    <?php
                        echo
                        '<a href="users.php?sort_by=liczba_dzwiekow" ><li style=" width: 180px; padding-left:5px; padding-right:5px;">Liczba dźwięków</li></a>
                        <a href="users.php?sort_by=suma_ocen_dzwiekow" ><li style="width: 180px; padding-left:5px; padding-right:5px;">Suma ocen dźwięków</li></a>
                        <a href="users.php?sort_by=liczba_pobran" ><li style="width: 180px; padding-left:5px; padding-right:5px;">Liczba pobrań pobrań</li></a>';
                    ?>
                </ol>
                </div>
            </div>

            <div style="clear: both;"></div>
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

        <?php
            if(isset($_SESSION['error']))
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        ?>
    </div>

</body>

</html>