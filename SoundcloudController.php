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

use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
/*
$ArtName = 'lakeyinspired';
$client = new Client([
     'base_uri' => 'https://soundcloud.com',
     'timeout' => 2.0,
     'headers' => [
         'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebkit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.110 Safari/537.36',
     ]
 ]);

$response = $client->request('GET', "/aljoshakonstanty/tracks");
//echo $response;
simpledebug($response->getBody()->getContents());
//https://soundcloud.com/lakeyinspired
*/








/*$client = new Client([
    // Base URI is used with relative requests
    'base_uri' => 'https://soundcloud.com/',
    // You can set any number of default request options.
    'timeout'  => 2.0,
]);
*/
//$serverUrl = 'https://soundcloud.com/lakeyinspired/tracks:4444';
//$driver = RemoteWebDriver::create($serverUrl, DesiredCapabilities::chrome());
//$driver = RemoteWebDriver::create($host, $dc);
//$driver->get('http://stackoverflow.com');
//$result = $driver->get('https://soundcloud.com/lakeyinspired/tracks/');

//simpledebug($driver);

/*
//Достать Хэдеры
$client = new GuzzleHttp\Client();
 
$response = $client->request('GET', 'http://simpleshop.local/JavaSc/m.js');
foreach ($response->getHeaders() as $name => $values) {
    echo $name . ': ' . implode(', ', $values) . "\r\n<br /><br />"; 
}
*/

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
//$result = $crawler->filter('window.__sc_hydration');
//$result = var_export($crawler, true);
//$findel = strpos($dump, 'visual_url');
//$findel = str_contains($dump, 'visual_url');
preg_match_all('#"station_permalink":"artist-stations:([^\s]+)#', $dump, $matches);

$str = implode(' ', $matches[1]);
//$str = $matches[1];
$keywords = preg_split("/[\n,]+/", $str); //вот эта строчка чудо образом разложила строку на удобный массив
print_r($keywords);
$usrID = intval($keywords[0]);
//simpledebug($usrID);
//$findel = strstr( $dump, 'soundcloud:system-playlists:artist-stations:', true)
//simpledebug($result);
//print_r($findel);

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





//$data = $crawler->filter('span[class="soundTitle__usernameText"]');
//$tag = $crawler->filterXPath('//[id="app"]')->attr('class');   
//*[@id="progress"]
//работает

//$tag = $crawler->filterXPath('//*[@id="app"]/header')->attr('class');
//$tag = $crawler->filterXPath('//*[@id="eu-cookie-notifier"]')->attr('class');   ///html/body/div[3]//*[@id="onetrust-consent-sdk"]
 //  $attrs = $crawler->filterXpath('//*[@id="app"]')->attr('id');
     //->nodeName()
//$class = $crawler->filterXPath('//*[class="soundTitle__usernameText"]');
//simpledebug($crawler);
//$contents = $stream->getContents(); // returns all the contents
//simpledebug($contents);
//$response = $client->request('GET', 'lakeyinspired/tracks');
//echo $response;
//simpledebug(json_decode($response->getBody()->getContents()));
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

