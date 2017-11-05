<?php

    session_start();

    if((!isset($_POST['login'])) || (!isset($_POST['password'])))
    {
        header('Location: index.php');
        exit();
    }

    require_once "connect.php";

    $connection = @new mysqli($host, $db_user, $db_password, $db_name);

    if ($connection->connect_errno != 0)
    {
        echo "Error: ".$connection->connect_errno;
    }
    else
    {
        $login = $_POST['login'];
        $password = $_POST['password'];

        $login = htmlentities($login, ENT_QUOTES, "UTF-8");

        if($request_result = @$connection->query(
        sprintf("SELECT * FROM users WHERE user='%s'",
        mysqli_real_escape_string($connection,$login))))
        {
            $how_many_rows = $request_result->num_rows;
            if($how_many_rows>0)
            {
                $row = $request_result->fetch_assoc();

                if(password_verify($password, $row['password']))
                {
                    $_SESSION['logged'] = true;

                    $_SESSION['id'] = $row['id'];
                    $_SESSION['user'] = $row['user'];

                    unset($_SESSION['info']);

                    $request_result->close();
                    header('Location: main.php');
                }
                else 
                {
                    $_SESSION['info'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';  
                    header('Location: index.php');
                }

            } 
            else 
            {
                $_SESSION['info'] = '<span style="color:red">Nieprawidłowy login lub hasło!</span>';  
                header('Location: index.php');

            }
        }

        $connection->close();
    }

?>