        <form id="login-form" method="post" class="login100-form js-validate-form flex-sb flex-w">
            <span class="mb-3">
                <a href="<?=$site_url?>">
                    <img src="<?=$site_url?>/images/login-logo-big.png" alt="POSHUK logo" />
                </a>
            </span>
            <div class="wrap-input100 validate-input" data-validate="<?=$l['login_not_set']?>">
                <input class="input100" type="text" name="username" placeholder="<?=$l['login']?>" autocomplete="off">
            </div>
            <div class="wrap-input100 validate-input" data-validate="<?=$l['password_not_set']?>">
                <input class="input100" type="password" name="password" placeholder="<?=$l['password']?>">
            </div>
            <div class="flex-sb-m">
                <div class="langs">
                    <a href="<?=$site_url?>/setlang/en" class="login-txt btn-lang<?=($l['lang']==='en')?' active':''?>">EN</a>
                    <a href="<?=$site_url?>/setlang/uk" class="login-txt btn-lang<?=($l['lang']==='uk')?' active':''?>">UK</a>
                    <a href="<?=$site_url?>/setlang/ru" class="login-txt btn-lang<?=($l['lang']==='ru')?' active':''?>">RU</a>
                </div>
                <div>
                    <a href="<?=$site_url?>/restore" class="login-txt"><?=$l['restore']?></a>
                </div>
            </div>
            <div class="container-login100-form-btn">
                <button class="login100-form-btn" id="btn-submit-login"><?=$l['singin']?></button>
            </div>
        </form>

        <div class="copy">&copy; <?=date('Y')?></div>