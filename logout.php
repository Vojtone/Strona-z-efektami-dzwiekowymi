<?php
    session_start();

    session_unset(); 
    $_SESSION['info'] = '<span style="color:green">Zostałeś wylogowany</span>';
    header('Location: index.php');

?>

