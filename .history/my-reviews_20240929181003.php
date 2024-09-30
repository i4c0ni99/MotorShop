e_public.html");
eviews.html");


$reviews=$mysqli->query("SELECT products_id, rate, review, date from feedbacks WHERE users_email =
'{$_SESSION['user']['email']}'");
if ($reviews != null) $result= $reviews;

$main->setContent("dynamic", $body->get());

$main->close();

?>