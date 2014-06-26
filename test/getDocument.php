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
            <label for="app">Applicazione</label>
            <input type="text" name="app" id="app" value="">
            <label for="project">Progetto</label>
            <input type="text" name="project" id="project" value="">
            <label for="id">Id</label>
            <input type="text" name="id" id="id" value="">
            <label for="filename">File</label>
            <input type="text" name="filename" id="filename" value="">
            
        </form>
        
    </body>
</html>