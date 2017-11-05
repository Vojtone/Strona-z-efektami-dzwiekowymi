<?php
    session_start();

    if (isset($_POST['delete_sound_button']))
    {
        
        //Zapamiętaj wprowadzone dane
        $sound_id = $_POST['delete_sound_button'];
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
                    if (!unlink($localization.$file_name))
                    {
                    echo ("Error deleting $file_name");
                    }
                    else
                    {
                    echo ("Deleted $file_name");
                    }
                }

                if ($connection->query("DELETE FROM sounds WHERE id=$sound_id"))
                {
                    if (!($connection->query("DELETE FROM downloads WHERE sound_id=$sound_id")))
                    {
                        throw new Exception($connection->error);
                    }
                    if (!($connection->query("DELETE FROM rates WHERE sound_id=$sound_id")))
                    {
                        throw new Exception($connection->error);
                    }
                    if (!($connection->query("UPDATE users SET amount_of_sounds=amount_of_sounds-1 WHERE id='$user_id'")))
                    {
                        throw new Exception($connection->error);
                    }
                    $_SESSION['info'] = '<span style="color:green"><br />Dźwięk został usunięty!</span>';
                    header('Location: user_sounds.php');
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
        header('Location: user_sounds.php');
    }

?>