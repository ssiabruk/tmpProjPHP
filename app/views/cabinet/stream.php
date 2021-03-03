        <style>
            .video-box {
                min-height:450px;
                background-image:url(/images/novideoyet.png);
                background-repeat:no-repeat;
                background-position: center;
            }
        </style>

        <div class="row mt-4">
<?php
    if (isset($clist) && $clist) {
        if (count($clist) == 1) $col = 12; else $col = 6;
        foreach ($clist as $cl) {
?>
            <div class="col-<?=$col?>">
                <!--div class="card mb-3">
                    <div class="row card-body">
                        <div class="col-12">
                            <form id="stream-form" method="post">
                                <select class="form-control" id="complex-list">
                                    <option value="" disabled="" selected=""><?=$l['select_complex']?></option>
<?php /*if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                                    <option value="<?=$cl['colour']?>" data-id="<?=$cl['id']?>" data-ip="<?=$cl['cip']?>"><?=$cl['cid']?> (<?=$cl['cip']?>)</option>
<?php }*/?>
                                </select>
                                <input type="hidden" id="selected-complex" name="cid" value="" />
                            </form>
                        </div>
                    </div>
                </div-->
                <strong class="fgc-<?=$cl['colour']?:'red'?>"><?=$cl['cid']?></strong>
                <!--input type="hidden" id="cip-<?=$cl['cid']?>" value="<?=$cl['cip']?>" /-->
                <div class="card mb-3">
                    <div class="card-body work-area video-box">
                        <div class="row">
                            <div class="col-12">
                                <span class="video-area" id="va-<?=$cl['id']?>"></span>
                                <img id="mjpeg-<?=$cl['id']?>" class="img-fluid" src="" style="width: 100%;" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<?php }} else echo 'Not found'?>
        </div>

        <form id="stream-form" method="post"><input type="hidden" id="selected-complex" name="cid" value="" /></form>
        <script type="text/javascript">
            var cids = <?=$cids?>;
<?php       //var vport = < ?=$port? >;
          //var modes = [< ?php foreach($modes as $key=>$val) echo "{id:'{$key}', mode:'{$val}'},"? >];?>
        </script>
