<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <title>Document</title>
</head>
<body>
    <?php
    set_time_limit(0);
    $cn = mysqli_connect("localhost","root","root","bse");
    mysqli_query($cn,"INSERT INTO links(id,title,link) VALUES ('1886',NULL,'http://elib.sfu-kras.ru//handle/2311/148309'");
    mysqli_query($cn,"INSERT INTO links(id,title,link) VALUES ('2156',NULL,'http://elib.sfu-kras.ru//handle/2311/148309'");
     
  ?>
</body>
</html>