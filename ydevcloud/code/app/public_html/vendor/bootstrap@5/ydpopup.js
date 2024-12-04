/*! ydecloud 弹窗处理 */
if (!this.YDECloud) {
    this.YDECloud = {};
}
( function() {
    function showModal(id, backdrop, esc) {
        backdrop = backdrop.toLowerCase();
        let keyboard =  esc;
        if (typeof esc == 'string'){
            keyboard =  esc.toLowerCase()=='true' ? true : false;
        }
        switch (backdrop){
            case 'yes': backdrop = true;break;
            case 'no': backdrop = false;break;
        }

        document.getElementById(id).addEventListener('hidden.bs.modal', event => {
            $(`#${id}`).remove()
            // 弹窗加载的其他资源一并删除
            $(`[data-page-uuid=${id}]`).remove()
        })

        const model = new bootstrap.Modal(`#${id}`, {
            backdrop,
            keyboard
        })
        model.show()
    }
    /**
     * 加载提示框
     */
    YDECloud.loading = function (){
        if(!$('#ydecloud-loading').length){
            $("body").append(`<div class="w-100 h-100" 
style="display: flex!important;justify-content: center;align-items: center; position: fixed;left: 0;top: 0;z-index: 9999999" id="ydecloud-loading">
    <div class="card shadow-sm">
        <div class="card-body bg-secondary">
            <div class="spinner-border text-white" role="status">
              <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>
</div>`);
        }
    }
    YDECloud.hideLoading = function (){
        $('#ydecloud-loading').remove()
    }
    /**
     * 打开bootstrap的modal
     *
     * @param currPageId 当前页面id
     * @param pageId 要打开的对话框页面
     * @param url 弹窗页面的url，页面的输出本身就包含bootstrap的modal结构
     * @param esc 是否按下esc后关闭对话框
     * @param backdrop yes / no / static
     */
    YDECloud.openModal = function({currPageId, pageId, url,  esc=true, backdrop= 'yes', events={}}){
        let timer;
        const listen = []
        for (const eventName in events) {
            listen.push(`${eventName}="${events[eventName]}"`)
        }
        const id = pageId
        timer = setTimeout(() => {
            YDECloud.loading()
        }, 1000)

        $(`[data-uiid="${currPageId}"]`).append(`<div ${listen.join(' ')} id="${id}Body"></div>`);
        YDECloud.loadUrl(url, `#${id}Body`).then(() => {
            clearTimeout(timer)
            YDECloud.hideLoading()
            showModal(id, backdrop, esc)
        })
    }

    /**
     * 根据modal id关闭弹窗
     * @param id
     */
    YDECloud.closeModal = function(id){
        const model = bootstrap.Modal.getOrCreateInstance(document.getElementById(id))
        model.hide()
    }
    /**
     * 弹窗内的元素关闭弹窗
     * @param self
     */
    YDECloud.closeSelf = function(self){
        const model = bootstrap.Modal.getOrCreateInstance($(self).parents('.modal').get(0))
        model.hide()
    }

}())
