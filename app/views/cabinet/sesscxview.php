        <input type="hidden" value="<?=$session_id?>" id="sid">
        <div class="page-area top-area" style="padding-top:10px">
            <div class="row">
                <div class="col-8 left-area">
                    <div class="card mt-1">
                        <div class="card-body work-area text-center panorama" style="background-color:#ccc;min-height:400px;" id="dt-img-bg">
                            <img src="<?=$bg_image?>" class="img-fluid-bak" id="dt-img" />
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
                    <div id="tracks-spinner" class="mt-3 ml-1"><span style="width: 3rem; height: 3rem;" class="spinner-grow" role="status" aria-hidden="true"></span></div>
                    <table class="table mt-1 mb-0 d-none" id="tracks">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col"><?=$l['detect_date']?></th>
                                <th scope="col"><?=$l['detect_objs']?></th>
                            </tr>
                        </thead>
                    </table>
                    <div class="table-wrapper-scroll mt-0 mb-3 d-none" id="tracks-data">
                        <table class="table table-hover">
                            <tbody id="tracklist"></tbody>
                        </table>
                    </div>
<?php /*if (is_array($tracks)) {?>
                    <table class="table table-hover table-responsive" id="detectslist">
                        <thead>
                            <tr>
                                <th scope="col">Track ID</th>
                                <th scope="col"><?=$l['detect_obj']?></th>
                            </tr>
                        </thead>
                        <tbody>
<?php foreach ($tracks as $t) {?>
                            <tr class="detect">
                                <td><?=$t['time_stamp']?></td>
                                <td><?=$t['id']?></td>
                            </tr>
<?php }?>
                        </tbody>
                    </table>
<?php } else echo $tracks;*/?>
                    <div class="mb-2 d-none" id="tracks-count">
                        <table class="table table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">Label</th>
                                    <th scope="col">Count</th>
                                </tr>
                            </thead>
                            <tbody id="trackcount"></tbody>
                        </table>
                    </div>
                    <small id="detect-coords">&nbsp;</small>
                    <div id="one-map-area" class="d-none"></div>
                </div>
            </div>
        </div>
        <!--div class="row mt-3">
            <div class="details col-7 mr-4">
                <strong><?=$l['detect_date']?></strong>: <span id="detect-date">&mdash;</span><br />
                <!--strong><?=$l['detect_obj']?></strong>:<br />
                <small id="detect-objects"></small>
            </div>
            <div class="col-1">
                <a href="<?=$site_url?>/sessions" class="btn btn-info btn-sm" title="<?=$l['nav_back']?>"><i class="material-icons small">arrow_back</i></a>
            </div>
        </div-->

        <script type="text/javascript">
            var stype = '<?=$stype?>';
            var cid = '<?=$complex_id?>';
            var sid = '<?=$session_id?>';
            var ccolor = '<?=$complex_color?>';
        </script>
