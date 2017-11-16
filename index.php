<?php
include './db_news.php';
include './helper.php';

$db_news = new db_news();

// get ip of client
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
  $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
  $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
  $ip = $_SERVER['REMOTE_ADDR'];
}

$table_tmp = 'tmp';

if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

  $user_agent = $_SERVER['HTTP_USER_AGENT'];
  $reviews_id = $_POST['id'];
  $value = '';

  // update review's content
  if (isset($_POST['value'])) {
    $value = $_POST['value'];
    $reviews_id = $_POST['pk'];

    $db_news->update("update $table_tmp set content='$value' where id = $reviews_id limit 1");
    $db_news->update("update reviews set content='$value' where id = $reviews_id limit 1");
  } else {
    $value = $_POST['sentiment'];
    $db_news->updateTmpSentiment($reviews_id, $_POST['sentiment'], $_POST['name'], $table_tmp);
    echo $db_news->updateReviewsSentiment($reviews_id, $_POST['sentiment'], $_POST['name']);
  }
  //update log
  $db_news->update("insert into log(ip,user_agent,reviews_id,`column`,value,created_at) values ('$ip', '$user_agent', $reviews_id, '{$_POST['name']}', '$value', now()) ");
  $db_news->update("UPDATE status set modified_at = now() WHERE ip ='$ip' limit 1");
  exit;
} else {
  $page = empty($_GET['page']) ? 1 : $_GET['page'];
  // remove any ip without refresh page more than 1 hours
  $db_news->update("DELETE FROM status WHERE modified_at < DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
  // save in use pages to db
  $db_news->update("INSERT INTO status(ip, page,modified_at) VALUES ('$ip', $page, now()) ON DUPLICATE KEY UPDATE page = $page, modified_at = now()");
  $rows = $db_news->query("select * from $table_tmp where `group` = $page order by is_done, rand() limit " . LIMIT);
  $num_complete = $db_news->query("select count(*) as count from $table_tmp where is_done = 1")[0]['count'];
  $metas = $db_news->query("SELECT `group`, SUM(is_done=0) as count FROM $table_tmp group by `group`");
  $in_use_pages = $db_news->query("SELECT DISTINCT `page` FROM status");
  $in_use_pages = $in_use_pages ? array_value_recursive($in_use_pages) : [];
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
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title">Hướng dẫn</h3>
      </div>
      <div class="panel-body">
        <ul>
        <li>Cảm xúc của câu: là cảm xúc toàn bộ câu</li>
        <li>Cảm xúc của khía cạnh thiết kế: là cảm xúc của khía cạnh thiết kế được đề cập trong câu. Ví dụ: thiết kế này đẹp, mẫu mà này ổn, tai nghe không ôm...</li>
        <li>Cảm xúc của khía cạnh giá: là cảm xúc của khía cạnh giá được đề cập trong câu</li>
        <li>Giá trị cảm xúc là:
          <ul>
          <li>0: rất tiêu cực (very negative)</li>
          <li>1: tiêu cực (negative)</li>
          <li>2: bình thường (neutral)</li>
          <li>3: tích cực (positive)</li>
          <li>4: rất tích cực (very positive)</li>
          </ul>
        </li>
        <li>Tổng cộng có: 4041 câu chia thành 41 trang. Mỗi trang có 100 câu</li>
        <li>Câu đã xong sẽ được <span style="background-color: beige;" >tô màu</span></li>
        <li>Có thể chỉnh sửa lại nội dung của câu bằng cách click vào câu đó.</li>
        <li> Chú thích thêm về trang </li>
        <ul>
          <li>
            <small><span class="badge">99</span></small> <i>Hiện tại trang này còn 99 câu chưa được nhận xét xong và không có ai xem trang này</i>
          </li>
          <li>
            <small><span class="badge" style="color: greenyellow;">99</span></small> <i>Hiện tại trang này còn 99 câu chưa được nhận xét xong và ai đó đang xem trang này</i>
          </li>
        </ul>
        <li><strong>Lưu ý: Câu nào không có giá trị cảm xúc của khía cạnh (thiết kế, giá) thì không cần chọn và khi cập nhật giá trị cảm xúc thành công phải có dấu check màu xanh</strong> <img class="img-thumbnail" width=20 src="http://www.clker.com/cliparts/6/d/6/3/l/M/check-mark-md.png"></li>
        </ul>
      </div>
    </div>

  <h2>Reviews</h2>
  <ul class="pagination">
    <?php foreach ($metas as $meta) : ?>
    <li><a href="?page=<?php echo $meta['group'] ?>"><?php echo $meta['group'] ?>
      <small><span class="badge" <?php if (in_array($meta['group'], $in_use_pages)) print 'style="color: greenyellow;"' ?> >
        <?php echo $meta['count']?>
      </span></small>
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
          <a style="border-bottom: none;" href="#" class="editable" id="content" data-type="textarea" data-pk="<?php echo $row['id'] ?>" data-url="/vn_sentiment_poll/index.php" ><strong><?php echo $row['content'] ?></strong></a>
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
        // console.log(response);
      }
    });
  });

  $.fn.editable.defaults.mode = 'inline';
</script>


</body>
</html>
