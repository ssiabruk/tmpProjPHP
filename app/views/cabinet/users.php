        <h4 class="mt-4 mb-4"><?=$l['users_title']?></h4>

        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th scope="col"><?=$l['login']?></th>
                    <th scope="col"><?=$l['role']?></th>
                    <th scope="col"><?=$l['fname']?></th>
                    <th scope="col"><?=$l['phone']?></th>
                    <th scope="col"><?=$l['email']?></th>
                    <th scope="col" class="text-center"><i class="material-icons small" style="vertical-align:middle;">flash_on</i></th>
                </tr>
            </thead>
            <tbody>
<?php if (isset($ulist) && is_array($ulist)) foreach($ulist as $ul) {?>
                <tr data-id="<?=$ul['id']?>"<?php if ($ul['id'] == $user['id']) echo 'class="tr-disabled"'?>>
                    <td><strong><?=$ul['ulogin']?></strong></td>
                    <td>
                        <select class="custom-select custom-select-sm" id="role-<?=$ul['id']?>" style="width:70%;">
                            <option value="admin" <?php if($ul['urole']=='admin') echo 'selected'?>><?=$l['role_admin']?></option>
                            <option value="oper" <?php if($ul['urole']=='oper') echo 'selected'?>><?=$l['role_oper']?></option>
                            <option value="disabled" <?php if($ul['urole']=='disabled') echo 'selected'?>><?=$l['role_disabled']?></option>
                        </select>
                    </td>
                    <td><?=$ul['fname']?:'&mdash;'?></td>
                    <td><?=$ul['phone']?:'&mdash;'?></td>
                    <td><?=$ul['email']?:'&mdash;'?></td>
                    <td class="text-center">
                        <button type="button" data-id="<?=$ul['id']?>" class="btn btn-success btn-sm mr-4 btn-user-save">
                            <i class="material-icons small">check</i>
                        </button>
                        <button type="button" data-id="<?=$ul['id']?>" class="btn btn-danger btn-sm btn-user-del">
                            <i class="material-icons small">clear</i>
                        </button>
                    </td>
                </tr>
<?php }?>
            </tbody>
        </table>

<style>.tr-disabled {pointer-events: none;opacity: 0.75;color: #999;}</style>
