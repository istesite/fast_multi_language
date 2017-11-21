<?php 
set_time_limit(300); 
require_once "translate.class.php"; 
require_once "functions.php"; 

$lang = new translate('en'); 
//echo $lang->detectBrowserLanguage()."\n<br>"; 
//echo "<pre>".var_export($lang->yandexSupportedLangs(), true)."</pre>"; 
//echo "<pre>".var_export($_SERVER['HTTP_ACCEPT_LANGUAGE'], true)."</pre>"; 

if (isset($_GET['lng']) and $_GET['lng'] != '' and $_GET['lng'] != $lang) { 
    $lang->setTranslate($_GET['lng']); 
    $_SESSION['language'] = $_GET['lng']; 
} 
?> 
<!doctype html> 

<html lang="en"> 
<head> 
    <meta charset="utf-8"> 
    <title><?=trans('Site Ba?l???') ?></title> 
    <meta name="description" content="<?= trans('Site Aç?klamas?') ?>"> 
</head> 

<body> 
<h1><?= trans('?çerik konu ba?l???') ?></h1> 
<p><?= trans('Merhaba dünya!') ?></p> 
</body> 
</html> 