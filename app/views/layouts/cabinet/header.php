<!DOCTYPE html>
<html lang="<?=$l['lang']?>">
<head>
    <title><?=$title?></title>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?=$token??''?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/icons.css">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/fonts.css">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="<?=$site_url?>/css/common.css">
    <?=$this->putCss()?>
</head>
<body class="d-flex flex-column h-100">
    <main role="main" class="flex-shrink-0">
    <div class="container">