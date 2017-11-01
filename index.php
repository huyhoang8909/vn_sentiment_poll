<?php include './db_news.php';
$db_news = new db_news();

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
  // get ip of client
  if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
  } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
  } else {
    $ip = $_SERVER['REMOTE_ADDR'];
  }

  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $reviews_id = $_POST['id'];
  $value = '';
  var_dump($_POST);
  // update review's content
  if (isset($_POST['value'])) {
    echo 'hello';
    $value = $_POST['value'];
    $db_news->update("update tmp set content='$value' where id = $reviews_id limit 1");
    $db_news->update("update reviews set content='$value' where id = $reviews_id limit 1");
  } else {
    $value = $_POST['sentiment'];
    $db_news->updateTmpSentiment($_POST['id'], $_POST['sentiment'], $_POST['name']);
    echo $db_news->updateReviewsSentiment($_POST['id'], $_POST['sentiment'], $_POST['name']);
  }
  //update log
  $db_news->update("insert into log(ip,user_agent,reviews_id,`column`,value) values ('$ip', '$user_agent', {$_POST['id']}, '{$_POST['name']}', '$value') ");

  exit;
} else {

  $page = $_GET['page'] ?: 1;
  $rows = $db_news->query("select * from tmp where `group` = $page order by is_done, rand() limit " . LIMIT);
  $num_complete = $db_news->query("select count(*) as count from tmp where is_done = 1")[0]['count'];
  $metas = $db_news->query("SELECT `group`, SUM(is_done=0) as count FROM tmp group by `group`");
}
?>

<!DOCTYPE html>
<html>
<head>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

  <!-- Optional theme -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

  <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/css/bootstrap-editable.css" rel="stylesheet"/>

  <script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>

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
  <h2>Trạng thái</h2>

  <h2>Phrases Table</h2>
  <ul class="pagination">
    <?php foreach ($metas as $meta) : ?>
    <li><a href="?page=<?php echo $meta['group'] ?>"><?php echo $meta['group'] ?>
      <small><span class="badge"><?php echo $meta['count']?></span></small>
    </a> </li>
    <?php endforeach; ?>
  </ul>

  <div class="table-responsive">
  <table class="table table-striped the-table">
    <thead>
      <tr>
        <th class="col-md-6">Nội dung</th>
        <th class="col-md-2">Cảm xúc câu</th>
        <th class="col-md-1">Cảm xúc khía cạnh thiết kế</th>
        <th class="col-md-1">Cảm xúc khía cạnh giá</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach($rows as $key => $row) :?>
      <tr style="<?php if ($row['is_done']) print 'background-color: beige;'?>" >
        <td class="col-md-6">
          <a style="border-bottom: none;" href="#" class="editable" id="content" data-type="textarea" data-id="<?php echo $row['id'] ?>" data-url="/vn_sentiment_poll/index.php" ><strong><?php echo $row['content'] ?></strong></a>
        </td>
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
<!-- Latest compiled and minified JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/bootstrap3-editable/js/bootstrap-editable.min.js"></script>

<script type="text/javascript">
  $(document).ready(function() {
    $("input").on( "click", function( event ) {
      that = $(this)
      siblings = that.siblings('[type="hidden"]')
      id = siblings.first().val();
      name = that.attr('name').split('-')[0]

      $.ajax({
        method:'POST',
        url:'/vn_sentiment_poll/index.php',
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
    $('.editable').editable({
      success: function(response, newValue) {
        console.log(response);
      }
    });
  });

  $.fn.editable.defaults.mode = 'inline';
</script>


</body>
</html>
