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
    if(parser()==true) echo "Программа отработала успешно";
    function parser(){
    $cn = mysqli_connect("localhost","root","root","bse");
    if(!$cn) die("Соединение прервано");
    // $numberOfPages = [626,140,12,265,1206];//задаём количество страниц в каждой категории
    // $linksToCategories = ["https://elib.sfu-kras.ru/handle/2311/3245/recent-submissions","https://elib.sfu-kras.ru/handle/2311/112966/recent-submissions",
    // "https://elib.sfu-kras.ru/handle/2311/32650/recent-submissions","https://elib.sfu-kras.ru/handle/2311/77/recent-submissions",
    // "https://elib.sfu-kras.ru/handle/2311/34566/recent-submissions"]; //создаём массив с ссылками на каждую категорию
    // if(count($numberOfPages)==count($linksToCategories)){
    //     for($i=0;$i<count($numberOfPages);$i++){
    //         parseCategory($linksToCategories[$i],$numberOfPages[$i],$cn); //проходимся по каждой категории и достаём из 
    //         echo "Прошёл категорию ".$i."<br/>";                                                              //из неё ссылки на страницы с работами
    //     }
    // }
    // else die("разное кол-во ссылок и страниц");
        $lastParsedUrl = mysqli_query($cn,"SELECT `id` FROM `templinks` WHERE `status` = 0 LIMIT 1"); 
        $lastParsedUrl = convertMysqliToInt($lastParsedUrl);
        $lastId = mysqli_query($cn,"SELECT MAX(id) FROM templinks");
        $lastId = convertMysqliToInt($lastId);
        echo $lastId."<br/>";
        echo $lastParsedUrl;
        $counter = 0;
        for($i=$lastParsedUrl;$i<$lastId;$i++){
            // if($counter>10) die("Слишком много пропустил"); //проходимся по собранным ссылкам на страницы из templinks
            $pageWithMaterial = mysqli_query($cn,"SELECT link FROM templinks WHERE id = '$i'"); //берём link с нужным id
            $pageWithMaterial =  $pageWithMaterial -> fetch_array();
            $pageWithMaterial = strval($pageWithMaterial[0]);
            $link = findBtnLink(getHtml($pageWithMaterial),"btn-success");//получаем ссылку на документ
            // if($link==0){
            //     mysqli_query($cn,"UPDATE `templinks` SET `status`='1' WHERE `id`='$i'");
            //     $counter++;
            //     continue;
            // } 
            $tittle = findTittle(getHtml($pageWithMaterial),"first-page-header");//получаем заголовок документа
            $link = mysqli_real_escape_string($cn,$link);
            $tittle = mysqli_real_escape_string($cn,$tittle);
            $sql = "INSERT INTO links(id,tittle,link,status) VALUES (NULL,'$tittle','$link',0)"; //вносим данные в bse_normalize_content
            // echo $sql;
            if(mysqli_query($cn,$sql)!=false)  mysqli_query($cn,"UPDATE `templinks` SET `status`='1' WHERE `id`='$i'");
        }
        return true;
}   
    function findTittle($html,$className){ //находим заголовки с нужным classname 
          $html = str_get_html($html); 
        //   if($html==true|$html==false) return 0;
          $h = $html -> find("h2.{$className}");
          $titleArray = [];
          if(!empty($h))
          foreach($h as $element)
             array_push($titleArray,strip_tags($element));
          return !empty($titleArray) ? $titleArray[0] : 0;

    }
  function parseCategory($url,$numberOfPages,$cn){ //получаем url,количество страниц в категории и массив со всеми страницами
            $html = getHtml($url); //получаем html из полученного url
            $nextPage = $html;
            for($i=0;$i<$numberOfPages;$i++){
                $a = findLinksInDiv($nextPage,"artifact-description");
                foreach($a as $element){
                    $link = getFullLink($element);
                    $sql = "INSERT INTO templinks(id,tittle,link) VALUES ('NULL','NULL','$link')"; //вносим ссылки на страницы в таблицу links
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
        return !empty($linkArray) ? $linkArray[0] : 0;
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