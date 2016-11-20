<?php
if ($_SERVER['REQUEST_METHOD']=="POST" && @trim($_REQUEST['url']) !== "") {
    $url = @trim($_REQUEST['url']);
    $file = "input.html";
    header("Content-type: text/plain");
    header("Content-Disposition: attachment; filename=$file");
    $url = @trim($_REQUEST['url']);
    mb_language('Japanese');
    $html = mb_convert_encoding(file_get_contents($url), 'UTF-8', 'auto');
    echo $html;
}else{
    header("Location: http://donatu33.sakura.ne.jp/kadai2/index.html");
    exit();
}
