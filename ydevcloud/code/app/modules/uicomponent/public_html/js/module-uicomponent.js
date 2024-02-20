
$(function () {
    $('.delete-uicomponent').on('click', function (){
        var content = $(this).attr('data-content');
        var uuid = $(this).attr('data-uuid');
        var ok = $(this).attr('data-button');
        YDJS.confirm(content,'Tip',function (dialogid) {
            YDJS.hide_dialog(dialogid);
            $.post("/uicomponent/delete.json", {uuid: uuid}, function (rst){
                window.location.reload()
            });
        }, null, ok);
    })
})
