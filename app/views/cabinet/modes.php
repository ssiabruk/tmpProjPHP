        <h4 class="mt-4"><?=$l['complex_modes']?></h4>
        <div class="row mt-4">
            <div class="col-12">
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                <div class="card mb-4">
                    <div class="card-body">
                    <form id="form<?=$cl['id']?>" method="post">
                        <input type="hidden" name="cid" value="<?=$cl['id']?>" />
                        <div class="row">
                            <div class="col-12" id="cl<?=$cl['id']?>">
                                <span class="badge badge-pill circ bgc-<?=$cl['colour']?:'red'?>">&nbsp;</span> &nbsp;
                                <big><strong>ID:</strong> <span class="cid"><?=$cl['cid']?></span></big> &nbsp;
                                <strong>IP:</strong> <span class="cip"><?=$cl['cip']?></span>:<span class="cpt"><?=$cl['cpt']?></span>
                                <!--button type="button" class="btn btn-success btn-sm btn-save right" title="<?=$l['do_save']?>">
                                    <i class="material-icons small">check</i>
                                </button-->
                                <button type="button" class="btn btn-primary btn-sm btn-sync right mr-4" title="<?=$l['do_sync']?>">
                                    <i class="material-icons small">sync</i>
                                </button>
                                <hr />
                                <div class="row" id="modes-<?=$cl['id']?>">
<?php
    $no_modes = true;
    foreach ($all_modes['res'.$cl['camres']] as $am) {
        //if (in_array($key, $cmodes[$cl['id']])){
        if (isset($cmodes[$cl['id']]) && in_array($am['param'], $cmodes[$cl['id']])) {
            $no_modes = false;
?>
                                    <div class="col-6">&bull; &nbsp;<?=$am['title']?></div>
<?php }}
        if ($no_modes) echo '<div class="col-12 text-danger"> &nbsp; ', $l['complex_no_modes'], '!</div>';
?>
                                    <?php /*<!--div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['file'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="file-<?=$cl['id']?>" value="file">
                                            <label class="custom-control-label" for="file-<?=$cl['id']?>"><?=$l['camdemo']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['3mp'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="3mp-<?=$cl['id']?>" value="3mp">
                                            <label class="custom-control-label" for="3mp-<?=$cl['id']?>"><?=$l['cam3mp']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['6mp_long'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="6mp_long-<?=$cl['id']?>" value="6mp_long">
                                            <label class="custom-control-label" for="6mp_long-<?=$cl['id']?>"><?=$l['cam6mp_long']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['6mp_wide'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="6mp_wide-<?=$cl['id']?>" value="6mp_wide">
                                            <label class="custom-control-label" for="6mp_wide-<?=$cl['id']?>"><?=$l['cam6mp_wide']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['12mp_b2'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="12mp_b2-<?=$cl['id']?>" value="12mp_b2">
                                            <label class="custom-control-label" for="12mp_b2-<?=$cl['id']?>"><?=$l['cam12mp_b2']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['12mp_b3'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="12mp_b3-<?=$cl['id']?>" value="12mp_b3">
                                            <label class="custom-control-label" for="12mp_b3-<?=$cl['id']?>"><?=$l['cam12mp_b3']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['20mp_b2'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="20mp_b2-<?=$cl['id']?>" value="20mp_b2">
                                            <label class="custom-control-label" for="20mp_b2-<?=$cl['id']?>"><?=$l['cam20mp_b2']?></label>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="custom-control custom-checkbox">
                                            <input <?=isset($cmodes[$cl['id']]['20mp_jpg_b2'])?'checked':''?> type="checkbox" name="mode[]" class="custom-control-input" id="20mp_jpg_b2-<?=$cl['id']?>" value="20mp_jpg_b2">
                                            <label class="custom-control-label" for="20mp_jpg_b2-<?=$cl['id']?>"><?=$l['cam20mp_jpg_b2']?></label>
                                        </div>
                                    </div-->*/?>

                                </div>
                            </div>
                        </div>
                    </form>
                    </div>
                </div>
<?php } else echo '<h3>', $l['complex_not_added'], '</h3>'?>
            </div>
        </div>
