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
        $url = "http://xn--90ahkajq3b6a.xn--2000-94dygis2b.xn--p1ai/diplom.shtml";
        $html = str_get_html(getHtml($url));
        $divArray = $html -> find("div.e7-1");
        $ulArray = $divArray[0] ->find("ul");
        $linkArray = [];
        $extraArray = [];
        foreach($ulArray as $element){
            $html = str_get_html($element);
            $links = $html->find("a[href]");
            foreach ($links as $link){
              $extra = $link ->href;
              array_push($extraArray,strtok($extra,'/'));
              $link = getFullLink($link);
              array_push($linkArray,$link);
            } 
         }
        if($linkArray==0) die("Не найдены категории");
        foreach($linkArray as $link)
            parseCategory($link,$cn,$extraArray);
        return true;
}
function parseCategory($url,$cn,$extraArray){
    $counter = 0;
    $html = str_get_html(getHtml($url));
    if($html==null) return;
    $divArray = $html -> find("div.e7-1");
    // showArray($divArray);
    if($divArray[0]==null) return;
    $ulArray = $divArray[0] ->find("ul");
    foreach($ulArray as $ul){
        $html = str_get_html($ul);
        $aArray = $html->find("a[href]");
        $domain = "http://xn--90ahkajq3b6a.xn--2000-94dygis2b.xn--p1ai/".$extraArray[$counter];
        foreach ($aArray as $a){
          $href = $a -> href;
          $link =mysqli_real_escape_string($cn,$domain."/$href");
          $title =mysqli_real_escape_string($cn,strip_tags($a));
          //echo "INSERT INTO test2(id,title,link,source_id) VALUES (NULL,'$title','$link',3)<br/>";
          mysqli_query($cn,"INSERT INTO links(id,title,link,source_id,) VALUES (NULL,'$title','$link',3)");
        }
        $counter++; 

    }
 
    

}
function findLinksInTable($html,$fullName){
    $divArray = $html -> find($fullName);
    $ulArray = $divArray[0] ->find("ul");
    return findLinks($ulArray);

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
        $link =$a->href;
        // $link = $a ->href;
        // $domain = 'http://учебники.информ2000.рф/';
        $domain = "http://xn--90ahkajq3b6a.xn--2000-94dygis2b.xn--p1ai/";
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