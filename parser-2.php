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
    parser();
    function parser(){
    $cn = mysqli_connect("localhost","root","root","bse");
    // $numberOfPages = [23,6,897,21,183];//задаём количество страниц в каждой категории
    // $linksToCategories = ["http://elib.sfu-kras.ru/handle/2311/21621/recent-submissions","http://elib.sfu-kras.ru/handle/2311/32908/recent-submissions",
    // "http://elib.sfu-kras.ru/handle/2311/26348/recent-submissions","http://elib.sfu-kras.ru/handle/2311/26024/recent-submissions",
    // "http://elib.sfu-kras.ru/handle/2311/26387/recent-submissions"]; //создаём массив с ссылками на каждую категорию
    // if(count($numberOfPages)==count($linksToCategories)){
    //     for($i=0;$i<count($numberOfPages);$i++){
    //         parseCategory($linksToCategories[$i],$numberOfPages[$i],$cn); //проходимся по каждой категории и достаём из 
    //         echo "Прошёл категорию ".$i."<br/>";                                                              //из неё ссылки на страницы с работами
    //     }
    // }
    // else die("разное кол-во ссылок и страниц");
        $lastId= mysqli_query($cn,"SELECT MAX(id) FROM links");
        $lastId = convertMysqliToInt($lastId);
        $lastBseId = mysqli_query($cn,"SELECT MAX(id) FROM bse_normalize_content");
        $lastBseId = convertMysqliToInt($lastBseId);
        $lastBseId = $lastBseId - 4667626;
        echo $lastId."<br/>";
        echo $lastBseId;
        for($i=$lastBseId;$i<$lastId;$i++){ //проходимся по собранным ссылкам на страницы из links
            $pageWithMaterial = mysqli_query($cn,"SELECT link FROM links WHERE id = '$i'"); //берём link с нужным id
            $pageWithMaterial =  $pageWithMaterial -> fetch_array();
            $pageWithMaterial = strval($pageWithMaterial[0]);
            $link = findBtnLink(getHtml($pageWithMaterial),"btn-success");//получаем ссылку на документ
            $title = findTittle(getHtml($pageWithMaterial),"first-page-header");//получаем заголовок документа
            $sql = "INSERT INTO bse_normalize_content(id,title,link) VALUES ('NULL','$title','$link')"; //вносим данные в bse_normalize_content
            if(mysqli_query($cn,$sql)!=false) ;
        }
}   
    function findTittle($html,$className){ //находим заголовки с нужным classname 
          $html = str_get_html($html); 
          $h = $html -> find("h2.{$className}");
          $titleArray = [];
          if(!empty($h))
          foreach($h as $element)
             array_push($titleArray,strip_tags($element));
          return !empty($titleArray) ? $titleArray[0] : "прикол";

    }
  function parseCategory($url,$numberOfPages,$cn){ //получаем url,количество страниц в категории и массив со всеми страницами
            $html = getHtml($url); //получаем html из полученного url
            $nextPage = $html;
            for($i=0;$i<$numberOfPages;$i++){
                $a = findLinksInDiv($nextPage,"artifact-description");
                foreach($a as $element){
                    $link = getFullLink($element);
                    $sql = "INSERT INTO links(id,tittle,link) VALUES ('NULL','NULL','$link')"; //вносим ссылки на страницы в таблицу links
                    mysqli_query($cn,$sql);
                }
                $nextPageLink = findBtnLink($nextPage,"next-page-link"); //находим кнопку,которая переходит на следующую страницу
                     //получаем полную ссылку на следующую страницу
                $nextPage = getHtml($nextPageLink);//получаем htmk этой страницы
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
    function convertToNormalArray($array){ //преобразуем массив массивов в обычный массив
        $newArray = [];
        foreach($array as $subarray){
            foreach($subarray as $element)
                array_push($newArray,$element);
        }
        return $newArray;
    }
    function findLinksInDiv($html,$className){
     $html = str_get_html($html);
     $divs = $html->find("div.{$className}"); //находим div с нужным classname
     $ans=[];
    foreach($divs as $div)
         $ans[]=$div->outertext; //достаём из div внутреннюю информацию
      return findLinks($ans); //достаём из найденных div линки
    }
    function getFullLink($a){           
        $link =  $a->href;
        $domain = 'http://elib.sfu-kras.ru/';
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
    function findBtnLink($html,$className){//находим ссылку на скачивание работы
        $html = str_get_html($html);
        $a = $html->find("a.{$className}");
        $linkArray = [];
        if(!empty($a))
        foreach($a as $element)
           array_push($linkArray,getFullLink($element));
        return !empty($linkArray) ? $linkArray[0] : "прикол";
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