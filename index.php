<?php

$get = json_decode(file_get_contents("https://api.snappea.com/v1/video/details?url=" . $_GET['url']), true);
$ex = explode('v=', $_GET['url']);
$fid = $_GET['fid'];
$img = null;
if(mb_strpos($_GET['url'], "youtu.be") !== false){
    $e = explode("youtu.be/", $_GET['url']);
    $img = "https://img.youtube.com/vi/" . $e[1] . "/maxresdefault.jpg";
}else{
    $img = "https://img.youtube.com/vi/" . $ex[1] . "/maxresdefault.jpg";
}


if ($get['videoInfo'] == null) {
    $date = [
        'status' => 'error',
        'message' => 'Video not found',
        'developer' => 'dadabayev.uz'
    ];
    echo json_encode($date);
    exit();
}

$array = [];
$i = 0;
//number to K, M, B
$number = $get['videoInfo']['viewCount'];
if ($number >= 1000000000) {
    $number = round($number / 1000000000, 1) . "B";
} elseif ($number >= 1000000) {
    $number = round($number / 1000000, 1) . "M"; 
} elseif ($number >= 1000) {
    $number = round($number / 1000, 1) . "K";
}
 
array_push($array, ["image" => $img, "name" => $get['videoInfo']['title'], "second" => $get['videoInfo']['duration'], "views" => $number, "developer" => "instagram: @Akhmadjon_dadabayev"]);

$i = 0;
foreach($get['videoInfo']['downloadInfoList'] as $download) {
    if($download['mime'] == 'audio' and $download['formatAlias'] == '128k') {
        if (floor($download['partList'][0]['size'] / 1024 / 1024) <= 50) {
        // file_put_contents("yt/" . $fid . ".m4a", file_get_contents($download['partList'][0]['urlList'][0]));
        shell_exec("ffmpeg -i " . $download['partList'][0]['urlList'][0] . " -vn -ar 44100 -ac 2 -b:a 128k yt/$fid.mp3");
        }
        array_push($array, ["audio" => $download['partList'][0]['urlList'][0], "size" => floor($download['partList'][0]['size'] / 1024 / 1024)]);
    }
    if ($download['formatExt'] == "mp4") {
        $calc = $download['partList'][0]['size'] / 1024 / 1024;
        $size = floor($calc);
        if ($size <= 50) {
            shell_exec("wget -O yt/" . $fid . $i . ".mp4 '" . $download['partList'][0]['urlList'][0] . "'");
        }
        array_push($array, ["type" => $download['formatAlias'], "video" => $download['partList'][0]['urlList'][0], "size" => $size]);
        $i++;
    }
  
}
echo json_encode($array); 
exit();
?>   
