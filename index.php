<?php include './db_news.php';
$db_news = new db_news();
// var_dump($_REQUEST);exit;
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  echo $db_news->updateReviewsSentiment($_POST['id'], $_POST['sentiment'], $_POST['name']);
  exit;
} else {

$sql = <<<EOS
  select *
  from (
  SELECT content,group_concat(category) as category,group_concat(sentiment) as sentiment,count(*) as count
  FROM vietnamese_treebank.laptop
  where is_in_dict = 0
  group by content
  having count(distinct sentiment) > 1) t
EOS;
$page = $_GET['page'] ?: 1;
$limit = 250;
$total = $db_news->query("select count(*) as count from tmp")[0]['count'];
$num_page = ceil($total/$limit);
$offset = ($page - 1) * $limit;
$rows = $db_news->query("select * from tmp limit $limit offset $offset");

}
?>
<!DOCTYPE html>
<html>
<head>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

  <!-- Latest compiled and minified JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
  <title>Sentiment Verification</title>
</head>
<body>
  <style type="text/css">
    .the-table {
    table-layout: fixed;
    word-wrap: break-word;
    }
  </style>
  <div class="container">
  <h2>Phrases Table</h2>

  <ul class="pagination">
    <?php for($i = 1; $i<= $num_page; $i++): ?>
    <li><a href="?page=<?php echo $i ?>"><?php echo $i ?></a></li>
    <?php endfor; ?>
  </ul>

  <div class="table-responsive">
  <table class="table table-striped the-table">
    <thead>
      <tr>
        <th class="col-md-1">id</th>
        <th class="col-md-5">content</th>
        <th class="col-md-2">general sentiment</th>
        <th class="col-md-1">design sentiment</th>
        <th class="col-md-1">price sentiment</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $key => $row) :?>
      <tr>
        <td class="col-md-1"><strong><?php echo $row['id'] ?></strong></td>
        <td class="col-md-5"><strong><?php echo $row['content'] ?></strong></td>
        <td class="col-md-2">
          <label>0&nbsp;</label><input type="radio" value="0" name="sentiment-<?php echo $key ?>">
          1&nbsp;<input type="radio" value="1" name="sentiment-<?php echo $key ?>" <?php if ($row['sentiment'] == 1) echo "checked"?> >
          2&nbsp;<input type="radio" value="2" name="sentiment-<?php echo $key ?>" <?php if ($row['sentiment'] == 2) echo "checked"?> >
          3&nbsp;<input type="radio" value="3" name="sentiment-<?php echo $key ?>" <?php if ($row['sentiment'] == 3) echo "checked"?> >
          4&nbsp;<input type="radio" value="4" name="sentiment-<?php echo $key ?>" <?php if ($row['sentiment'] == 4) echo "checked"?> >
          <input type="hidden" value="<?php echo $row['id'] ?>">
        </td>
        <td class="col-md-1">
          <label>0&nbsp;</label><input type="radio" value="0" name="design_sentiment-<?php echo $key ?>">
          1&nbsp;<input type="radio" value="1" name="design_sentiment-<?php echo $key ?>" <?php if ($row['design_sentiment'] == 1) echo "checked"?> >
          2&nbsp;<input type="radio" value="2" name="design_sentiment-<?php echo $key ?>" <?php if ($row['design_sentiment'] == 2) echo "checked"?> >
          3&nbsp;<input type="radio" value="3" name="design_sentiment-<?php echo $key ?>" <?php if ($row['design_sentiment'] == 3) echo "checked"?> >
          4&nbsp;<input type="radio" value="4" name="design_sentiment-<?php echo $key ?>" <?php if ($row['design_sentiment'] == 4) echo "checked"?> >
          <input type="hidden" value="<?php echo $row['id'] ?>">
        </td>
        <td class="col-md-1">
          <label>0&nbsp;</label><input type="radio" value="0" name="price_sentiment-<?php echo $key ?>">
          1&nbsp;<input type="radio" value="1" name="price_sentiment-<?php echo $key ?>" <?php if ($row['price_sentiment'] == 1) echo "checked"?> >
          2&nbsp;<input type="radio" value="2" name="price_sentiment-<?php echo $key ?>" <?php if ($row['price_sentiment'] == 2) echo "checked"?> >
          3&nbsp;<input type="radio" value="3" name="price_sentiment-<?php echo $key ?>" <?php if ($row['price_sentiment'] == 3) echo "checked"?> >
          4&nbsp;<input type="radio" value="4" name="price_sentiment-<?php echo $key ?>" <?php if ($row['price_sentiment'] == 4) echo "checked"?> >
          <input type="hidden" value="<?php echo $row['id'] ?>">
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
  $("input").on( "click", function( event ) {
    that = $(this)
    siblings = that.siblings('[type="hidden"]')
    id = siblings.first().val();
    name = that.attr('name').split('-')[0]

    $.ajax({
      method:'POST',
      url:'/crawl_tiki/index.php',
      data: {id: id, sentiment: that.val(), name: name},
      success: function(data){
        // console.log(data)
        if (data) {
          that.parent().last().append('<img class="img-thumbnail" width=20 src="http://www.clker.com/cliparts/6/d/6/3/l/M/check-mark-md.png">')
        }
        // $('#myResponse').html(data);
      }
    });
  });
});
</script>
</body>
</html>
