<?php

?>

<html>
    <head>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
        <script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1/jquery-ui.min.js"></script>
        <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.4/themes/smoothness/jquery-ui.css" />
        
    </head>
    <body>
        <form method="POST" action="../services/xGetDocument.php" target="_new">
            <div>
                <label for="app">Applicazione</label>
                <input type="text" name="app" id="app" value=""/>
            </div>
            <div>
                <label for="project">Progetto</label>
                <input type="text" name="project" id="project" value=""/>
            </div>
            <div>
                <label for="id">Id</label>
                <input type="text" name="id" id="id" value=""/>
            </div>
            <div>
                <label for="filename">File</label>
                <input type="text" name="filename" id="filename" value=""/>
            </div>
            <input type="submit" value="Invia"/>
        </form>
        
    </body>
</html>