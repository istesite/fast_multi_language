<?php
set_time_limit(300);
require_once "translate.class.php";
require_once "functions.php";

$lang = new translate('en');

if (isset($_GET['lng']) and $_GET['lng'] != '' and $_GET['lng'] != $lang) {
	$lang->setTranslate($_GET['lng']);
	$_SESSION['language'] = $_GET['lng'];
}
?>
<!doctype html>

<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?=trans('Site Başlığı') ?></title>
	<meta name="description" content="<?= trans('Site Açıklaması') ?>">
</head>

<body>
<h1><?= trans('İçerik konu başlığı') ?></h1>
<p><?= trans('Merhaba dünya!') ?></p>
</body>
</html>