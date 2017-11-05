<?php
    session_start();

    if (isset($_POST['rate_sound_button_+']))
    {
        $sound_id = $_POST['rate_sound_button_+'];
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

                if ($request_result = $connection->query("SELECT * FROM rates WHERE user_id = '$user_id' AND sound_id = '$sound_id'"))
                {
                    $how_many_rows = $request_result->num_rows;
                    if ($how_many_rows == 0)
                    {
                        $rate = '+';
                        if (!($connection->query("INSERT INTO rates VALUES (NULL, '$user_id', '$sound_id', '$rate')")))
                        {
                            throw new Exception($connection->error);
                        }
                        if (!($connection->query("UPDATE sounds SET rating = rating+1, rating_counter = rating_counter+1 WHERE id = $sound_id")))
                        {
                            throw new Exception($connection->error);
                        }

                        if ($owner_request_result = $connection->query("SELECT * FROM sounds WHERE id = '$sound_id'"))
                        {
                            $row = $owner_request_result->fetch_assoc();
                            $sound_owner_id = $row['user_id'];
                            if (!($connection->query("UPDATE users SET sum_of_rates=sum_of_rates+1 WHERE id='$sound_owner_id'")))
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
                header('Location: ' . $_SERVER['HTTP_REFERER']);
             }

        }
        catch(Exception $err)
        {
            echo '<span style="color: red;">Błąd serwera. Proszę spróbować później.</span>';
            //echo '<br />Informacja deweloperska: '.$err;
        }
    }

    else if (isset($_POST['rate_sound_button_-']))
    {
        
        //Zapamiętaj wprowadzone dane
        $sound_id = $_POST['rate_sound_button_-'];
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

                if ($request_result = $connection->query("SELECT * FROM rates WHERE user_id = '$user_id' AND sound_id = '$sound_id'"))
                {
                    $how_many_rows = $request_result->num_rows;
                    if ($how_many_rows == 0)
                    {
                        $rate = '-';
                        if (!($connection->query("INSERT INTO rates VALUES (NULL, '$user_id', '$sound_id', '$rate')")))
                        {
                            throw new Exception($connection->error);
                        }
                        if (!($connection->query("UPDATE sounds SET rating = rating-1, rating_counter = rating_counter+1 WHERE id = $sound_id")))
                        {
                            throw new Exception($connection->error);
                        }
                        if ($owner_request_result = $connection->query("SELECT * FROM sounds WHERE id = '$sound_id'"))
                        {
                            $row = $owner_request_result->fetch_assoc();
                            $sound_owner_id = $row['user_id'];
                            if (!($connection->query("UPDATE users SET sum_of_rates=sum_of_rates-1 WHERE id='$sound_owner_id'")))
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
                header('Location: ' . $_SERVER['HTTP_REFERER']);
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