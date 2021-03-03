        <h4 class="mt-4"><?=$l['user_settings']?></h4>
        <div class="card mt-2">
            <div class="card-body">
                <form id="password-form" method="post" class="js-validate-form">
                    <div class="row">
                        <div class="col-7">
                            <div class="form-group validate-input" data-validate="<?=$l['password_not_set']?>">
                                <input type="password" class="form-control" name="oldpassw" placeholder="<?=$l['old_password']?>" autocomplete="off">
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['password_not_set']?>">
                                <input type="password" class="form-control" name="password" placeholder="<?=$l['new_password']?>" autocomplete="off">
                            </div>
                            <div class="form-group validate-input" data-validate="<?=$l['password_not_set']?>">
                                <input type="password" class="form-control" name="password2" placeholder="<?=$l['password_again']?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4 offset-1 pr-5">
                            <button class="btn btn-success btn-block" id="btn-submit-passwd"><?=$l['do_password_change']?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card mt-5">
            <div class="card-body">
                <form id="lang-form" method="post" class="js-validate-form">
                    <div class="row">
                        <div class="col-7">
                            <div class="form-group">
                                <label for="lang"><?=$l['ilang']?></label>
                                <select class="form-control" id="lang" name="lang">
                                    <option value="uk"<?php if ($user['uilang']=='uk') echo ' selected'?>>Українська</option>
                                    <option value="en"<?php if ($user['uilang']=='en') echo ' selected'?>>English</option>
                                    <option value="ru"<?php if ($user['uilang']=='ru') echo ' selected'?>>Русский</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-4 offset-1 pr-5 pt-3">
                            <div class="mt-3">
                                <button class="btn btn-warning btn-block" id="btn-submit-lang"><?=$l['ilang_change']?></button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
         <div class="card mt-5">
            <div class="card-body">
                <form id="userdata-form" method="post" class="js-validate-form">
                    <div class="row">
                        <div class="col-7">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><?=$l['full_name']?></div>
                                </div>
                                <input type="text" class="form-control" name="fname" value="<?=$user['fname']?>" autocomplete="off">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><?=$l['phone']?></div>
                                </div>
                                <input type="text" class="form-control" name="phone" value="<?=$user['phone']?>" autocomplete="off">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><?=$l['email']?></div>
                                </div>
                                <input type="email" class="form-control" name="email" value="<?=$user['email']?>" autocomplete="off">
                            </div>
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><?=$l['departament']?></div>
                                </div>
                                <input type="text" class="form-control" name="dept" value="<?=$user['dept']?>" autocomplete="off">
                            </div>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <div class="input-group-text"><?=$l['squad']?></div>
                                </div>
                                <input type="text" class="form-control" name="squad" value="<?=$user['squad']?>" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-4 offset-1 pr-5">
                            <button class="btn btn-info btn-block" id="btn-submit-userdata"><?=$l['userdata_change']?></button>
<?php
    if ($user['syncok']) {
        $sync_ok_hide = '';
        $sync_bad_hide = ' d-none';
    } else {
        $sync_ok_hide = ' d-none';
        $sync_bad_hide = '';
    }
?>
                            <!--div class="sync-info" id="sync-status">
                                <span id="sync-ok" class="text-success<?=$sync_ok_hide?>"><?=$l['sync_ok']?></span>
                                <span id="sync-bad" class="text-danger<?=$sync_bad_hide?>"><?=$l['sync_not_ok']?></span><br />
                                <button class="btn btn-sm btn-outline-secondary mt-2<?=$sync_bad_hide?>" id="btn-submit-usersync"><?=$l['userdata_sync']?></button>
                            </div-->
                        </div>
                    </div>
                </form>
            </div>
        </div>
