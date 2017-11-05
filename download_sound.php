<?php
    session_start();

    if (isset($_POST['download_sound_button']))
    {
        
        //Zapamiętaj wprowadzone dane
        $sound_id = $_POST['download_sound_button'];
        $user_id = $_SESSION['id'];

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

                if($request_result = $connection->query("SELECT * FROM sounds WHERE id=$sound_id"))
                {
                    $row = $request_result->fetch_assoc();
                    $file_name = $row['name'];
                    $file_name = html_entity_decode($file_name, ENT_QUOTES, "UTF-8");
                    $localization = 'uploads/';
                    $dir = $localization.$file_name;

                    header("Content-disposition: attachment; filename=$file_name");
                    header("Content-type: audio/mp3");
                    readfile("$dir");
                }

                if ($request_result = $connection->query("SELECT * FROM downloads WHERE user_id = '$user_id' AND sound_id = '$sound_id'"))
                {
                    $how_many_rows = $request_result->num_rows;
                    if ($how_many_rows == 0)
                    {
                        if (!($connection->query("INSERT INTO downloads VALUES (NULL, '$user_id', '$sound_id')")))
                        {
                            throw new Exception($connection->error);
                        }
                        if (!($connection->query("UPDATE sounds SET download_counter = download_counter+1 WHERE id = $sound_id")))
                        {
                            throw new Exception($connection->error);
                        }
                        if ($owner_request_result = $connection->query("SELECT * FROM sounds WHERE id = '$sound_id'"))
                        {
                            $row = $owner_request_result->fetch_assoc();
                            $sound_owner_id = $row['user_id'];
                            if (!($connection->query("UPDATE users SET sum_of_downloads=sum_of_downloads+1 WHERE id='$sound_owner_id'")))
                            {
                                throw new Exception($connection->error);
                            }
                        }
                        else
                        {
                            throw new Exception($connection->error);
                        }
                    }
                }
                else
                {
                    throw new Exception($connection->error);
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
    else
    {
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }

?>