<?php
require 'C:/Users/GoodPC/Documents/NetBeansProjects/simpleshop.local/vendor/autoload.php';
//require '';


use Symfony\Component\DomCrawler\Crawler;
//use Symfony\Component\Panther\Client;
//use JonnyW\PhantomJs\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Stream\Stream;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;
use Psr\Http\Message\RequestInterface;

$locdbtest = "127.0.0.1";
$namedbtest = "test";
$userdbtest = "root";
$passdbtest = ""; //если указан

$dbcontest = mysqli_connect($locdbtest, $userdbtest, $passdbtest, $namedbtest);
//simpledebug($dbcon);
if (!$dbcontest){
    echo "Error access from MySql";
    exit();
}

mysqli_set_charset($dbcontest,'utf8mb4');

if(!mysqli_select_db($dbcontest, $namedbtest)){
    echo "Error access from DB: {$namedbtest}"; 
    exit();
}

function newArt($ArtistNick, $ArtistName, $dbcontest){
   
    $sqlcom = "INSERT INTO media_artists (`Username`, `Fullname`) values ('$ArtistNick', '$ArtistName')";
    //simpledebug($sqlcom);
    
    $dataset = mysqli_query($dbcontest, $sqlcom);
    //simpledebug($dataset);
    //получить Никнэйм последнего добавленного артиста  !!!!!это пока не использованно, думал, пригодится
    if ($dataset){
        $sqlcom = "Select username from media_artist order by username desc limit 1";
        $dataset = mysqli_query($dbcontest, $sqlcom);
       // simpledebug($dataset);
        $dataset = createSmartyDsArr($dataset);
        //simpledebug($dataset);
        if (isset($dataset[0])){
            return $dataset[0]['id'];
        }
    }
    return false;
}

function addTracksToArtist($ArtistNick, $TrackName, $TrackDur, $dbcontest){
    $sqlcom = "INSERT INTO media_tracks (`username`, `track_name`, `duration`) values ('$ArtistNick', '$TrackName', '$TrackDur')";
    mysqli_query($dbcontest, $sqlcom);
}


$client = new GuzzleHttp\Client();
//Здесь указываем ссылку на Артиста, которого нужно спарсить
 $request = $client->get('https://soundcloud.com/aljoshakonstanty/tracks');
//$result = var_export($request->getBody()->getContents());

 //получаем userID
$crawler = new Crawler($request->getBody()->getContents());
$dump = print_r($crawler, true);

preg_match_all('#"station_permalink":"artist-stations:([^\s]+)#', $dump, $matches);

$str = implode(' ', $matches[1]);
//$str = $matches[1];
$keywords = preg_split("/[\n,]+/", $str); //вот эта строчка чудо образом разложила строку на удобный массив
print_r($keywords);
$usrID = intval($keywords[0]);

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!так искать удобно нужные строки, которые мы вставляем в preg_match_all
//simpledebug(var_export($dump));

//получение треков  (client_id скорее всего со временем нужно будет менять, просто заходим в браузер, смотрим "Сеть" и ищем любой запрос к api )
 $requestTracks = $client->get("https://api-v2.soundcloud.com/users/".strval($usrID)."/tracks?offset=2021-04-29T12%3A46%3A35.000Z%2Ctracks%2C01039080739&limit=40&representation=&client_id=SFgOqxCBBl2FvAQKMEoyfcKSecc6PvRh&app_version=1632912002&app_locale=en");
 $responseTracks = json_decode($requestTracks->getBody()->getContents(), true);
 $Tracks_col = count($responseTracks['collection']);
 $ArtistNick = $responseTracks['collection'][0]['user']['username'];
 $ArtistName = $responseTracks['collection'][0]['user']['full_name'];
 
 newArt($ArtistNick, $ArtistName, $dbcontest);
 
 for ($i=0; $i<=$Tracks_col-1; $i++){
 
 $TrackName = $responseTracks['collection'][$i]['title'];
 $TrackDur = intval($responseTracks['collection'][$i]['duration']);
 addTracksToArtist($ArtistNick, $TrackName, $TrackDur, $dbcontest);
 }
//simpledebug($TracksArr);




