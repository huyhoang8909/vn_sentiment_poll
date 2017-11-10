<?php include './db_news.php';
$db_news = new db_news();

$is_first = true;
$limit = 5000;
$page = 0;

do {
$offset = $page * $limit;
$page++;
$query = <<<EOS
  select accessory.*,reviews.price_sentiment from accessory 
  join reviews on accessory.reviews_id=reviews.id
  limit $limit offset $offset
EOS;
$rows = $db_news->query($query);
$is_first = true;

$text = "";
foreach ($rows as $row) {
  $sentiment = $row['sentiment'];

  if ($row['category'] == 'ROOT') {
    if (!$is_first) $text .= PHP_EOL;
    switch ($row['price_sentiment']) {
      case "":
        $sentiment .= 6;
        break;
      case 0:
        $sentiment .= 5;
        break;
      case 1:
        $sentiment .= 5;
        break;
      case 3:
        $sentiment .= 7;
        break;
      case 4:
        $sentiment .= 7;
        break;
      default:
        $sentiment .= 6;
        break;
    }
  }

  $is_first = false;

  $text .= $sentiment . ' ' . $row['content'] . PHP_EOL;
}

file_put_contents('training.txt', $text, FILE_APPEND);
} while (!$is_first);
