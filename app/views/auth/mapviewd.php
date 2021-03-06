        <input type="hidden" name="dtid" id="dtid" value="<?=$dtid?>" />
        <div class="page-area top-area" style="padding-top:10px">
            <div class="row">
                <div class="col-8 left-area">
                    <div class="card mt-1">
                        <div class="card-body text-center panorama" style="background-color:#ccc;max-height:500px;">
                            <img src="<?=$detect['image']?>" class="img-fluid-bak" id="dt-img" />
                        </div>
                        <div id='controls' class='disabled'>
                            <button type="button" class="btn btn-link" id='zoomOut' title='Zoom out'>
                                <i class="material-icons" style="vertical-align:middle;">zoom_out</i>
                            </button>
                            <button type="button" class="btn btn-link" id='fit' title='Fit image'>
                                <i class="material-icons" style="vertical-align:middle;">fullscreen</i>
                            </button>
                            <button type="button" class="btn btn-link" id='zoomIn' title='Zoom in'>
                                <i class="material-icons" style="vertical-align:middle;">zoom_in</i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div class="mb-5 mt-1">
                        <img src="<?=$site_url?>/images/login-logo.png" alt="Poshuk logo" />
                    </div>
                    <div class="card">
                        <div class="card-body">
                            <strong><?=$l['complex_name']?></strong>: <?=$detect['cid']?><br />
                            <strong><?=$l['complex_ip']?></strong>: <?=$detect['cip']?><br />
                            <strong><?=$l['detect_date']?></strong>: <?=date('d/m/Y H:i:s', $detect['time_stamp'])?><br />
                            <?php if ($detect['latitude'] && $detect['longitude']) echo $detect['latitude'], ', ', $detect['longitude'], '<br />';?>
                            <br /><strong><?=$l['detect_obj']?></strong><br />
                            <small>

                    <?php
                        $dto = json_decode($detect['detect_objects'], true);
                        if (is_array($dto)) {
                            foreach ($dto as $d) {
                                foreach ($d as $key=>$val) {
                                    if ($key == 'label') {
                                        echo $key, ': ', $val;
                                        echo '<br />';
                                    }
                                }
                            }
                            //echo '<br />';
                        }
                    ?>

                            </small><br />
                    <?php if (!$detect['imgfull']){?><span id="data-loading" class="spinner-grow" role="status" aria-hidden="true"></span><?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--div class="mt-5 pt-4">
            <a href="<?=$site_url?>" class="btn btn-secondary btn-block">
                <i class="material-icons" style="vertical-align:middle;">arrow_back</i>
            </a>
        </div-->
        <div class="mt-5 text-center">
            <hr class="mb-4" />
            <button type="button" class="btn btn-danger" onclick="javascript:window.close()">
                <i class="material-icons" style="vertical-align:middle;">close</i>
            </button>
        </div>

        <script type="text/javascript">
            var currentPage = 'page';
            var stype = 'local';
            var hasImage = <?=($detect['imgfull'])?1:0?>;
        </script>
