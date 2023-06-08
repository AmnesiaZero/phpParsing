<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
    set_time_limit(0);
    include "simple_html_dom.php";
     if(download()) echo "Скачивание прошло успешно";
    function download(){
        $cn = mysqli_connect("localhost","root","root","bse");
        $lastId= mysqli_query($cn,"SELECT MAX(id) FROM links");//выбираем максимальный id с таблицы
        $lastId = convertMysqliToInt($lastId);
        $lastDowloadFile = mysqli_query($cn,"SELECT `id` FROM `links` WHERE `status` = 0 LIMIT 1"); 
        $lastDowloadFile = convertMysqliToInt($lastDowloadFile);
        $currentOfDirectory = intdiv($lastDowloadFile,10000) + 1;//получаем номер директории последнего скачанного файла
        $maxDirectory = intdiv($lastId,10000)+2;
        for($i=$currentOfDirectory;$i<$maxDirectory;$i++){
            echo $i."//////";
            if($i==$currentOfDirectory){
                $curentFile = $lastDowloadFile;
            }
            else $curentFile = 10000*($i-1);
            $path = "D:/xampp/htdocs/website/parsedFiles/$i";
            if(!file_exists($path)) mkdir($path);
            echo "curentFile = $curentFile/////////";
            for($j=$curentFile;$j<10000*$i;$j++){
                $fileType = "";
                // echo $fileType;
                $url = mysqli_query($cn,"SELECT link FROM links WHERE id = '$j'");
                if (mysqli_num_rows($url)==0) continue;
                $url = convertMysqliToString($url);
                $fileType = exec("java -jar D:/tika-2.8.0/tika-app-2.8.0.jar -d $url");
                $fileName = "$path/$j.$fileType";
                echo $fileName."<br/>";
                if(file_exists($fileName)) continue;
                 if(file_put_contents($fileName, fopen($url, 'r'))!=false) mysqli_query($cn,"UPDATE `links` SET `status`= '1' WHERE `id`='$j'");
                 else  mysqli_query($cn,"UPDATE `links` SET `status`= '2' WHERE `id`='$j'");
            }
        }
        return true;
    }
    function convertMysqliToString($msqli){
        $msqli =  $msqli -> fetch_array();
        return strval($msqli[0]);
    }
    function convertMysqliToInt($mysqli){
        $mysqli = $mysqli->fetch_array();
        return intval($mysqli[0]); 
    }

    ?>
</body>
</html>