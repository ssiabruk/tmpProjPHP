        <div class="page-area mt-4">
            <div class="row">
                <div class="col-9 work-area">
                    <div class="card mb-3">
                        <div class="row card-body">
                            <div class="col-2" id="stream-video-box">
                                <button class="btn btn-outline-success dropdown-toggle" type="button" data-toggle="dropdown" id="stream-video-view" data-close-others="false" title="<?=$l['stream_title']?>">
                                    <i class="material-icons" style="line-height:1;">videocam</i>
                                </button>
<div class="dropdown-menu" style="width:280px;height:175px;padding-right:10px;padding-top:5px;overflow:hidden;" id="stream-video-area">
    <span id="va-<?=$active_complex?>" class="va-info small pl-1"><span class="spinner-grow" role="status" aria-hidden="true" style="margin:15px;"></span></span>
    <img id="mjpeg-<?=$active_complex?>" class="img-fluid mjpeg-img" src="" style="width: 100%;" />
</div>
<form id="stream-form" method="post"><input type="hidden" id="selected-complex" name="cid" value="" /></form>
                            </div>
                            <div class="col-7">
                                <select class="form-control" id="complex-list">
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                                    <option<?php if ($cl['id'] == $active_complex) echo " selected"?> value="<?=$cl['colour']?>" data-id="<?=$cl['id']?>" data-ip="<?=$cl['cip']?>"><?=$cl['cid']?> (<?=$cl['cip']?>)</option>
<?php }?>
                                </select>
                            </div>
                            <div class="col-3 text-right">
                                <button class="btn btn-outline-dark mr-4" type="button" id="set-base-marker" title="<?=$l['base_marker']?>" data-toggle="button" aria-pressed="false" autocomplete="off">
                                    <i class="material-icons" style="line-height:1;">add_location</i>
                                </button>
                                <button class="btn btn-outline-warning dropdown-toggle" type="button" data-toggle="dropdown" id="notify-off-btn" data-close-others="false" title="<?=$l['notify_off_title']?>">
                                    <i class="material-icons" style="line-height:1;">notifications_off</i>
                                </button>
<div class="dropdown-menu dropdown-menu-right" style="width:320px;height:150px;padding-right:10px;padding-top:5px;" id="notify-off">
    <label for="notify-range"><?=$l['notify_off_time']?></label>: <strong><span id="time-off">25</span></strong>
    <input type="range" class="custom-range" min="1" max="50" value="25" id="notify-range">
    <button class="btn btn-warning btn-block mt-3" id="btn-notify-off"><?=$l['notify_off_title']?></button>
</div>
<form id="notify-form" method="post"><input type="hidden" id="notify-time-off" name="timeoff" value="" /></form>
                            </div>
                        </div>
                    </div>
                    <div class="card mt-1">
                        <div class="card-body" id="one-map-area"></div>
                    </div>
                </div>
                <div class="col-3 text-center mt-3">
                    <h4><?=$l['last_detections']?></h4>
<?php /*if (isset($has_detection)) {?>
                    <button type="button" class="btn btn-danger btn-sm right mr-4" id="clear-base">
                        <i class="material-icons small">close</i>
                    </button>
<?php }*/ ?>
                    <hr />
                    <div id="last-detects"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></div>
                </div>
            </div>
        </div>
