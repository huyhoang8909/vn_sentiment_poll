<?php
include './db_news.php';

$db_news = new db_news();
$rows = $db_news->query('select * from products where category="smartphone"');
// kt lai dau ..
$special_chars = [":))",":) ","-___-","=]]",";)",":.","^^",":((",";)",":(","^_^","=))","@@","^____^","=((",";)","<3",":(",":3",":'(",";-)","~~","(((","-_",": ))",":'((","=((",":'(",">.<","<3","(´з`)","(y)",":)",":like:",":'(","=]]",":]]",":p ",":'(", "(*)", ":] ", "==", ":'(", "❤️", "☺️","⭐️", "���", ":>", ">_<", "T_T", "=[((", "", "^-^", "😊 🙂", "\"PRODUCT OF DELL'S PROMOTION NOT FOR RESALES\"", "Ý \"HÀNG DỄ VỠ\"", '50 charrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrrr', '1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20 21 22 23 24 25 26 27 28 29 30 31 32 33 34 35 36 37 38 39 40 41 42 43 44 45 46 47 48 49 50.', 'Goodddddddddddddddddddddddddddddddđ. D d d d', '4.8/5♥', '-.-', '^ ^', '=)', ':-s', '@-@','=[[[','Nội dung chứa ít nhất 50 ký tự' , 'Video review của mình lúc mới mua sản phẩm 1 tuần: https://www.youtube.com/watch?v=NUADlZTPB38 . Video kết nối bộ chuột bàn phím MK235 với Smart Tivi Sony mình vừa quay trong tháng 6/2017: https://www.youtube.com/watch?v=0VFneITelgk', 'P/s : ghi thêm cho đủ 50 kí tự theo yêu cầu !', 'có cần phải tối thiểu 50 ký tự không?', 'Haizzz, cái bao da này cũng ko cần phải đủ 50 ký tự đâu Tiki ạ!', 'bắt 50 ký tự nữa cơ,','Hài lòng, này thì 50 ký tự!','nhận xét nhiu đó được r, còn bắt 50 kí tự', 'này thì 50 ký tự', 'Tiki bỏ 50 ký tự đi', '50 từ 1k xu',
];
mb_regex_encoding('UTF-8');
foreach ($rows as $row) {
    $comments = json_decode($row['comment']);
    foreach ($comments->data as $comment) {
        // replace newline by .
        $content = preg_replace('/\s*\n+\s*/', " . ", $comment->content);
        // replace - by .
        $content = preg_replace('/^\s*-\s*/', " . ", $content);
        // replace + by .
        $content = preg_replace('/\s*\+\s*/', " . ", $content);

        $content = str_replace($special_chars, ' . ',$content);
        // replace , . by ,
        $content = preg_replace('/,\s+\.\s/', " , ", $content);
        // remove space
        $content = preg_replace('/\s+/S', " ", $content);
        // remove dot at start position
        $content = preg_replace('/^\s*\.\s*/', "", $content);
        // replace !!+ by !
        $content = preg_replace('/!!+/', "!", $content);
        // replace ??+ by !
        $content = preg_replace('/\?\?+/', "?", $content);
        // replace . 2 . by .
        $content = preg_replace('/\.\s+[1-9]\./', " . ", $content);
        // replace : . by :
        $content = preg_replace('/\:\s+\.\s/', " : ", $content);
        // replace . ))+ by .
        $content = preg_replace('/\s\.\s\)\)+/', " . ", $content);
        // replace ; . by .
        $content = preg_replace('/\,\s*\.\s*[^.]/', " . ", $content);
         // replace , . by .
        $content = preg_replace('/;\s+\./', " . ", $content);
        // replace : * by .
        $content = preg_replace('/:\s*\*/', " . ", $content);
        // replace . * by .
        $content = preg_replace('/\.\s*\*/', " . ", $content);
         // replace . ) by .
        // $content = preg_replace('/\.\s\)\)/', " ) ", $content);
        // remove ..
        $content = preg_replace('/[^.]\.\s*\.[^.]/', " . ", $content);
        $content = str_replace([': . ', '. . ', '! .', '? .', '. -', ': -', '! -', ': ,'], [': ', '. ', '!', '? ', '. ', ': ', '!', ':'],$content);
        $content = preg_replace('/\.\.\.\.+/', ".", $content);
        //50GB -> 50 GB
        $content = preg_replace('/([0-9]+)GB/i', "$1 GB", $content);
        $content = to_lowercase($content);
        $length = count(explode(' ', $content));
        $content = mb_eregi_replace('\:\s*Ưu điểm\s*\:', '. Ưu điểm : ', $content);
        if ($length == 0) continue;
        // if (mb_ereg_match("/Ưu/iu", $content)) {echo $comment->content . PHP_EOL . $content, PHP_EOL; exit;}
        // if (mb_strpos($content, "Ưu:") !== false) {echo $content, PHP_EOL;}
        // if (mb_strpos($content, "Ưu:") !== false) {echo $content, PHP_EOL;}
        $db_news->insertReview([$row['id'], $row['category'], $row['link'], $comment->rating - 1, $content, $length]);
        // echo  . ' '.   . ' '. str_word_count($comment->content), PHP_EOL;
    }
}

/**
 * If there is more than 10 uppercase words in this sentence then convert it back to lowercase
 * @param  [type]  $words [description]
 * @return boolean        [description]
 */
function to_lowercase($sentence) {
    $words = explode(' ', $sentence);
    $num_upper = 0;
    $is_upper = false;
    $last_upper_index = -1;
    $uppercase = array();
    $tmp_upper = '';
    foreach ($words as $key => $word) {
        if (!is_numeric($word) && !in_array($word, ['.', ',', ')', '(', '?', '!', ':', '...', '-']) && mb_strtoupper($word) === $word) {
            if ($last_upper_index == -1) {
                $last_upper_index = $key;
                $tmp_upper = $word;
                $num_upper = 1;
            } elseif ($last_upper_index + 1 == $key){
                $tmp_upper .= ' ' . $word;
                $num_upper++;
                $last_upper_index++;
            }
        } elseif ($num_upper >= 4) {
            $tmp_upper = trim($tmp_upper);
            if (mb_strpos($tmp_upper, 'ASUS ABTU005 ZEN POWER') === false) {
                $uppercase[] = $tmp_upper;
                $last_upper_index = -1;
                $num_upper = 0;
            }
        }
    }

    if ($num_upper >= 4) {
        if (mb_strpos($tmp_upper, 'ASUS ABTU005 ZEN POWER') === false) {
            $uppercase[] = $tmp_upper;
        }
    }

    if (count($uppercase) > 0) {
        var_dump($uppercase);
        return str_replace($uppercase, array_map('mb_strtolower', $uppercase), $sentence);
    }
    return $sentence;
}

