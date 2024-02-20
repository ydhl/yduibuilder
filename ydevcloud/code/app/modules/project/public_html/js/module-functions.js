function delete_module_confirm(dialogid, value){
    $.post(`/module/${dialogid}/delete.json`, { value:value }, function(rst) {
        if (rst && rst.success){
            window.location.reload();
            return;
        }
        YDJS.toast(rst.msg || 'Oops', YDJS.ICON_ERROR);
    })
}
function delete_project_confirm(dialogid, value){
    $.post(`/project/${dialogid}/delete.json`, { value:value }, function(rst) {
        if (rst && rst.success){
            window.location.href="/project"
            return;
        }
        YDJS.toast(rst.msg || 'Oops', YDJS.ICON_ERROR);
    })
}
function delete_function_confirm(dialogid, value){
    $.post(`/function/${dialogid}/delete.json`, { value:value }, function(rst) {
        if (rst && rst.success){
            window.location.reload();
            return;
        }
        YDJS.toast(rst.msg || 'Oops', YDJS.ICON_ERROR);
    })
}
$(function () {
    $('.reverse2version').on('click', function (){
        var content = $(this).attr('data-content');
        var ok = $(this).attr('data-title');
        var socketURL = $(this).attr('data-socket')
        var token = $(this).attr('data-token')
        var versionid = $(this).attr('data-uuid')
        var pageid = $(this).attr('data-pageid')
        YDJS.confirm(content,'Tip',function (dialogid) {
            YDJS.hide_dialog(dialogid);
            $.post("/project/reverse/"+versionid, {}, function (rst){
                const socket = new WebSocket(socketURL);
                socket.addEventListener('open', function (event) {
                    socket.send(JSON.stringify({
                        action: 'modifiedPage',
                        pageid: pageid,
                        token: token
                    }));
                    socket.close()
                    window.location.reload()
                });
            });
        }, null, ok);
    })

    $(".delete-page").on('click', function () {
        var title = $(this).attr('data-title');
        var content = $(this).attr('data-content');
        var socketURL = $(this).attr('data-socket');
        var pageid = $(this).attr('data-uuid');
        var token = $(this).attr('data-token');
        var id = $(this).attr('data-id');
        YDJS.confirm(content,title,function (dialogid) {
            YDJS.hide_dialog(dialogid);
            $.post("/api/deletepage.json", { pageid:  pageid }, function (rst){
                const socket = new WebSocket(socketURL);
                socket.addEventListener('open', function (event) {
                    socket.send(JSON.stringify({
                        action: 'deletedPage',
                        id: id,
                        pageid: pageid,
                        token: token
                    }));
                    socket.close()
                    window.location.reload()
                });
            })
        }, null, title)

    })
    $(".join-project").on('click', function () {
        var uuid = $(this).attr('data-uuid');
        $.post(`/project/${uuid}/member/join.json`, {}, function(rst) {
            if (rst && rst.success){
                window.location.reload();
                return;
            }
            YDJS.toast(rst.msg || 'Oops', YDJS.ICON_ERROR);
        })
    })
    $(".notjoin-project").on('click', function () {
        var uuid = $(this).attr('data-uuid');
        $.post(`/project/${uuid}/member/disagree.json`, {}, function(rst) {
            if (rst && rst.success){
                window.location.reload();
                return;
            }
            YDJS.toast(rst.msg || 'Oops', YDJS.ICON_ERROR);
        })
    })
    $("#build-project").on('click', function () {
        var log = $("#build-log");
        var uuid = $(this).attr('data-uuid');
        var token = $(this).attr('data-token');
        var url = $(this).attr('data-url');
        YDJS.confirm($(this).attr('data-content'),'Tip',function (dialogid) {
            YDJS.hide_dialog(dialogid);
            $("#build-project").attr('disabled', 'disabled');
            const socket = new WebSocket(url);
            socket.addEventListener('open', function (event) {
                socket.send(JSON.stringify({'uuid': uuid, 'token': token, 'action':'build', 'lang': LANG}));
                log.append('<div>Connected</div>');
            });
            socket.addEventListener('close', function (event) {
                $("#build-project").removeAttr('disabled');
                log.append('<div>Disconnected</div>');
            });
            socket.addEventListener('error', function (event) {
                console.log(event)
                $("#build-project").removeAttr('disabled');
                log.append('<div>Error</div>');
            });
            socket.addEventListener('message', function (event) {
                if (event.data=='done'){
                    $("#build-project").removeAttr('disabled');
                    socket.close()
                    return;
                }
                log.append(`<div>${event.data}</div>`);
                // console.log(log.get(0).scrollHeight, log.get(0).scrollTop);
                log.get(0).scrollTo({ top: log.get(0).scrollHeight });
            });
        }, null, 'Build Now')
    })
    $('.delete-uicomponent').on('click', function (){
        var content = $(this).attr('data-content');
        var uuid = $(this).attr('data-uuid');
        var ok = $(this).attr('data-button');
        YDJS.confirm(content,'Tip',function (dialogid) {
            YDJS.hide_dialog(dialogid);
            $.post("/api/deletepage.json", {pageid: uuid}, function (rst){
                console.log(rst);
                if (rst && rst.success){
                    window.location.reload()
                }else{
                    YDJS.alert(rst.msg || 'oops', 'Oops', YDJS.ICON_ERROR);
                }
            });
        }, null, ok);
    })
})
