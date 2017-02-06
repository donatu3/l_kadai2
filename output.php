<?php
if ($_SERVER['REQUEST_METHOD']=="POST") {
    if($_FILES['input']['error'] !== 0){
        echo "ファイルアップロードエラー";
    }else{
        $filename = "output.txt";
        $input = $_FILES['input']['tmp_name'];
        mb_language('Japanese');
        require_once 'simple_html_dom.php';
        $buf = mb_convert_encoding(file_get_contents($input), 'UTF-8','auto');
        $result = getBlogEntryBody_org($buf);
        header("Content-Disposition: attachment; filename=$filename");
        header('Content-Length: '.strlen($result));
        header('Content-Type: application/octet-stream');
        echo $result;
    }
}else{
    echo "不正なアクセスです。";
}

//本文抽出全体
function getBlogEntryBody_org($buf)
{
    //本文に不要そうなものを削除する
    $dom = filter1($buf);
    //本文を抽出する
    $contents = extractBody($dom);
    //大まかに抜き出した本文に対して再び削除を行う（はじめに消すてしまうと必要以上に消えやすい）
    $dom = filter2($contents);
    //再度本文を抽出する
    $contents = extractBody($dom);
    //タグなどを取り除く
    $contents = removeTags($contents);
    return $contents;
}

//本分に不要な要素を削除する[1]
function filter1($dom){
    if(str_get_html($dom) != null){
        $dom = str_get_html($dom);
        foreach($dom->find('head,script,noscript,style,header,footer,comment,form,aside') as $key => $element ){
            $element->outertext = '';
        }
        //プロフィールっぽい所は先に削除
        preg_match_all("/<[\w]+([^>]*?)=[\"']([^\"']*?profile[^\"']*?)[\"'][^>]*?>/ius",$dom,$matches2);
        $deletekey = $matches2[1];
        $deleteval = $matches2[2];
        //findで探せる形に整形
        $patterns = convertFindArray($deletekey,$deleteval);
        //削除
        $dom = deleteElement($dom,$patterns);
        return $dom;
    }else{
        echo "htmlの読み込みに失敗しました[2]";
        die();
    }
}

//本分に不要な要素を削除する[2]
function filter2($dom){
    if(str_get_html($dom) != null){
        $dom = str_get_html($dom);
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
        //findで探せる形に整形
        $patterns = convertFindArray($deletekey,$deleteval);
        //削除
        $dom = deleteElement($dom,$patterns);
        return $dom;
    }else{
        echo "htmlの読み込みに失敗しました[3]";
        die();
    }
}

//findで探せる形に変換
function convertFindArray($keys,$vals){
    $arraymax = count($keys);
    $patterns = array();
    for($i=0;$i<$arraymax;$i++){
        $key = trim($keys[$i]);
        $val = $vals[$i];
        $patterns[] = "[$key=$val]";
    }
    return $patterns;
}

//要素の削除
function deleteElement($dom,$patterns){
    foreach($dom->find(implode(',', $patterns)) as $key => $element ) {
        $element->outertext = '';
    }
    return $dom;
}

//本文抽出
function extractBody($html){
    if(str_get_html($html) != null){
        $dom = str_get_html($html);
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
    }else{
        echo "htmlの読み込みに失敗しました[1]";
        die();
    }
    return $contents;
}

//htmlタグなどを取り除く
function removeTags($contents){
    //タグを消去
    $contents = preg_replace('/<("[^"]*"|\'[^\']*\'|[^\'"<>])*>/','',$contents);
    //空白消去
    $contents = preg_replace('/[\s　]/u','',$contents);
    return $contents;
}