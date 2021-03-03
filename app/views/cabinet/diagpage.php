
        <div class="card mt-4">
            <div class="card-body">
                <h4 class="mb-4"><?=$l['diag_result']?></h4>
                <div id="diag-result">...</div>
            </div>
        </div>
        <div class="card mt-4">
            <div class="card-body row">
                <div class="col-4">
                    <button class="btn btn-warning btn-block" id="btn-diag-start"><?=$l['diag_start']?></button>
                </div>
<?php if ($user['role'] === 'admin') {?>
                <div class="col-3 offset-2">
                    <button class="btn btn-success btn-block" id="btn-start"><?=$l['service_start']?></button>
                </div>
                <div class="col-3">
                    <button class="btn btn-danger btn-block" id="btn-stop"><?=$l['service_stop']?></button>
                </div>
<?php }?>
            </div>
        </div>
