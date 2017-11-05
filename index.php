<?php
    session_start();

    if ((isset($_SESSION['logged'])) && ($_SESSION['logged']==true))
    {
        header('Location: main.php');
        exit();
    }

    if (!(isset($_GET['category']) && isset($_GET['sort_by'])))
    {
        header('Location: index.php?category=wszystkie&sort_by=najnowsze');
        exit();
    }

    if (!isset($_GET['page']))
    {
        header('Location: index.php?category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page=1');
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
    <link rel="Shortcut icon" href="img/icon.png" />
    <link rel="stylesheet" href="style.css" type="text/css" />
    <link href='https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>

</head>

<body>
    <div id="wrapper">
        <div id="header">

            <div id="logo">
                <a href="index.php"><img src="img/logo.png" width="800" height="170"/></a>
            </div>

            <div id=login>
                <form action="login.php" method="post">
                    <input type="text" name="login" placeholder="Login" />
                    <input type="password" name="password" placeholder="Hasło" />
                    <input type="submit" value="Zaloguj się" style="margin-bottom: 5px;" />
                </form>
                <a href="signup.php"><span>Nie masz konta? - zarejestruj się</span></a>
            </div>

            <div style="clear: both;"></div>

        </div>

        <div id=content>
            <div id="content_box" style="min-height: 450px;">
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
                            if ($_GET['sort_by']=='najnowsze')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds ORDER BY addition_date DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE category='$category' ORDER BY addition_date DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najstarsze')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds ORDER BY addition_date ASC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE category='$category' ORDER BY addition_date ASC");
                                }
                            }
                            if ($_GET['sort_by']=='najwiecej_pobran')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds ORDER BY download_counter DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE category='$category' ORDER BY download_counter DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najlepsza_ocena')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds ORDER BY rating DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE category='$category' ORDER BY rating DESC");
                                }
                            }
                            if ($_GET['sort_by']=='najwiecej_ocen')
                            {
                                if ($_GET['category']=='wszystkie')
                                {
                                    $request_result = $connection->query("SELECT * FROM sounds ORDER BY rating_counter DESC");
                                }
                                else
                                {
                                    $category = $_GET['category'];
                                    $request_result = $connection->query("SELECT * FROM sounds WHERE category='$category' ORDER BY rating_counter DESC");
                                }
                            }
                            $how_many_rows = $request_result->num_rows;
                            if ($how_many_rows==0)
                            {
                                echo 'Brak dźwięków';
                            }

                            $how_many_sounds_on_page = 10;
                            $how_many_pages = $how_many_rows / $how_many_sounds_on_page;

                            for ($i=0; $i<$how_many_rows; $i++)
                            {
                                $row = $request_result->fetch_assoc();
                                if ( $i>=(($_GET['page']-1)*$how_many_sounds_on_page) && ($i<$_GET['page']*$how_many_sounds_on_page) && ($i<$how_many_rows) ) //wyswietlanie wybranych do page
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
                                    
                                    $owner_name = $row['user_name'];
                                    echo 'Dodał: '.$owner_name.' ';
                                    $download_counter = $row['download_counter'];
                                    echo 'Ilość pobrań: '.$download_counter.' ';
                                    $rating = $row['rating'];
                                    $rating_counter = $row['rating_counter'];
                                    echo 'Ocena: '.$rating.' ('.$rating_counter.')';
                                    echo '</div>';
                                }
                            }

                            if ($_GET['page'] > 1)
                            {
                                $previous = $_GET['page'] - 1;
                                echo '<a href="index.php?category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$previous.'" ><div class="page"><<</div></a>';
                            }

                            $page = $_GET['page'];
                            for ($i = $page-2; $i <= $page+2 ; $i++)
                            {
                                if ($i>0 && $i<=round($how_many_pages+0.49))
                                {
                                    if ($_GET['page'] == $i)
                                    {
                                        echo '<a href="index.php?category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page" style="color: #ff6d0b;">'.$i.'</div></a>';
                                    }
                                    else
                                    {
                                        echo '<a href="index.php?category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$i.'" ><div class="page">'.$i.'</div></a>';
                                    }
                                }
                            }
                            
                            if ($_GET['page'] < $how_many_pages)
                            {
                                $next = $_GET['page'] + 1;
                                echo '<a href="index.php?category='.$_GET['category'].'&sort_by='.$_GET['sort_by'].'&page='.$next.'" ><div class="page">>></div></a>';
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
            <div id="info">
                <?php
                    if(isset($_SESSION['info']))
                    {
                    echo $_SESSION['info'].'<br/>';
                    unset($_SESSION['info']);
                    }
                ?>
                Zaloguj się, aby móc oceniać, pobierać i dodawać własne dźwięki. Zyskasz również dostęp do wyszukiwarki dźwięków.
            </div>

            <a href="about.php">
                <div id=about style="margin-top: 0px;">
                    O stronie
                </div>
            </a>

            <div id="content_list">
                <div id="content_list_category">
                KATEGORIE
                <ol style="margin-top: 20px;">
                    <?php
                        echo
                        '<a href="index.php?category=wszystkie&sort_by='.$_GET['sort_by'].'" ><li>Wszystkie</li></a>
                        <a href="index.php?category=natura&sort_by='.$_GET['sort_by'].'" ><li>Natura</li></a>
                        <a href="index.php?category=akcja&sort_by='.$_GET['sort_by'].'" ><li>Akcja</li></a>
                        <a href="index.php?category=obyczajowe&sort_by='.$_GET['sort_by'].'" ><li>Obyczajowe</li></a>
                        <a href="index.php?category=inne&sort_by='.$_GET['sort_by'].'" ><li>Inne</li></a>';
                    ?>
				</ol>
                </div>

                <div id="content_list_sort">
                SORTOWANIE
                <ol style="margin-top: 20px;">
                    <?php
                        echo
                        '<a href="index.php?category='.$_GET['category'].'&sort_by=najnowsze" ><li>Najnowsze</li></a>
                        <a href="index.php?category='.$_GET['category'].'&sort_by=najstarsze" ><li>Najstarsze</li></a>
                        <a href="index.php?category='.$_GET['category'].'&sort_by=najwiecej_pobran" ><li>Najwięcej pobrań</li></a>
                        <a href="index.php?category='.$_GET['category'].'&sort_by=najlepsza_ocena" ><li>Najlepsza ocena</li></a>
                        <a href="index.php?category='.$_GET['category'].'&sort_by=najwiecej_ocen" ><li>Najwięcej ocen</li></a>';
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
    </div>

</body>

</html>