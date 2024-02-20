
function getCookie (name) {
    var arr = ''
    const reg = new RegExp('(^| )' + name + '=([^;]*)(;|$)')
    if ((arr = document.cookie.match(reg))) {
        return unescape(arr[2])
    } else {
        return null
    }
}

$(function () {
    $('body').on('click', '.run-ui-builder', function (){
        var pageid = $(this).attr('data-uuid')||'';// 编辑具体的页面
        var funcid = $(this).attr('data-functionid')||''; // 在功能中添加页面
        var url = $(this).attr('data-url');
        $.post('/api/sso/token.json', {}, function (rst){
            if (rst && rst.success){
                window.location.href = url + 'sso?' + 'uuid=' + pageid + '&functionid='+funcid + '&token=' + rst.data.token + '&lang=' + (getCookie('lang') || '')
            }else{
                YDJS.toast('Oops, Can not open UI Builder')
            }
        })
    })
    $("#btn-sendcode").click(function (event) {
        var email = $.trim($("#email").val())
        if (!email){
            $("#email").addClass('is-invalid')
            setTimeout(function () { YDJS.spin_clear("#btn-sendcode") }, 100);
            return
        }
        var format = $(this).attr("data-format");
        if (format === 'email' && !email.match(/@/)){
            $("#email").addClass('is-invalid')
            setTimeout(function () { YDJS.spin_clear("#btn-sendcode") }, 100);
            return
        }
        $("#email").removeClass('is-invalid')
        $.post("/sendcode.json", { email: email, region: $("#region").val(), 'check': $(event.target).attr('data-check') }, function (rst) {
            if (!rst || !rst.success) {
                YDJS.spin_clear("#btn-sendcode")
                YDJS.toast(rst.msg || 'Some wrong, please try again', YDJS.ICON_ERROR)
                return
            }
            YDJS.spin_clear("#btn-sendcode")
            $("#btn-sendcode").attr('disabled', true)
            $("#btn-sendcode").attr("data-text", $("#btn-sendcode").text())
            tickLeft = 60;
            codetick = setInterval(() => {
                $("#btn-sendcode").text(tickLeft -- )
                if (tickLeft <= 0 ) {
                    clearInterval(codetick)
                    $("#btn-sendcode").attr('disabled', false)
                    $("#btn-sendcode").text($("#btn-sendcode").attr("data-text"))
                }
            }, 1000)
        });
    })
})
