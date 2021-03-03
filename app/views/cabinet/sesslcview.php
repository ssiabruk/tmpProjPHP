        <input type="hidden" value="<?=$session_id?>" id="sid">
        <div class="page-area top-area" style="padding-top:10px">
            <div class="row">
                <div class="col-8 left-area">
                    <div class="card mt-1">
                        <div class="card-body work-area text-center panorama" style="background-color:#ccc;min-height:395px;padding:20px;" id="dt-img-bg">
                            <img src="<?=$bg_image?>" class="bak-img-fluid-1" id="dt-img" />
                        </div>
                        <div id='controls' class='disabled'>
                            <button type="button" class="btn btn-link" id="zoomOut" title="Zoom out">
                                <i class="material-icons" style="vertical-align:middle;">zoom_out</i>
                            </button>
                            <button type="button" class="btn btn-link" id="fit" title="Fit image">
                                <i class="material-icons" style="vertical-align:middle;">fullscreen</i>
                            </button>
                            <button type="button" class="btn btn-link" id="zoomIn" title="Zoom in">
                                <i class="material-icons" style="vertical-align:middle;">zoom_in</i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-4">
                    <div id="dobj">
                        <strong><?=$l['detect_date']?></strong>: <span id="detect-date">&mdash;</span><br />
                        <strong><?=$l['detect_objs']?></strong>:<br />
                        <small id="detect-objects">&hellip;</small><br />
                    </div>
                    <small id="detect-coords" class="d-none-">&nbsp;</small>
                    <div id="one-map-area"></div>
                </div>
            </div>
        </div>
        <div id="detects" class="mt-4">
<?php if (isset($detects)) {?>
            <div class="owl-carousel">
<?php foreach ($detects as $d) {?>
                <div class="det-img">
                    <img 
                        class="owl-lazy"
                        data-src="<?=$site_url?>/detects/<?=$complex_id?>/<?=$session_id?>/<?=$d['image']?>-prev.jpg"
                        data-track="<?=$d['track_id']?>"
                        data-dtid="<?=$d['id']?>"
                        data-lat="<?=$d['latitude']?>"
                        data-lon="<?=$d['longitude']?>"
                    />
                </div>
<?php }?>
            </div>
<?php } else echo '<h4 class="text-center pt-4">', $l['sessions_empty'], '</h4>'?>
        </div>

        <script type="text/javascript">
            var stype = '<?=$stype?>';
            var ccolor = '<?=$complex_color?>';
        </script>
