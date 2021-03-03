<!DOCTYPE html>
<html lang="<?=$l['lang']?>">
<head>
    <title><?=$title?></title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?=$token?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/icons.css">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/fonts.css">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/bootstrap.min.css">
    <?=$this->putCss()?>
</head>
<body>

<div class="limiter">
    <div class="container-login100 container">
    <div class="wrap-login100">