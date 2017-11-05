<?php
    session_start();

    if (!isset($_SESSION['logged']))
    {
        header('Location: index.php');
        exit();
    }

    if (!(isset($_GET['user'])))
    {
        header('Location: main.php');
        exit();
    }

    if (!(isset($_GET['category']) && isset($_GET['sort_by'])))
    {
        header('Location: user.php?user='.$_GET['user'].'&category=wszystkie&sort_by=najnowsze');
        exit();
    }

    if (!isset($_GET['page']))
    {
        header('Location: user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page=1');
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
                    if (isset($_SESSION['info']))
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
                    if (isset($_SESSION['info']))
                    {
                        echo $_SESSION['info'];
                        unset($_SESSION['info']);
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
                            $user_id = $_SESSION['id'];
                            $user_name = $_SESSION['user'];
                            $owner_name = $_GET['user'];
                            if ($_GET['sort_by']=='najnowsze')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' ORDER BY addition_date DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' AND category='$category' ORDER BY addition_date DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najstarsze')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' ORDER BY addition_date ASC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' AND category='$category' ORDER BY addition_date ASC");
                                }
                            }
                            if ($_GET['sort_by']=='najwiecej_pobran')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' ORDER BY download_counter DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' AND category='$category' ORDER BY download_counter DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najlepsza_ocena')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' ORDER BY rating DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' AND category='$category' ORDER BY rating DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najwiecej_ocen')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' ORDER BY rating_counter DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE user_name='$owner_name' AND category='$category' ORDER BY rating_counter DESC");
                                }
                            }
                            $how_many_rows = $request_result->num_rows;

                            $how_many_sounds_on_page = 10;
                            $how_many_pages = $how_many_rows / $how_many_sounds_on_page;

                            echo $owner_name;

                            for ($i=0; $i<$how_many_rows; $i++)
                            {
                                $row = $request_result->fetch_assoc();
                                if ( $i>=(($_GET['page']-1)*$how_many_sounds_on_page) && ($i<$_GET['page']*10) && ($i<$how_many_rows) ) //wyswietlanie wybranych do page
                                {
                                    echo '<div class="sound">';
                                    $file_name = html_entity_decode($row['name'], ENT_QUOTES, "UTF-8");
                                    
                                    echo '<label style="font-size:26px;">'.$file_name.'</label><br />';
                                    $localization = 'uploads/';
                                    $file_name = str_replace(" ","%20",$file_name);
                                    $file_name = str_replace("ą","Ä…",$file_name);
                                    $cenzura = array("Ą", "Ć", "Ę", "Ł", "Ń", "Ó", "Ś", "Ż", "Ź", "ć", "ę", "ł", "ń", "ó", "ś", "ż", "ź");
                                    $zamiana = array("Ä„", "Ä†", "Ä", "Ĺ", "Ĺ", "Ă“", "Ĺš", "Ĺ»", "Ĺą", "Ä‡", "Ä™", "Ĺ‚", "Ĺ„", "Ăł", "Ĺ›", "ĹĽ", "Ĺş");
                                    $file_name = str_replace($cenzura, $zamiana, $file_name);

                                    echo '<audio controls>';
                                        echo "<source src=".$localization.$file_name." type='audio/mp3' />";
                                    echo '</audio> <br />';
                                    $download_counter = $row['download_counter'];
                                    echo 'Ilość pobrań: '.$download_counter.' ';
                                    $rating = $row['rating'];
                                    $rating_counter = $row['rating_counter'];
                                    echo 'Ocena: '.$rating.' ('.$rating_counter.')<br />';
                                    
                                    $rates_request_result = $connection->query("SELECT * FROM rates WHERE user_id='$user_id'");
                                    $how_many_rows_in_rates = $rates_request_result->num_rows;
                                    $rated = false;
                                    $rate = '';
                                    for ($j=0; $j<$how_many_rows_in_rates; $j++)
                                    {
                                        $rates_row = $rates_request_result->fetch_assoc();
                                        if($rates_row['sound_id'] == $row['id'])
                                        {
                                            $rated = true;
                                            $rate = $rates_row['rate'];
                                        }
                                    }
                                    echo '<div style="display:inline-block;">';
                                    if ($rated == false)
                                    {
                                        echo ' <div style="float: left;"><form action="rate_sound.php" method="post"><input src="img/plus.png" title="Oceń na plus" width="40" height="40" type="image" value="'.$row['id'].'" name="rate_sound_button_+"></input></form></div>';
                                        echo ' <div style="float: left; margin-left: 5px;"><form action="rate_sound.php" method="post"><input src="img/minus.png" title="Oceń na minus" width="40" height="40" type="image" value="'.$row['id'].'" name="rate_sound_button_-"></input></form></div>';
                                        
                                    }
                                    else
                                    {
                                        if ($rate=='+')
                                        echo '<div style="float: left; margin-top: 10px; font-size: 20px;">Twoja ocena: <img src="img/plus.png" width="16" height="16"/></div>';
                                        else
                                        echo '<div style="float: left; margin-top: 10px; font-size: 20px;">Twoja ocena: <img src="img/minus.png" width="16" height="16"/></div>';
                                    }
                                    echo ' <div style="float: left; margin-left: 20px;"><form action="download_sound.php" method="post"><input src="img/download.png" title="Pobierz" width="40" height="40" type="image" value="'.$row['id'].'" name="download_sound_button"></input></form></div>';
                                    echo '</div>';
                                    echo '<div style="clear: both;"></div>';

                                    echo '</div>';
                                }
                            }

                            if ($_GET['page'] > 1)
                            {
                                $previous = $_GET['page'] - 1;
                                echo '<a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$previous.'" ><div class="page"><<</div></a>';
                            }

                            $page = $_GET['page'];
                            for ($i = $page-2; $i <= $page+2 ; $i++)
                            {
                                if ($i>0 && $i<=round($how_many_pages+0.49))
                                {
                                    if ($_GET['page'] == $i)
                                    {
                                        echo '<a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page" style="color: #ff6d0b;">'.$i.'</div></a>';
                                    }
                                    else
                                    {
                                        echo '<a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page">'.$i.'</div></a>';
                                    }
                                }
                            }
                            
                            if ($_GET['page'] < $how_many_pages)
                            {
                                $next = $_GET['page'] + 1;
                                echo '<a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$next.'" ><div class="page">>></div></a>';
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
                <div id="content_list_category">
                KATEGORIE
                <ol style="margin-top: 20px;">
                    <?php
                        echo
                        '<a href="user.php?user='.$_GET['user'].'&category=wszystkie&sort_by='.$_GET['sort_by'].'" ><li>Wszystkie</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category=natura&sort_by='.$_GET['sort_by'].'" ><li>Natura</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category=akcja&sort_by='.$_GET['sort_by'].'" ><li>Akcja</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category=obyczajowe&sort_by='.$_GET['sort_by'].'" ><li>Obyczajowe</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category=inne&sort_by='.$_GET['sort_by'].'" ><li>Inne</li></a>';
                    ?>
				</ol>
                </div>

                <div id="content_list_sort">
                SORTOWANIE
                <ol style="margin-top: 20px;">
                    <?php
                        echo
                        '<a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by=najnowsze" ><li>Najnowsze</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by=najstarsze" ><li>Najstarsze</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by=najwiecej_pobran" ><li>Najwięcej pobrań</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by=najlepsza_ocena" ><li>Najlepsza ocena</li></a>
                        <a href="user.php?user='.$_GET['user'].'&category='.$_GET['category'].'&sort_by=najwiecej_ocen" ><li>Najwięcej ocen</li></a>';
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