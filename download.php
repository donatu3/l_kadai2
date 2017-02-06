<?php
if ($_SERVER['REQUEST_METHOD']=="POST" && @trim($_REQUEST['url']) !== "") {
    $url = @trim($_REQUEST['url']);
    $filename = "input.html";
    getHtml($filename,$url);
}else{
    header("Location: http://donatu33.sakura.ne.jp/kadai2/index.html");
    exit();
}

function getHtml($filename = 'input.html',$url = null){
    if($data = @file_get_contents($url)){
        $html = mb_convert_encoding($data, 'UTF-8', 'auto');
        header("Content-Disposition: attachment; filename=$filename");
        header('Content-Length: '.strlen($html));
        header('Content-Type: application/octet-stream');
        echo $html;
    }else{
        //エラー処理
        if(!empty($http_response_header)){
            $status_code = explode(' ', $http_response_header[0]);
            switch($status_code[1]){
                case 404:
                    echo "指定したページが見つかりませんでした";
                    break;
                case 500:
                    echo "指定したページがあるサーバーにエラーがあります";
                    break;
                default:
                    echo "何らかのエラーによって指定したページのデータを取得できませんでした";
            }
        }else{
            echo "タイムエラー or URLが間違っています";
        }
    } 
}