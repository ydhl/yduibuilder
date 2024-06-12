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

        $(`#${id}`).on('hidden.bs.modal', event => {
            $(`#${id}`).remove()
            // 弹窗加载的其他资源一并删除
            $(`[data-page-uuid=${id}]`).remove()
        })
        $(`#${id}`).modal({
            backdrop,
            keyboard
        })
    }
    /**
     * iframe 加载的页面有变化时调用
     * @param pageId 要打开的页面id
     */
    YDECloud.updateIframe = function (pageId){
        const title  = $(`#${pageId} iframe`).get(0).contentDocument.title
        $(`#${pageId} .modal-title`).text(title)
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
     * bootstrap 用model打开url指定的page
     * page 通过iframe和当前页面做隔离
     *
     * @param pageId 要打开的页面id
     * @param url 要打开的地址
     */
    YDECloud.openPage = function ({pageId, url,  esc=true, backdrop= 'yes'}){
        const id = pageId
        $("body").append(`<div class="modal fade" id="${id}">
    <div style="pointer-events: none;display: flex;width: 100vw;height: 100vh;align-items: center;justify-content: center">
        <div class="modal-dialog" style="max-width: none !important;">
            <div class="modal-content" style="width: 80vw;height: 70vh">
                <div class="modal-header">
                    <h5 class="modal-title">Loading</h5>
                    <button type="button" onclick="YDECloud.closeSelf(this)" class="close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <iframe src="${url}" onload="YDECloud.updateIframe('${id}')" style="width: 100%;height: 100%" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>
    </div>`);

        showModal(id, backdrop, esc)
    }

    /**
     * 打开bootstrap的modal
     *
     * @param pageId 要打开的对话框页面
     * @param url 弹窗页面的url，页面的输出本身就包含bootstrap的modal结构
     * @param esc 是否按下esc后关闭对话框
     * @param backdrop yes / no / static
     */
    YDECloud.openModal = function({pageId, url,  esc=true, backdrop= 'yes'}){
        let timer;
        const id = pageId
        timer = setTimeout(() => {
            YDECloud.loading()
        }, 1000)
        YDECloud.loadUrl(url, 'body').then(() => {
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
        $('#'+id).modal('hide')
    }
    /**
     * 弹窗内的元素关闭弹窗
     * @param self
     */
    YDECloud.closeSelf = function(self){
        $(self).parents('.modal').modal('hide')
    }

}())
