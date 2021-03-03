
var fixFollFlag = false;

/*$.ajaxSetup({
    timeout: 5000
});*/

$('.main-menu').on('click', function(e){
    e.preventDefault();
    showNavProc();
    let t_href = $(this).attr('href');
    window.location.href = t_href;
    return true;
});

window.onbeforeunload = function(){
    showNavProc();
};

function showNavProc()
{
    fixFollFlag = true;
    let fgDiv = '<div class="nav-proc mt-auto"><span style="width: 3rem; height: 3rem;" class="spinner-border text-danger" role="status" aria-hidden="true"></span></div>';
    $('body').append(fgDiv);
}