<?php
    session_start();

    if (!isset($_SESSION['logged']))
    {
        header('Location: index.php');
        exit();
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
		var slide_number = Math.floor(Math.random()*2)+1;
		function hide()
		{
			$("#slide_show").fadeOut(500);
		}
		function change_slide()
		{
			slide_number++; 
			if(slide_number>3) slide_number=1;
			
			var slide = "<img style=\"width: 404px; height: 398px;\" src=\"slide_show/slide" + slide_number + ".jpg\" />"
			
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

            <a href="index.php">
                <div id=return>
                    <div style="padding-top: 55px;">
                        POWRÓT DO STRONY GŁÓWNEJ
                    </div>
                </div>
            </a>
            
        </div>

        <div id="content">

            <div id=sign_up_form>
                <form action="upload.php" method="post" enctype="multipart/form-data">
                    
                    <div style="margin-top:20px;">
                        <input type="file" name="fileToUpload" id="fileToUpload">
                        <label for="fileToUpload" id="file">
                            Wybierz dźwięk
                        </label>
                    </div>
                    <div style="margin-top:20px;">
                        <label>Kategoria:</label>
                        <select name="category" id="category">
                            <option>-</option>
                            <option>Natura</option>
                            <option>Akcja</option>
                            <option>Obyczajowe</option>
                            <option>Inne</option>
                        </select>
                    </div>
                    <div style="margin-top:20px;">
                        <textarea placeholder="Tu wpisz słowa kluczowe, które ułatwią wyszukanie dźwięku. Na przykład dla dźwięku burzy możesz wpisać: burza piorun błyskawica natura. " name="keywords" id="keywords"></textarea>
                    </div>
                    <br />
                    <input type="submit" value="Dodaj dźwięk">
                </form>

                <?php
                    if(isset($_SESSION['info']))
                    {
                    echo $_SESSION['info'];
                    unset($_SESSION['info']);
                    }
                ?>

            </div>

            <div id="slide_show" style="margin-top: 10px;">
            </div>

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