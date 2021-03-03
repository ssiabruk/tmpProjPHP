
<?php if (is_array($sessions) && $sessions) {?>
                                    <table class="table table-borderless table-hover table-light">
                                        <tbody id="t-<?=array_values($sessions)[0]['complex_id']?>">
<?php foreach ($sessions as $s) {?>
                                            <tr id="<?=$s['session_id']?>">
                                                <td class="sess-chk-td">
                                                    <input type="checkbox" data-stype="<?=$stype?>" data-sname="<?=$s['session_id']?>" data-cid="<?=$s['complex_id']?>" class="sess-chk" />
                                                </td>
                                                <td class="col">
                                                    <strong><?=$s['session_start']??$l['sessions_nodate']?> <strong class="text-danger">&mdash;</strong> <?=$s['session_stop']??$l['sessions_nodate']?></strong><br />
                                                    <?php if ($s['session_type']) { echo ($s['session_type'] == 'detect')?
                                                        '<span class="badge badge-primary">' . $s['session_type'] . '</span>':
                                                        '<span class="badge badge-warning">' . $s['session_type'] . '</span>';}?>

                                                    <small class="text-secondary">
                                                        <?=$s['session_id']?>
                                                        

                                                    </small>
                                                </td>
                                                <td><a href="<?=$site_url?>/sessions/view/<?=$stype?>/<?=$s['session_id']?>" class="btn btn-info btn-sm"><i class="material-icons small">visibility</i></a></td>
                                            </tr>
<?php } ?>
                                        </tbody>
                                    </table>
<?php } ?>