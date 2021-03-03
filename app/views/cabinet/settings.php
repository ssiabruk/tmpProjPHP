        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-4 text-center pt-2 pb-2">
                        <a href="<?=$site_url?>/settings/modes" class="btn btn-secondary btn-sm">
                            <i class="material-icons">photo_camera</i> <?=$l['complex_modes']?>
                        </a>
                    </div>
                    <div class="col-4 text-center pt-2 pb-2">
                        <a href="<?=$site_url?>/settings/mailing" class="btn btn-secondary btn-sm">
                            <i class="material-icons">email</i> <?=$l['mailing']?>
                        </a>
                    </div>
                    <div class="col-4 text-center pt-2 pb-2">
                        <a href="<?=$site_url?>/settings/users" class="btn btn-secondary btn-sm">
                            <i class="material-icons">group</i> <?=$l['users']?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-8">
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-7" id="cl<?=$cl['id']?>">
                                <big><strong>ID:</strong> <span class="cid"><?=$cl['cid']?></span></big>
                                <span class="badge badge-pill circ bgc-<?=$cl['colour']?:'red'?>" title="<?=$l['map_color']?>">&nbsp;</span>
                                <input type="hidden" class="color" value="<?=$cl['colour']?:'red'?>"/>
                                <strong class="onoff"><?=$cl['cstatus']?></strong>
                                <input type="hidden" class="status" value="<?=$cl['cstatus']?>"/>
                                <br />
                                <strong>IP:</strong> <span class="cip"><?=$cl['cip']?></span>:<span class="cpt"><?=$cl['cpt']?></span> &nbsp; | &nbsp;
                                <strong>CamRes:</strong> <span class="cres"><?=$cl['camres']?></span> Mp
                                <!--strong>Key:</strong> <span class="ckey"><?=$cl['ckey']?></span-->
                                <input type="hidden" value="<?=$cl['ckey']?>" class="ckey" />
                            </div>
                            <div class="col-5 text-right pr-3 sess-util-buttons">
                                <button type="button" data-id="<?=$cl['id']?>" class="btn btn-info btn-sm mr-4 btn-test" title="<?=$l['test_link']?>">
                                    <i class="material-icons small">settings_remote</i>
                                </button>
                                <button type="button" data-id="<?=$cl['id']?>" class="btn btn-success btn-sm mr-4 btn-edit" title="<?=$l['edit']?>">
                                    <i class="material-icons small">edit</i>
                                </button>
                                <button type="button" data-id="<?=$cl['id']?>" class="btn btn-danger btn-sm mr-4 btn-delete" title="<?=$l['delete']?>">
                                    <i class="material-icons small">clear</i>
                                </button>
                                <button type="button" data-id="<?=$cl['id']?>" class="btn btn-warning btn-sm btn-reboot complex-control" title="<?=$l['complex_reboot']?>">
                                    <i class="material-icons small">replay</i>
                                </button>
                                <form id="action-form-<?=$cl['id']?>" method="post">
                                    <input type="hidden" id="complex-command-<?=$cl['id']?>" name="command" value="" />
                                    <input type="hidden" id="selected-complex-<?=$cl['id']?>" name="cid" value="<?=$cl['id']?>" />
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
<?php } else echo '<h3>', $l['complex_not_added'], '</h3>'?>
                <div class="mt-5"><hr /></div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="logger" name="logger"<?=($logger=='on')?' checked':''?>>
                                    <label class="custom-control-label" for="logger"><?=$l['logger']?></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-4">
                <div class="card">
                    <div class="card-body">
                        <form id="add-form" method="post" class="js-validate-form">
                            <div class="mb-3">
                                <span><?=$l['add_new_complex']?></span>
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['id_not_set']?>">
                                <input type="text" class="form-control" name="cid" placeholder="<?=$l['complex_id']?>" autocomplete="off">
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['ip_not_set']?>">
                                <input type="text" class="form-control" name="cip" placeholder="<?=$l['complex_ip']?>" autocomplete="off">
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['port_not_set']?>">
                                <input type="text" class="form-control" name="cpt" placeholder="<?=$l['complex_port']?>" autocomplete="off">
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['key_not_set']?>">
                                <input type="text" class="form-control" name="ckey" placeholder="<?=$l['complex_key']?>" autocomplete="off">
                            </div>
                            <div class="form-check form-check-inline text-center ml-5">
                                <input class="form-check-input" type="radio" name="camres" id="camres12" value="12">
                                <label class="form-check-label" for="camres12"> 12 Mp </label>
                            </div>
                            <div class="form-check form-check-inline ml-5">
                                <input class="form-check-input" type="radio" name="camres" id="camres20" value="20">
                                <label class="form-check-label" for="camres20"> 20 Mp </label>
                            </div>
                            <div class="mb-3"></div>
                            <div class="form-group mb-4">
                                <select class="form-control" name="colour">
                                    <option value="" disabled="" selected=""><?=$l['select_color']?></option>
                                    <option value="red"><?=$l['red']?></option>
                                    <option value="pink"><?=$l['pink']?></option>
                                    <option value="purple"><?=$l['purple']?></option>
                                    <option value="indigo"><?=$l['indigo']?></option>
                                    <option value="blue"><?=$l['blue']?></option>
                                    <option value="green"><?=$l['green']?></option>
                                    <option value="yellow"><?=$l['yellow']?></option>
                                    <option value="orange"><?=$l['orange']?></option>
                                </select>
                            </div>
                            <button class="btn btn-info btn-block" id="btn-submit-add"><?=$l['do_add']?></button>
                        </form>
                    </div>
                </div>
                <div class="text-center mt-3 grey">
                    <!--strong>Network: <?=$ip_range?></strong-->
                </div>
                <!--div class="text-center mt-3">
                    <hr class="mb-5" />
                    <a href="<?=$site_url?>/settings/modes" class="btn btn-secondary btn-sm">
                        <i class="material-icons">camera_enhance</i> <?=$l['complex_modes']?>
                    </a>
                </div-->
            </div>
        </div>

        <div class="modal" id="chcomplex" tabindex="-1" role="dialog" aria-labelledby="chcomplextitle" aria-hidden="true">
            <form id="edit-form" method="post" class="js-validate-form">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="chcomplextitle"><?=$l['complex_edit']?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group validate-input" data-validate="<?=$l['id_not_set']?>">
                            <input type="text" class="form-control" id="cid" name="cid" placeholder="<?=$l['complex_id']?>" autocomplete="off">
                        </div>
                        <div class="form-group validate-input" data-validate="<?=$l['ip_not_set']?>">
                            <input type="text" class="form-control" id="cip" name="cip" placeholder="<?=$l['complex_ip']?>" autocomplete="off">
                        </div>
                        <div class="form-group validate-input" data-validate="<?=$l['port_not_set']?>">
                            <input type="text" class="form-control" id="cpt" name="cpt" placeholder="<?=$l['complex_port']?>" autocomplete="off">
                        </div>
                        <div class="form-group validate-input" data-validate="<?=$l['key_not_set']?>">
                            <input type="text" class="form-control" id="ckey" name="ckey" placeholder="<?=$l['complex_key']?>" autocomplete="off">
                        </div>
                        <div class="form-check form-check-inline text-center ml-1">
                            <input class="form-check-input" type="radio" name="camres" id="cres12" value="12">
                            <label class="form-check-label" for="cres12"> 12 Mp </label>
                        </div>
                        <div class="form-check form-check-inline ml-5">
                            <input class="form-check-input" type="radio" name="camres" id="cres20" value="20">
                            <label class="form-check-label" for="cres20"> 20 Mp </label>
                        </div>
                        <div class="mb-3"></div>
                        <div class="form-group mb-4">
                            <select class="form-control" id="colour" name="colour">
                                <option value="" disabled="" selected=""><?=$l['select_color']?></option>
                                <option value="red"><?=$l['red']?></option>
                                <option value="pink"><?=$l['pink']?></option>
                                <option value="purple"><?=$l['purple']?></option>
                                <option value="indigo"><?=$l['indigo']?></option>
                                <option value="blue"><?=$l['blue']?></option>
                                <option value="green"><?=$l['green']?></option>
                                <option value="yellow"><?=$l['yellow']?></option>
                                <option value="orange"><?=$l['orange']?></option>
                            </select>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="cstatus" name="cstatus" value="on">
                                <label class="custom-control-label" for="cstatus"><?=$l['complex_status']?></label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" id="cledit"><?=$l['do_save']?></button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal"><?=$l['cancel']?></button>
                        <input type="hidden" id="id" name="id" value="" />
                    </div>
                </div>
            </div>
            </form>
        </div>

        <div class="modal" id="test" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <h5><?=$l['testing_link']?></h5>
                        <div id="test-wait" class="mt-2"><span class="spinner-border text-info" role="status" aria-hidden="true"></span></div>
                        <div id="test-result"></div>
                    </div>
                    <div class="modal-footer">
                        <button id="test-close" type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            <i class="material-icons small">close</i>
                        </button>
                    </div>
                </div>
            </div>
        </div>