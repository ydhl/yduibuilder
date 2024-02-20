function avatar_callback(up, file, response) {
    if (response && response.success) {
        $("#avatar-img").attr('src', response.data.url)
        $("#avatar-img").removeClass('d-none')
        $("#avatar").val(response.data.url)
    }
}
$(function () {
    $("#save-profile").click(function () {
        $.post("/profile.json", { nickname: $("#nickname").val(), 'avatar': $("#avatar").val() }, function (rst) {
            YDJS.spin_clear("#save-profile")
            if (!rst || !rst.success) {
                YDJS.toast(rst.msg || 'Somethine Wrong, Please Try Again', YDJS.ICON_ERROR)
                return;
            }

            YDJS.toast('Save Success')
        })
    })
    $("#btn-singinpwd").click(function (){
        $.post("/profile/signinpwd.json", { old_password: $("#old_password").val(), 'new_password': $("#new_password").val(), 'new_password1': $("#new_password1").val() }, function (rst) {
            YDJS.spin_clear("#btn-singinpwd")
            if (!rst || !rst.success) {
                YDJS.toast(rst.msg || 'Somethine Wrong, Please Try Again', YDJS.ICON_ERROR)
                return;
            }

            YDJS.toast('Save Success')
        })
    })
    $("#btn-cellphone").click(function () {
        $.post("/profile/cellphone.json", { cellphone: $("#email").val(), code: $("#code").val(), region: $("#region").val()}, function (rst) {
            YDJS.spin_clear("#btn-cellphone")
            if (!rst || !rst.success) {
                YDJS.toast(rst.msg || 'Some wrong, please try again', YDJS.ICON_ERROR)
                return
            }
            YDJS.toast('Save Success')
        });
    });
    $("#btn-email").click(function () {
        $.post("/profile/email.json", { email: $("#email").val(), code: $("#code").val()}, function (rst) {
            YDJS.spin_clear("#btn-email")
            if (!rst || !rst.success) {
                YDJS.toast(rst.msg || 'Some wrong, please try again', YDJS.ICON_ERROR)
                return
            }
            YDJS.toast('Save Success')
        });
    });
})