<?php include './db_news.php';
set_time_limit(0);
$db_news = new db_news();

$page = 1;
$limit = LIMIT;
$total = $db_news->query("select count(*) as count from tmp")[0]['count'];
$num_page = ceil($total/$limit);
for ($page = 1; $page <= $num_page; $page++) {
  $offset = ($page - 1) * $limit;
  $rows = $db_news->update("update tmp join (select id from tmp limit $limit offset $offset) t using(id) set `group` = $page");
}

?>
