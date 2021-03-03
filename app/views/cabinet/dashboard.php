
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
        <div class="row mt-5">
            <div class="col-6 left-area">
                <div class="card">
                    <div class="card-body work-area" id="map-area-<?=$cl['id']?>"></div>
                </div>
            </div>
            <div class="col-6 right-area">
                <div class="row mb-1">
                    <div class="col-7" id="data-area-<?=$cl['id']?>">
                        <strong class="fgc-<?=$cl['colour']?:'red'?>"><?=$cl['cid']?></strong> <small>(<?=$cl['cip']?>)</small><br />
                        <div class="card mb-3">
                            <div class="card-body realtime" id="mode-<?=$cl['id']?>"><?=$l['data_update']?>...</div>
                        </div>
                        <div class="card">
                            <div class="card-body realtime">
                                <div class="row" id="telemetry-<?=$cl['id']?>">
                                    <div class="col-12"><?=$l['data_update']?>...</div>
                                </div>
                            </div>
                        </div>
                    </div>
<?php //if ($user['role'] === 'admin') {?>
                    <div class="col-5">
                        <strong class="ml-1 text-dark"><i class="material-icons small" style="vertical-align:middle;">flash_on</i> <?=$l['btn_control']?></strong>
                        <form id="action-form-<?=$cl['id']?>" method="post">
                            <select class="form-control complex-control mb-3" id="complex-cam-modes-<?=$cl['id']?>">
                                <option value="" disabled="" selected="" class="bg-light text-info"><?=$l['select_video']?> *</option>

    <?php foreach ($all_modes['res'.$cl['camres']] as $am) {?>
<?php /*if (in_array($key, $cmodes[$cl['id']])){?><option value="<?=$key?>">&nbsp;<?=$val?></option><?php }*/?>
<?php if ($cur_env != 'development' && $am['param'] == 'file') continue; ?>
                                <?php if (@in_array($am['param'], $cmodes[$cl['id']])) echo '<option data-sparam="' . $am['param'] . '" data-smode="' . $am['mode'] . '">&nbsp;' . $am['title'] . '</option>';?>

    <?php } ?>

                                <?php /*if (in_array('file', $cmodes[$cl['id']])){?><option value="file">&nbsp;<?=$l['camdemo']?></option><?php }?>
                                <?php if (in_array('3mp', $cmodes[$cl['id']])){?><option value="3mp">&nbsp;<?=$l['cam3mp']?></option><?php }?>
                                <?php if (in_array('6mp_long', $cmodes[$cl['id']])){?><option value="6mp_long">&nbsp;<?=$l['cam6mp_long']?></option><?php }?>
                                <?php if (in_array('6mp_wide', $cmodes[$cl['id']])){?><option value="6mp_wide">&nbsp;<?=$l['cam6mp_wide']?></option><?php }?>
                                <?php if (in_array('12mp_b2', $cmodes[$cl['id']])){?><option value="12mp_b2"><?=$l['cam12mp_b2']?></option><?php }?>
                                <?php if (in_array('12mp_b3', $cmodes[$cl['id']])){?><option value="12mp_b3"><?=$l['cam12mp_b3']?></option><?php }?>
                                <?php if (in_array('20mp_b2', $cmodes[$cl['id']])){?><option value="20mp_b2"><?=$l['cam20mp_b2']?></option><?php }?>
                                <?php if (in_array('20mp_jpg_b2', $cmodes[$cl['id']])){?><option value="20mp_jpg_b2"><?=$l['cam20mp_jpg_b2']?></option><?php }*/?>

                            </select>
                            <?php /*<button type="button" class="btn btn-success btn-sm complex-control btn-block mb-2" id="start-record"><i class="material-icons small">videocam</i> <?=$l['start_record']?></button>
                            <button type="button" class="btn btn-success btn-sm complex-control btn-block mb-3" id="start-recdet"><?=$l['start_recdet']?></button-->
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="recdet-<?=$cl['id']?>" checked="" />
                                <label class="form-check-label" for="recdet-<?=$cl['id']?>">
                                    <?=$l['start_recdet']?>
                                </label>
                            </div>*/?>

                            <button type="button" class="btn btn-success btn-sm btn-block complex-control mb-3 start-btn" id="start-record-<?=$cl['id']?>" data-id="<?=$cl['id']?>">
                                <i class="material-icons small">videocam</i> <?=$l['complex_start']?></button>

                            <button type="button" class="btn btn-danger btn-sm complex-control btn-block stop-btn" id="stop-complex-<?=$cl['id']?>" data-id="<?=$cl['id']?>">
                                <i class="material-icons small">error_outline</i> <?=$l['complex_stop']?></button>
<?php if ($user['role'] === 'oper') {?>
                            <button type="button" class="btn btn-warning btn-sm complex-control btn-block reboot-btn" id="reboot-complex-<?=$cl['id']?>" data-id="<?=$cl['id']?>">
                                <i class="material-icons small">replay</i> <?=$l['complex_reboot']?></button>
<?php }?>
                            <button type="button" class="btn btn-light btn-sm btn-block mt-4 alerts-btn" id="alerts-<?=$cl['id']?>" data-id="<?=$cl['id']?>">
                                <i class="material-icons small">notifications</i>
                            </button>

                            <input type="hidden" id="complex-cam-resolution-<?=$cl['id']?>" name="camres" value="" />
                            <input type="hidden" id="complex-start-mode-<?=$cl['id']?>" name="startmode" value="" />
                            <input type="hidden" id="complex-command-<?=$cl['id']?>" name="command" value="" />
                            <input type="hidden" id="selected-complex-<?=$cl['id']?>" name="cid" value="<?=$cl['id']?>" />
                        </form>
                    </div>
<?php //}?>
                </div>

                <!--div class="tele-area">
                    <div class="text-center">
                        <img class="img-fluid" src="<?=$site_url?>/images/login-logo.png" alt="Пошук logo" />
                    </div>
                    <hr class="mt-4" />
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-secondary btn-block" id="alerts">
                                <i class="material-icons">notifications</i>
                            </button>
                        </div>
                    </div>
                    <hr class="mt-4" />
                    <div class="tech-title text-center">
                        <?=$l['telemetry']?>
                        <span id="btn-tele-update" class="d-none">
                            <button type="button" class="btn btn-light btn-sm" id="tele-update" title="<?=$l['update']?>" style="padding:0 .5rem;">
                                <i class="material-icons small">replay</i>
                            </button>
                        </span>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <select class="form-control" id="complex-list">
                                <option value="" disabled="" selected=""><?=$l['select_complex']?></option>
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                                <option value="<?=$cl['colour']?>" data-id="<?=$cl['id']?>"><?=$cl['cid']?></option>
<?php }?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="tele-data">
                    <div class="card">
                        <div class="card-body">
                            <div class="row" id="tele-data-area">
                                <?=$l['complex_not_select']?>
                            </div>
                        </div>
                    </div>
                </div-->
            </div>
        </div>
<?php } else {?>
        <div class="row mt-5">
            <div class="col-12">
                <h2><?=$l['complexes_not_found']?></h2>
            </div>
        </div>
<?php }?>

        <?php /*<!--div class="modal" id="complex-ctrl" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="ctrltitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="ctrltitle"><?=$l['complex_control']?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="action-form" method="post">
                        <div class="row mb-3">
                            <div class="col-12">
                                <select class="form-control complex-control" id="complex-list-control">
                                    <option value="" disabled="" selected=""><?=$l['select_complex']?> *</option>
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                                    <option value="<?=$cl['colour']?>" data-id="<?=$cl['id']?>"><?=$cl['cid']?> (<?=$cl['cip']?>)</option>
<?php }?>
                                </select>
                                <input type="hidden" id="complex-command" name="command" value="" />
                                <input type="hidden" id="selected-complex" name="cid" value="" />
                            </div>
                        </div>
                        <hr />
                        <span class="grey"><?=$l['select_title']?> *</span>
                        <div class="row mb-5 pt-4">
                            <div class="col-6 mb-3">
                                <select class="form-control complex-control" name="camres" id="complex-cam-resolution">
                                    <option value="" disabled="" selected=""><?=$l['select_video']?></option>
                                    <option value="file" class="d-none">&nbsp;<?=$l['camdemo']?></option>
                                    <option value="3mp" class="d-none">&nbsp;<?=$l['cam3mp']?></option>
                                    <option value="6mp_long" class="d-none">&nbsp;<?=$l['cam6mp_long']?></option>
                                    <option value="6mp_wide" class="d-none">&nbsp;<?=$l['cam6mp_wide']?></option>
                                    <option value="12mp_b2" class="d-none"><?=$l['cam12mp_b2']?></option>
                                    <option value="12mp_b3" class="d-none"><?=$l['cam12mp_b3']?></option>
                                    <option value="20mp_b2" class="d-none"><?=$l['cam20mp_b2']?></option>
                                    <option value="20mp_jpg_b2" class="d-none"><?=$l['cam20mp_jpg_b2']?></option>
                                </select>
                                <input type="hidden" id="complex-start-mode" name="startmode" value="" />
                            </div>
                            <div class="col-6">
                                <div class="row">
                                    <div class="col-5 text-center">
                                        <button type="button" class="btn btn-success btn-sm complex-control" id="start-record"><i class="material-icons small">videocam</i> <?=$l['start_record']?></button>
                                    </div>
                                    <div class="col-7 text-center">
                                        <button type="button" class="btn btn-success btn-sm complex-control" id="start-recdet"><i class="material-icons small">flash_on</i> <?=$l['start_recdet']?></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr />
                        <div class="row pt-3 mb-3">
                            <div class="col-3 text-center">
                                <button type="button" class="btn btn-warning complex-control" id="reboot-complex"><i class="material-icons small">replay</i> <?=$l['complex_reboot']?></button>
                            </div>
                            <div class="col-3 text-center">
                                <button type="button" class="btn btn-danger complex-control" id="stop-complex"><i class="material-icons small">error_outline</i> <?=$l['complex_stop']?></button>
                            </div>
                            <div class="col-3 text-center">
                                <button type="button" class="btn btn-info complex-control" id="info-complex"><i class="material-icons small">info_outline</i> <?=$l['complex_info']?></button>
                            </div>
                            <div class="col-3 text-center">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="material-icons small">close</i> <?=$l['close']?></button>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div-->*/?>

        <div class="modal" id="alerts-view" tabindex="-1" role="dialog" aria-labelledby="alertvtitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger" id="alertvtitle"><?=$l['alarm']?>!</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <strong><?=$l['hazard_info']?>:</strong><br /><br />
                        <div id="alert-result"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=$l['close']?></button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
            var cids = <?=$cids?>;
        </script>