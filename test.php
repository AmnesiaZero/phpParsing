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
    //div data-edugram="edulinks"
    include "simple_html_dom.php";
    set_time_limit(0);
    if(parser()==true) echo "Программа выполнена успешно";
    function parser(){
        $cn = mysqli_connect("localhost","root","root","bse");
        $url = "https://2dip.su/%D0%B4%D0%B8%D0%BF%D0%BB%D0%BE%D0%BC%D0%BD%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B/12299/";
        parsePage($url,"Заголовок",$cn);
        return true;
        
        
}

function parseCatalog($url,$cn){ //получаем url,количество страниц в категории и массив со всеми страницами
     $html = getHtml($url);
     $html = str_get_html($html);
     $divs = $html -> find("div.table-responsive");
     foreach($divs as $div){
        $div = str_get_html($div);
        $as = $div->find("a[href]");
        foreach($as as $a)
            parseCategory(getFullLink($a),$cn);
     }
    //  showArray($categiries);
} 
function parseCategory($url,$cn){
    echo "url = $url";
    $flag = true;
    $html = getHtml($url); //получаем html из полученного url
    $nextPage = $html;
    $nextPage = str_get_html($nextPage);
    while($flag){
        // $tds = $nextPage->find('td.project-title');//если что-то сломается,это - первый кандидат
        // $links = findLinks($tds);
        $as = $nextPage->find('a."theme"');//если что-то сломается,это - первый кандидат
        if(count($as)==0){
            echo "Не находит статью";
            break;
        }
        // foreach($as as $a){
        //     $title = strip_tags($a);
        //     $link = getFullLink($a);
        //     parsePage($link,$title,$cn);
            
        // }
        // $listButtons = $nextPage-> find("a.btn-white");
        // echo "Кнопки - <br/>";
        // showArray($listButtons);
        // for($i=0;$i<count($listButtons);$i++){
        //     if(get_class($listButtons[$i])=="btn-white active"){
        //         $nextPage =str_get_html(getHtml(getFullLink($listButtons[$i+1])));
        //         if(count($listButtons)==$i+1) $flag = false;
        //         break;

        //     }
        // }

 }
}
function parsePage($url,$title,$cn){
    $page = str_get_html(getHtml($url));
    $div = $page ->find("div.cardtext");
    showArray($div);
    $text = strip_tags($div[0]);
    mysqli_query($cn,"INSERT INTO test (id,source_id,title,content,link) VALUES (NULL,1,$title,$text,$url)");

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
         $domain = 'https://2dip.su/';
        return $domain.$link;                         
     }
     function getFullLink2($a){           
        preg_match('/href=(["\'])([^\1]*)\1/i', $a, $m);
        $link = $m[2];
        $domain = 'https://2dip.su/';
        return $domain.$link;                         
     }
    function findLinks($array){ //находим в каждом из элементов массива линки
        $linkArray = [];
       foreach($array as $element){
           $html = str_get_html($element);
           $links = $html->find("a[href]");
           foreach ($links as $link) 
              array_push($linkArray,getFullLink($link));
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