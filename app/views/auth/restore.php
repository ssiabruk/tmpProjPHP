        <div class="text-center">
            <a href="<?=$site_url?>"><img src="<?=$site_url?>/images/login-logo-big.png" alt="Пошук logo" /></a>
        </div>
        <div class="card mt-4">
            <div class="card-body">
                <form id="restore-form" method="post" class="js-validate-form">
                    <div class="mb-3">
                        <span><?=$l['restore_access']?></span>
                    </div>
                    <div class="form-group validate-input" data-validate="<?=$l['login_not_set']?>">
                        <input type="text" class="form-control" name="username" placeholder="<?=$l['login']?>" autocomplete="off">
                    </div>
                    <button class="btn btn-info btn-block" id="btn-submit-restore"><?=$l['do_restore']?></button>
                </form>
            </div>
        </div>
        <div class="mt-3 text-center">
            <strong><?=$l['disabled_in_demo']?></strong>
        </div>
<?php ?>
        <div class="card mt-5">
            <div class="card-body">
                <form id="register-form" method="post" class="js-validate-form">
                    <div class="mb-3">
                        <span><?=$l['register_new_user']?></span>
                    </div>
                    <div class="form-group validate-input" data-validate="<?=$l['login_not_set']?>">
                        <input type="text" class="form-control" name="username" placeholder="<?=$l['login']?>" autocomplete="off">
                    </div>
                    <div class="form-group validate-input" data-validate="<?=$l['password_not_set']?>">
                        <input type="password" class="form-control" name="password" placeholder="<?=$l['password']?>">
                    </div>
                    <div class="form-group validate-input" data-validate="<?=$l['password_not_set']?>">
                        <input type="password" class="form-control" name="password2" placeholder="<?=$l['password_again']?>">
                    </div>
                    <button class="btn btn-info btn-block" id="btn-submit-register"><?=$l['do_register']?></button>
                </form>
            </div>
        </div>
<?php ?>

        <div class="copy">&copy; <?=date('Y')?></div>