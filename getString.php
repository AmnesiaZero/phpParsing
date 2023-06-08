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
if(getString()) echo "Программа сработала успешно";
    function getString(){
        $cn = mysqli_connect("localhost","root","root","bse");
        //"D:\tika-2.8.0\tika-app-2.8.0.jar";
        $mainDirectory = 'D:\xampp\htdocs\website\parsedFiles';
        $counter = 1;
        $lastDownloadFileId =convertMysqliToInt(mysqli_query($cn,"SELECT `id` FROM `links` WHERE `status` = 0 LIMIT 1")); 
        $currentFile = convertMysqliToInt(mysqli_query($cn,"SELECT `id` FROM `links` WHERE `bse_insert_status` = 0 LIMIT 1")); 
        echo $currentFile;
        $lastDirectory = intdiv($lastDownloadFileId,10000);
        try{
        // echo "Последняя директория = ".$lastDirectory;
        for($i=1;$i<$lastDirectory+2;$i++){
            $directoryPath = $mainDirectory."\\$i";
            // echo $path;
            if($counter!=1){
                $currentFile = 10000*($i-1);
            }
            for($j=$currentFile;$j<10000*$i;$j++){
                $fileSourceId =convertMysqliToInt(mysqli_query($cn,"SELECT source_id FROM links WHERE id = '$j'"));
                
                if($fileSourceId==1) $file = "$directoryPath/$j.pdf";
                else $file = "$directoryPath/$j.rar";
                if(is_file($file)){
                    if($fileSourceId==1) $t = "D:/xampp/htdocs/website/TikaExitFiles/$counter.pdf";
                    else $file = $t = "D:/xampp/htdocs/website/TikaExitFiles/$counter.rar";
                    exec("java -Dfile.encoding=UTF-8 -jar D:/tika-2.8.0/tika-app-2.8.0.jar -t -r $file>$t");
                    $fileType="";
                    // mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                    // $text =mysqli_real_escape_string($cn,file_get_contents($t));
                    // if($text=="\n\n\n\n\nToggle navigation\n\n	\n\n	\nрусский\n\n	\nEnglish\n\n\n\n\n\n\n\n\n\n\n\n	\nрусский \n	\nрусский\n\n	\nEnglish\n\n\n\n\n\n\n\n\nToggle navigation\n\n\n\n\n\n\n\n\n\n\n\n\nВыберите имя пользователя \n	\n \n                        Главная\n\n	\nВыберите имя пользователя\n\n\n\n\n\n	\n \n            Главная\n\n	Выберите имя пользователя\n\n\n\n\n\n\n\n\n\n\n\n\nJavaScript is disabled for your browser. Some features of this site may not work without it.\n\n\n\n\n\n\n\n\n\n\nВыбрать способ входа\n\n\n\n\nДля получения максимального доступа к ресурсам архива нужно находиться в сети СФУ или войти под учётной записью СФУ (при нахождении вне сети СФУ). Некоторые ресурсы имеют ограниченный режим доступа и могут быть недоступны даже в этом случае.\nВойти с помощью:\n	\nУчётная запись администратора\n\n	\nСетевая учётная запись СФУ\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nDSpace software copyright © 2002-2015  DuraSpace\n\n\n\nКонтакты | Отправить отзыв\n\n\n\n\n\n\nTheme by \n\n\n\n\n\n\n\n\n\n \n \n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\n\nDSpace software copyright © 2002-2015  DuraSpace\n\n\n\nКонтакты | Отправить отзыв\n\n\n\n\n\n\nTheme by \n\n\n\n\n\n\n\n\n\n \n \n\n\n\n\n\n\n\n\n\n\n\n\n\n\n"){
                    //     mysqli_query($cn,"UPDATE `links` SET `status`= 2 WHERE `id`='$j'");
                    //     continue;
                    // }
                    // if($text==NULL){
                    //     mysqli_query($cn,"UPDATE `links` SET `status`= 2 WHERE `id`='$j'");
                    //     continue;
                    // }
                    // $link =mysqli_real_escape_string($cn,convertMysqliToString(mysqli_query($cn,"SELECT link FROM links WHERE id = '$j'")));
                    // $title =mysqli_real_escape_string($cn,convertMysqliToString( mysqli_query($cn,"SELECT title FROM links WHERE id = '$j'")));
                    // $conn = new PDO('mysql:host=localhost;dbname=bse','root','root');
                    // $text =clean($conn->quote($text));
                    // $title =clean($conn->quote($title));
                    // $link = $conn->quote($link);
                    // $sql = 'INSERT INTO `bse_normalize_content`(`id`, `source_id`, `title`, `content`, `normalize`, `information`, `link`) VALUES (NULL,1,'.$title.','.$text.',NULL,NULL,'.$link.')';
                    // // echo $sql;
                    // if(mysqli_query($cn,$sql)!=false) mysqli_query($cn,"UPDATE `links` SET `status`= '1' WHERE `id`='$j'");
                    // else  mysqli_query($cn,"UPDATE `links` SET `status`= 2 WHERE `id`='$j'");
                    $counter++;
                    unlink($t);
                }
                else{
                    echo "файл не найден";
                    mysqli_query($cn,"UPDATE `links` SET `status`= 2 WHERE `id`='$j'");
                    continue;
                }
            }
        }
    }
    catch(Exception $e){
        echo 'Caught exception: ',  $e->getMessage(), "\n","in line ",$e->getLine();
        deleteAllFiles("D:/xampp/htdocs/website/TikaExitFiles");
    }
    return true;

}
function deleteAllFiles($directory){
            $directory = $directory;
            $files = glob($directory.'\*'); 
            foreach($files as $file) 
                   if(is_file($file))
                        unlink($file); 
    }   
    
    function convertMysqliToString($msqli){
        $msqli =  $msqli -> fetch_array();
        return strval($msqli[0]);
    }
    function convertMysqliToInt($mysqli){
        $mysqli = $mysqli->fetch_array();
        return intval($mysqli[0]); 
    }
    function clean($string) {
        return preg_replace("/\r|\n/", "", $string);;
     }

    ?>
</body>
</html>