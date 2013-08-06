<?php
$cache_life = 30; //caching time, in seconds

if (!isset($_REQUEST['url'])) {
    exit();
}
$url = $_REQUEST['url'];

$url = trim(urldecode($url));
if ($url == '') {
    exit();
}

if (!stristr($url, 'http://') and !stristr($url, 'https://')) {
    $url = 'http://' . $url;

}

$url_segs = parse_url($url);
if (!isset($url_segs['host'])) {
    exit();
}


$here = dirname(__FILE__) . DIRECTORY_SEPARATOR;
$bin_files = $here . 'bin' . DIRECTORY_SEPARATOR;
$jobs = $here . 'jobs' . DIRECTORY_SEPARATOR;
$cache = $here . 'cache' . DIRECTORY_SEPARATOR;
if (!is_dir($jobs)) {
    mkdir($jobs);
}
if (!is_dir($cache)) {
    mkdir($cache);
}





$screen_file = $url_segs['host'] . crc32($url) . '.jpg';
$cache_job = $cache . $screen_file;


$refresh = false;
if (is_file($cache_job)) {
$filemtime = @filemtime($cache_job);  // returns FALSE if file does not exist
if (!$filemtime or (time() - $filemtime >= $cache_life)){
$refresh = true;
}
}

$w = 1024;
$h = 768;
if (isset($_REQUEST['w'])) {
  $w = intval($_REQUEST['w']);  
}

if (isset($_REQUEST['h'])) {
  $h = intval($_REQUEST['h']);  
}

if (!is_file($cache_job) or $refresh == true) {
    $src = "

var page = require('webpage').create();
page.viewportSize = { width: {$w}, height: {$h} };
page.open('{$url}', function () {
    page.render('{$screen_file}');
    phantom.exit();
});


";
    $job_file = $jobs . $url_segs['host'] . crc32($src) . '.js';
    file_put_contents($job_file, $src);

    $exec = $bin_files . 'phantomjs ' . $job_file;


    //var_dump($here.$screen_file);
    exec($exec);

    if (is_file($here . $screen_file)) {
        rename($here . $screen_file, $cache_job);
    }
}



if (is_file($cache_job)) {

    $file = $cache_job;
    $type = 'image/jpeg';
    header('Content-Type:' . $type);
    header('Content-Length: ' . filesize($file));
    readfile($file);

}





 