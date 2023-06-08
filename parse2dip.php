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
        $catalogs =["https://2dip.su/%D0%B4%D0%B8%D0%BF%D0%BB%D0%BE%D0%BC%D0%BD%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B/","https://2dip.su/%D0%BA%D1%83%D1%80%D1%81%D0%BE%D0%B2%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B/",
    "https://2dip.su/%D1%80%D0%B5%D1%84%D0%B5%D1%80%D0%B0%D1%82%D1%8B/","https://2dip.su/%D0%BA%D0%BE%D0%BD%D1%82%D1%80%D0%BE%D0%BB%D1%8C%D0%BD%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B/",
"https://2dip.su/%D0%BA%D0%BE%D0%BD%D1%81%D0%BF%D0%B5%D0%BA%D1%82%D1%8B/","https://2dip.su/%D1%88%D0%BF%D0%B0%D1%80%D0%B3%D0%B0%D0%BB%D0%BA%D0%B8/","https://2dip.su/%D1%82%D0%B5%D0%BE%D1%80%D0%B8%D1%8F/",
"https://2dip.su/%D0%BE%D1%82%D1%87%D1%91%D1%82%D1%8B_%D0%BF%D0%BE_%D0%BF%D1%80%D0%B0%D0%BA%D1%82%D0%B8%D0%BA%D0%B5/","https://2dip.su/%D1%8D%D1%81%D1%81%D0%B5/"];
       foreach($catalogs as $catalog)
               parseCatalog($catalog,$cn);
        return true;
        
        
}
function parseCatalog($url,$cn){ //получаем url,количество страниц в категории и массив со всеми страницами
     $html = getHtml($url);
     $html = str_get_html($html);
     $divs = $html -> find("div.table-responsive");
     foreach($divs as $div){
        $div = str_get_html($div);
        $as = $div->find("a[href]");
        foreach($as as $a){
            parseCategory(getFullLink($a),$cn);
            break;

        }
        break;
     }
    //  showArray($categiries); //https%3A%2F%2F2dip.su%2F%2F
} 
//https://2dip.su/%D0%B4%D0%B8%D0%BF%D0%BB%D0%BE%D0%BC%D0%BD%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B/%D0%B8%D1%81%D1%82%D0%BE%D1%80%D0%B8%D1%8F/ - ссылка из браузера
//https://2dip.su/%2F%D0%B4%D0%B8%D0%BF%D0%BB%D0%BE%D0%BC%D0%BD%D1%8B%D0%B5_%D1%80%D0%B0%D0%B1%D0%BE%D1%82%D1%8B%2F%D0%B8%D1%81%D1%82%D0%BE%D1%80%D0%B8%D1%8F%2F - ссылка,полученаая программой
//
function parseCategory($url,$cn){
    echo "url = $url <br/>";
    $flag = true;
    $html = getHtml($url); //получаем html из полученного url
    // echo $html."<br/>";
    $nextPage = $html;
    echo $nextPage;
    $nextPage = str_get_html($nextPage);
    while($flag){
        $tds = $nextPage->find('td.project-title');//если что-то сломается,это - первый кандидат
        if(count($tds)==0) die("Работы не нашлись");
        $links = findLinks($tds);
        showArray($links);
        break;
        // $as = $nextPage->find('a."theme"');//если что-то сломается,это - первый кандидат
        // if(count($links)==0){
        //     echo "Не находит статью";
        //     break;
        // }
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
    if(count($div)==0) return false;
    $text = strip_tags($div);
    mysqli_query($cn,"INSERT INTO bse_normalize_content (id,source_id,title,content,link) VALUES (NULL,1,$title,$text,$url)");

}
    function getHtml($url){
         $ch = curl_init($url);//запускаем и настраиваем парсер
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
         curl_setopt($ch,CURLOPT_HEADER,true);
         curl_setopt($ch,CURLOPT_HTTPHEADER, array("Accept-Charset: UTF-8"));
        //  $url = curl_escape($ch,$url);
         $html = curl_exec($ch);
         curl_close($ch);
         return $html;
    }
    function findLinksInTag($html,$fullName){
        $tags = $html -> find($fullName);
        return findLinks($tags);
    }
    function getFullLink($a){           
        $link =urlencode($a->href);
        // $link = $a ->href;
        $domain = 'https://2dip.su/';
        return $domain.$link;                         
     }
    //  function getFullLink2($a){           
    //     preg_match('/href=(["\'])([^\1]*)\1/i', $a, $m);
    //     $link = $m[2];
    //     $domain = 'https://2dip.su/';
    //     return $domain.$link;                         
    //  }
    function findLinks($array){ //находим в каждом из элементов массива линки
        $linkArray = [];
       foreach($array as $element){
           $html = str_get_html($element);
           $links = $html->find("a[href]");
           foreach ($links as $link){
             $link = getFullLink($link);
             array_push($linkArray,$link);
           } 
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