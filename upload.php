<?php

    session_start();

    if(!($_FILES["fileToUpload"]) || !isset($_POST['category']) || !isset($_POST['keywords']))
    {
        header('Location: add_sound.php');
        exit();
    }

    if ($_FILES["fileToUpload"]["name"] == '')
    {
        $_SESSION['info'] = '<span style="color:red">Musisz wybrać plik</span>';
        header('Location: add_sound.php');
        exit();
    }

    if ($_POST['category'] == '-')
    {
        $_SESSION['info'] = '<span style="color:red">Wybierz kategorię dla dźwięku</span>';
        header('Location: add_sound.php');
        exit();
    }

    if ($_POST['keywords'] == '')
    {
        $_SESSION['info'] = '<span style="color:red">Wpisz jakieś słowa kluczowe</span>';
        header('Location: add_sound.php');
        exit();
    }

    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = pathinfo($target_file,PATHINFO_EXTENSION);

    // Sprawdzenie rozszerzenia pliku
    if($fileType != "mp3" && $fileType != "wav" )
    {
        $_SESSION['info'] = '<span style="color:red">Dodawać można jedynie pliki z rozszerzeniem .mp3 lub .wav</span>';
        $uploadOk = 0;
    }

    // Sprawdzenie rozmiaru pliku wav
    if ($fileType == "wav" && $_FILES["fileToUpload"]["size"] > 3145728)
    {
        $_SESSION['info'] = '<span style="color:red">Plik wav nie może przekraczać rozmiaru 3MB</span>';
        $uploadOk = 0;
    }

    // Sprawdzenie rozmiaru pliku mp3
    if ($fileType == "mp3" && $_FILES["fileToUpload"]["size"] > 1048576)
    {
        $_SESSION['info'] = '<span style="color:red">Plik mp3 nie może przekraczać rozmiaru 1MB</span>';
        $uploadOk = 0;
    }

    // Sprawdzenie czy plik o tej nazwie już istnieje
    if (file_exists($target_file))
    {
        $_SESSION['info'] = '<span style="color:red">Dźwięk o tej nazwie już istnieje</span>';
        $uploadOk = 0;
    }

    // Wszystko ok?
    if ($uploadOk == 0)
    {
    header('Location: add_sound.php');
    }
    else
    {
        
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
        {
            $_SESSION['info'] = '<span style="color:green"><br />Dźwięk został dodany</span>';

            //Dźwięk leci do bazy
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

                    $name = htmlentities($_FILES["fileToUpload"]["name"], ENT_QUOTES, "UTF-8"); //kodowanie znaków specjalnych typu '

                    $category = $_POST['category'];
                    $rating = 0;
                    $rating_counter = 0;
                    $download_counter = 0;
                    $keywords = $_POST['keywords'];

                    if ($connection->query("INSERT INTO sounds VALUES (NULL, '$user_id', '$user_name', '$name', '$category', '$rating', '$rating_counter', '$download_counter', '$keywords', default)"))
                    {
                        if (!($connection->query("UPDATE users SET amount_of_sounds=amount_of_sounds+1 WHERE id='$user_id'")))
                        {
                            throw new Exception($connection->error);
                        }
                    }
                    else
                    {
                        throw new Exception($connection->error);
                    }

                    $connection->close();
                    header('Location: main.php');
                }

            }
            catch(Exception $err)
            {
                echo '<span style="color: red;">Błąd serwera. Proszę spróbować później.</span>';
                //echo '<br />Informacja deweloperska: '.$err;
            }
            
        }
        else
        {
            $_SESSION['info'] = '<span style="color:red">Przepraszamy podczas dodawania pliku wystąpił błąd</span>';
            header('Location: add_sound.php');
        }
    }
?>