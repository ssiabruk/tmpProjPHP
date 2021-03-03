        <div class="page-area mt-4">
            <div class="row">
                <div class="col-9 work-area">
                    <div class="card mb-3">
                        <div class="row card-body">
                            <div class="col-2" id="stream-video-box">

                                <button class="btn btn-outline-success dropdown-toggle" type="button" data-toggle="dropdown" id="stream-video-view" data-close-others="false">
                                    <i class="material-icons" style="line-height:1;">videocam</i>
                                </button>

<div class="dropdown-menu" style="width:280px;height:175px;padding-right:10px;padding-top:5px;" id="stream-video-area">
    <span id="va-<?=$active_complex?>" class="va-info small pl-1"><span class="spinner-grow" role="status" aria-hidden="true" style="margin:15px;"></span></span>
    <img id="mjpeg-<?=$active_complex?>" class="img-fluid mjpeg-img" src="" style="width: 100%;" />
</div>
<form id="stream-form" method="post"><input type="hidden" id="selected-complex" name="cid" value="" /></form>

                            </div>
                            <div class="col-10">
                                <select class="form-control" id="complex-list">
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                                    <option<?php if ($cl['id'] == $active_complex) echo " selected"?> value="<?=$cl['colour']?>" data-id="<?=$cl['id']?>" data-ip="<?=$cl['cip']?>"><?=$cl['cid']?> (<?=$cl['cip']?>)</option>
<?php }?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-1">
                        <div class="card-body" id="one-map-area"></div>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <img src="<?=$site_url?>/images/login-logo.png" alt="Poshuk logo" />
                    <h4 class="mt-5"><?=$l['last_detections']?></h4>
                    <hr />
                    <div id="last-detects"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></div>
                </div>
            </div>
        </div>

        <div class="row mt-3 footer py-1">
            <div class="col-4">&copy; <?=date('Y')?></div>
            <div class="col-4 text-center">
                <a href="<?=$site_url?>/login"><?=$l['singin']?></a>
            </div>
            <div class="col-4 text-right">
                <div class="langs">
                    <a href="<?=$site_url?>/setlang/en" class="login-txt btn-lang<?=($l['lang']==='en')?' active':''?>">EN</a> |
                    <a href="<?=$site_url?>/setlang/uk" class="login-txt btn-lang<?=($l['lang']==='uk')?' active':''?>">UK</a> |
                    <a href="<?=$site_url?>/setlang/ru" class="login-txt btn-lang<?=($l['lang']==='ru')?' active':''?>">RU</a>
                </div>
            </div>

    <script type="text/javascript">var currentPage = 'main';</script>
    <script type="text/javascript" src="<?=$site_url?>/js/popper.min.js"></script>
