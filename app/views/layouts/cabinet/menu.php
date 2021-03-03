        <div class="menu mb-2">
            <ul class="nav nav-fill">
                <li class="menu-logo">
                    <img src="<?=$site_url?>/images/login-logo.png" alt="Poshuk logo" />
                </li>
                <li class="menu-home">
                    <a href="<?=$site_url?>/" class="main-menu"><i class="material-icons">home</i></a>
                </li>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'map') echo ' active'?>" href="<?=$site_url?>/map"><i class="material-icons">map</i> <?=$l['mnu_map']?></a>
                </li>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'stream') echo ' active'?>" href="<?=$site_url?>/stream"><i class="material-icons">ondemand_video</i> <?=$l['mnu_stream']?></a>
                </li>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'sessions') echo ' active'?>" href="<?=$site_url?>/sessions"><i class="material-icons">storage</i> <?=$l['mnu_sess']?></a>
                </li>
<?php if ($user['role'] === 'admin') {?>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'settings') echo ' active'?>" href="<?=$site_url?>/settings"><i class="material-icons">settings</i> <?=$l['mnu_settings']?></a>
                </li>
<?php }?>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'diagnostic') echo ' active'?>" href="<?=$site_url?>/diagnostic"><i class="material-icons">network_check</i> <?=$l['mnu_diagnostic']?></a>
                </li>
                <li class="nav-item">
                    <a class="main-menu<?php if ($active_menu_item == 'profile') echo ' active'?>" href="<?=$site_url?>/profile"><i class="material-icons">account_box</i> <?=$l['mnu_profile']?></a>
                </li>
                <li class="nav-item">
                    <a href="<?=$site_url?>/logout"><i class="material-icons">exit_to_app</i> <?=$l['mnu_exit']?></a>
                </li>
            </ul>
        </div>