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
    include "simple_html_dom.php";
    set_time_limit(0);
    if(parser()==true) echo "Программа выполнена успешно";
    function parser(){
        $cn = mysqli_connect("localhost","root","root","bse");
        $url = "https://edunews.ru/kursovaya/";
        $html = str_get_html(getHtml($url));
        $categories = findLinksInTag($html,"div.col-md-6");
        foreach($categories as $category)
            parseCategory(getFullLink($category),$cn,"td");
        return true;
        
        
}
function parseCategory($url,$cn,$fullName){ //получаем url,количество страниц в категории и массив со всеми страницами
     $html = getHtml($url);
     $html = str_get_html($html);
     $as = $html -> find($fullName);
    //  showArray($as);
     for($i=0;$i<count($as);$i+=3){
        $link = getFullLink2($as[$i]);
        $link = str_replace('" target="_blank','',$link);
        $title = strip_tags($as[$i+1]);
        // echo $title."<br/>";
        $sql = "INSERT INTO links(id,title,link,status,source_id) VALUES (NULL,'$title','$link',0,2)";
        // echo $sql."<br/>";
        mysqli_query($cn,$sql);
    }      
} 
    function getHtml($url){
         $ch = curl_init($url);//запускаем и настраиваем парсер
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_HEADER,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER, array("Accept-Charset: UTF-8"));
         $html = curl_exec($ch);
         curl_close($ch);
         return $html;
    }
    function findLinksInTag($html,$fullName){
        $tags = $html -> find($fullName);
        return findLinks($tags);
    }
    function getFullLink($a){           
        $link =  $a->href;
         $domain = 'https://edunews.ru';
        return $domain.$link;                         
     }
     function getFullLink2($a){           
        preg_match('/href=(["\'])([^\1]*)\1/i', $a, $m);
        $link = $m[2];
        $domain = 'https://edunews.ru';
        return $domain.$link;                         
     }
    function findLinks($array){ //находим в каждом из элементов массива линки
        $linkArray = [];
       foreach($array as $element){
           $html = str_get_html($element);
           $links = $html->find("a[href]");
           foreach ($links as $link) 
              array_push($linkArray,$link);
        }
        return $linkArray;
    } 
    function convertMysqliToInt($mysqli){
        $mysqli = $mysqli->fetch_array();
        return intval($mysqli[0]); 
    }

    function showArray($array){
        foreach($array as $element)
            echo $element."<br/>";
    }
  ?>
</body>
</html>