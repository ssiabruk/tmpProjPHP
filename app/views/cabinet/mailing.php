
        <div class="card mt-4">
            <div class="card-body">
                <form id="form-add-contact" method="post">
                    <div class="form-row">
                        <div class="col-2 pt-1">
                            <strong><?=$l['contact_add']?></strong>
                        </div>
                        <div class="col-5">
                            <input type="text" class="form-control" name="contact-input" id="contact-input" placeholder="<?=$l['start_contact_label']?>">
                        </div>
                        <div class="col-3 pt-1 text-center">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ctype" id="ct1" value="email" checked="">
                                <label class="form-check-label" for="ct1">E-mail</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="ctype" id="ct2" value="phone">
                                <label class="form-check-label" for="ct2"><?=$l['contact_tel']?></label>
                            </div>
                        </div>
                        <div class="col-2 text-center">
                            <button type="button" id="contact-add" class="btn btn-success"><?=$l['do_add']?></button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

<?php $c_none = (!isset($mlist) || !is_array($mlist))?'':' d-none'?>
        <h4 id="no-contacts" class="mt-4<?=$c_none?>"><?=$l['contacts_none']?></h4>

        <br /><br />
        <div class="row" id="contacts-list">

<?php if (isset($mlist) && is_array($mlist)) foreach($mlist as $ml) {?>
            <div class="col-4 mb-3" data-id="<?=$ml['id']?>">
                <div class="card">
                    <div class="card-body row pt-2 pb-2">
                        <div class="col-8">
                            <span class="contact-data"><?=$ml['contact']?></span>
                        </div>
                        <div class="col-2 offset-2 text-center">
                            <a class="remove-contact text-danger" href="#" data-id="<?=$ml['id']?>">
                                <i class="material-icons" style="vertical-align:middle;">close</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
<?php }?>

        </div>

<div class="col-4 mb-3 d-none" id="clone-contact" data-id="">
    <div class="card">
        <div class="card-body row pt-2 pb-2">
            <div class="col-8">
                <span class="contact-data"></span>
            </div>
            <div class="col-2 offset-2 text-center del-btn">
                <a class="remove-contact text-danger" href="#" data-id=""><i class="material-icons" style="vertical-align:middle;">close</i></a>
            </div>
        </div>
    </div>
</div>
