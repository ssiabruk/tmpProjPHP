        <div class="row">
            <div class="col-12">
                <div class="mt-3">
                    <div class="work-area">
                        <div class="card mr-1 ml-1">
                            <div class="row card-body">
                                <div class="col-12">
                                    <select class="form-control" id="storage-types">
                                        <option value="local" <?=($stype == 'local')?'selected':''?>><?=$l['storage_local']?></option>
                                        <option value="complex" <?=($stype == 'complex')?'selected':''?>><?=$l['storage_complex']?></option>
                                        <option value="remote" <?=($stype == 'remote')?'selected':''?>><?=$l['storage_remote']?></option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <script type="text/javascript">var sess_none = '<h4><?=$lang_sessions_none?></h4>';</script>

                        <div class="row mt-4">
<?php if (isset($clist) && $clist) foreach ($clist as $cl) {?>
                            <div class="col-lg-<?=($ccount == 1)?'12':'6'?> col-md-12 <?=($ccount > 2)?'mb-4':''?>">
                                <div class="card mr-1 ml-1">
                                <div class="row card-body">
                                    <div class="col-12" id="cl<?=$cl['id']?>">
                                        <big class="mr-3"><span class="cid fgc-<?=$cl['colour']?:'red'?>"><?=$cl['cid']?></span></big>
                                        <strong>IP:</strong> <span class="cip"><?=$cl['cip']?></span>

<button class="btn btn-outline-secondary dropdown-toggle right" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="material-icons small">settings</i></button>
<div class="dropdown-menu">
    <a class="dropdown-item disabled"><strong><?=$l['sessions_actions']?></strong></a>
    <div role="separator" class="dropdown-divider"></div>
<?php if ($user['role'] === 'admin') {?>
    <a class="dropdown-item act-local d-none" href="#" data-ctype="act-local" data-id="<?=$cl['id']?>"><?=$l['sessions_remove']?></a>
    <a class="dropdown-item act-remote d-none" href="#" data-ctype="act-remote" data-id="<?=$cl['id']?>"><?=$l['sessions_delete']?></a>
<?php }?>
    <a class="dropdown-item act-complex d-none" href="#" data-ctype="act-complex" data-id="<?=$cl['id']?>"><?=$l['sessions_move']?></a>
    <a class="dropdown-item" href="#" id="check-<?=$cl['id']?>" data-id="<?=$cl['id']?>"><?=$l['sessions_select']?></a>
    <a class="dropdown-item" href="#" id="clear-<?=$cl['id']?>" data-id="<?=$cl['id']?>"><?=$l['sessions_clear']?></a>
</div>

                                        <hr class="row mt-4" />
                                    </div>
                                    <div id="s<?=$cl['id']?>" class="sess-area sess-scrollbar mr-2 ml-2"></div>
                                </div>
                                </div>
                            </div>
<?php } else echo '<h3 class="ml-3">', $l['complex_not_found'], '</h3>'?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
