
/**
 * layer 增加esc 关闭当前激活的对话框的功能
 */
document.addEventListener('keydown', (keyEvent) => {
    if (keyEvent.code !== 'Escape') {
        return;
    }

    var modals = {}
    $(".layui-layer").each(function (index, el) {
        var style = window.getComputedStyle(el);
        if(style.zIndex){
            var layer = $(el).find('[data-layer-index]')
            modals[style.zIndex] = {
                index: layer.attr("data-layer-index"),
                esc: layer.attr("data-layer-esc")
            }
        }
    })

    var topIndex = Object.keys(modals).sort().pop();
    if (topIndex && modals[topIndex].esc == 'yes') {
        layer.close(modals[topIndex].index)
    }

    return true
})
