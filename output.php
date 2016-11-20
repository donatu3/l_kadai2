<?php
function getBlogEntryBody_org($buf)
{
    /*** とりあえず本文を含むであろう要素を大きくとってくる ***/
    //DOMに変換
    $dom = str_get_html($buf);
    //head,script,noscript,style,header,footer,htmlコメント,form,asideを削除
    foreach($dom->find('head,script,noscript,style,header,footer,comment,form,aside') as $key => $element ){
        $element->outertext = '';
    }
    //プロフィールっぽい所は先に削除
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?profile[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches2);
    $deletekey = $matches2[1];
    $deleteval = $matches2[2];
    $arraymax = count($deletekey);
    //findで探せる形に整形
    $patterns = array();
    for($i=0;$i<$arraymax;$i++){
        $key = trim($deletekey[$i]);
        $val = $deleteval[$i];
        $patterns[] = "[$key=$val]";
    }
    //削除
    foreach($dom->find(implode(',', $patterns)) as $key => $element ) {
        $element->outertext = '';
    } 
    //削除したのを反映させてfindするためにもう一度読み込む
    $dom = str_get_html($dom);
    $max = 0;
    $length = 999999999;
    foreach($dom->find('div,td') as $key => $element ){
        //句読点や感嘆符、疑問符の数（文章にはこれらの符号が出現しやすい）
        $count = substr_count($element, "、");
        $count += substr_count($element, "。");
        $count += substr_count($element, "，");
        $count += substr_count($element, "．");
        $count += substr_count($element, "！");
        $count += substr_count($element, "？");
        //半角・全角スペース削除
        $val = str_replace(array(" ", "　"), "", $element);
        //文字数
        $len = mb_strlen($val);
        //符号の数が増えるような要素なら、それを抜き出す。同じなら、長さが短い方を優先。
        if($max < $count || ($max == $count && $len < $length)){
            $max = $count;
            $length = $len;
            $contents = $element;
        }
    }
    /*** 大まかに抜き出した要素に対して再び削除を行う（はじめに消すてしまうと必要以上に消えやすい） ***/
    $dom = str_get_html($contents);
    //ブログ上にある閲覧者からのコメントっぽいもの
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?comment[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches1);
    //下の方についてる余分な要素っぽいもの
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?bottom[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches2);
    //ブログのメニューっぽいもの 他の物が消えやすいので使わない
    /*preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?menu[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches3);*/
    //サイドバーっぽいもの
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?sidebar[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches4);
    //display:none;が設定されているもの
    preg_match_all("/<[\w]+[^>]*?(class|id)=[\"']([^\"']*?)[\"'][^>]*?style=[\"']display: none;[^>]*?\>/ius",$dom,$matches5);
    //ヘッダーっぽいもの
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?header[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches6);
    //フッターっぽいもの
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?footer[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches7);
    //ウィジェットっぽいもの　ブログサイトではユーザーが自由にウィジェットをカスタムできるものが多い
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?widget[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches8);
    //プラグインっぽいもの　同上
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?plugin[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches9);
    //バナーっぽいもの　本文とは関係ない事が多い
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?banner[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches10);
    //ボタンっぽいもの　SNSボタンなどは本文とは関係ないのでできるだけ削除
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?btn[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches11);
    //カレンダーっぽいもの　サイドに配置されていることが多い
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?calendar[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches12);
    //トラックバックっぽいもの　本文のあとなどには配置されていることが多い
    preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?trackback[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches13);
    //よくあるが、正規表現だと他のが消えやすいため個別で
    $targetkey = array("id");
    $targetval = array("menu");
    $deletekey = array_merge($matches1[1],$matches2[1],$matches4[1],$matches5[1],$matches6[1],$matches7[1],$matches8[1],$matches9[1],$matches10[1],$matches11[1],$matches12[1],$matches13[1],$targetkey);
    $deleteval = array_merge($matches1[2],$matches2[2],$matches4[2],$matches5[2],$matches6[2],$matches7[2],$matches8[2],$matches9[2],$matches10[2],$matches11[2],$matches12[2],$matches13[2],$targetval);
    $arraymax = count($deletekey);
    //findで探せる形に整形
    $patterns = array();
    for($i=0;$i<$arraymax;$i++){
        $key = trim($deletekey[$i]);
        $val = $deleteval[$i];
        $patterns[] = "[$key=$val]";
    }
    //削除
    foreach($dom->find(implode(',', $patterns)) as $key => $element ) {
        $element->outertext = '';
    } 
    //色々消えているはずなので、もう一回本文を探す。
    $dom = str_get_html($dom);
    $max = 0;
    $length = 999999999;
    foreach($dom->find('div,td') as $key => $element ){
        $count = substr_count($element, "、");
        $count += substr_count($element, "。");
        $count += substr_count($element, "，");
        $count += substr_count($element, "．");
        $count += substr_count($element, "！");
        $count += substr_count($element, "？");
        $val = str_replace(array(" ", "　"), "", $element);
        $len = mb_strlen($val);
        if($max < $count || ($max == $count && $len < $length)){
            $max = $count;
            $length = $len;
            $contents = $element;
        }
    }    
    //タグを消去
    $contents = preg_replace('/<("[^"]*"|\'[^\']*\'|[^\'"<>])*>/','',$contents);
    //空白消去
    $contents = preg_replace('/[\s　]/u','',$contents);
    //特定文字列出現時、以降を削除　本文中に特定文字列出現時に本文を削除してしまうので使わない
    //$contents = preg_replace('/トラックバック.*/ius','',$contents);
    return $contents;
}

/***** main program *****/
if ($_SERVER['REQUEST_METHOD']=="POST") {
    if($_FILES['input']['error'] !== 0){
        echo "ファイルアップロードエラー";
    }else{
        $file = "output.txt";
        header("Content-type: text/plain");
        header("Content-Disposition: attachment; filename=$file");
        $input = $_FILES['input']['tmp_name'];
        mb_language('Japanese');
        require_once 'simple_html_dom.php';
        $buf = mb_convert_encoding(file_get_contents($input), 'UTF-8','auto');
        echo getBlogEntryBody_org($buf);
    }
}else{
    echo "不正なアクセスです。";
}