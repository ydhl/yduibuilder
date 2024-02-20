
var codetick, tickLeft;
$(function (){
    $("#email").keyup(function () {
        $("#region").attr('disabled', $("#email").val().match(/@/))
    })
    $("#btn-signin").click(function () {
        $.post("/signin.json", { email: $("#email").val(), code: $("#code").val(), region: $("#region").val()}, function (rst) {
            if (!rst || !rst.success) {
                YDJS.spin_clear("#btn-signin")
                YDJS.toast(rst.msg || 'Some wrong, please try again', YDJS.ICON_ERROR)
                return
            }
            window.location.href="/dashboard"
        });
    });
    $("#btn-signin-pwd").click(function () {
        $.post("/signin.json", { email: $("#email").val(), pwd: $("#password").val(), region: $("#region").val()}, function (rst) {
            if (!rst || !rst.success) {
                YDJS.spin_clear("#btn-signin-pwd")
                YDJS.toast(rst.msg || 'Some wrong, please try again', YDJS.ICON_ERROR)
                return
            }
            window.location.href="/dashboard"
        });
    });
})