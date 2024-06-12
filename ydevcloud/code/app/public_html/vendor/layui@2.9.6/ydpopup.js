/*! ydecloud 弹窗处理 */
if (!this.YDECloud) {
    this.YDECloud = {};
}
( function() {
    if (typeof YDECloud.openPage !== 'function'){
        /**
         * 用layer封装 page的打开
         *
         * @param pageId 要打开的页面id
         * @param url 要打开的地址
         */
        YDECloud.openPage = function ({pageId, url}){
            var index = layer.open({
                title: 'loading'
                ,shade: 0
                ,skin: 'layer-adapter'
                ,shadeClose: false
                ,area: ['80%','80%']
                ,maxmin: true
                ,resize: true
                ,fixed: false
                ,zIndex: layer.zIndex
                ,type: 2
                ,minStack: true
                ,closeBtn: true
                ,content: url
                ,success: function (el){
                    const title = el.find("iframe").get(0).contentDocument.title
                    var index = layer.getFrameIndex(title)
                    layer.title(title, index)
                }
            });
        }
    }
    if (typeof YDECloud.openModal !== 'function'){
        /**
         * 用layer封装bootstrap的modal
         *
         * @param pageId 要打开的对话框页面
         * @param esc 是否按下esc后关闭对话框
         * @param backdrop yes / no / static
         * @param position 打开位置，默认居中['center','center']
         */
        YDECloud.openModal = function({pageId,  esc=true, backdrop= 'yes', position= ['center','center']}){
            if (!document.getElementById(pageId)){
                var template = document.getElementById(pageId + 'template')
                var clon = template.content.cloneNode(true);
                document.body.appendChild(clon);
            }

            backdrop = backdrop.toLowerCase();
            var offset = 'auto';
            var position = (position ? position.join('-') : 'center-center').toLowerCase();
            switch (position){
                case 'left-top': offset='lt';break;
                case 'center-top':  offset='t';break;
                case 'right-top':  offset='rt';break;
                case 'left-center':  offset='l';break;
                case 'right-center':  offset='r';break;
                case 'left-bottom':  offset='lb';break;
                case 'center-bottom':  offset='b';break;
                case 'right-bottom':  offset='rb';break;
                case 'center-center':
                default:  offset='auto';break;
            }
            var el = $('#'+pageId)
            var index = layer.open({
                title: false
                ,move: '.move-handler'
                ,skin: 'layer-adapter'
                ,shade: backdrop=='no' ? 0 : 0.5
                ,shadeClose: backdrop!=='static'
                ,maxmin: false
                ,resize: false
                ,fixed: backdrop!='no'
                ,offset: offset
                ,zIndex: layer.zIndex
                ,type: 1
                ,closeBtn: false
                ,content: el
                ,success: function (el){
                    // console.log(el)
                }
                ,end: function (){
                    $('#'+pageId).hide();
                }
            });
            el.attr('data-layer-index', index)
            el.attr('data-layer-esc', esc?'yes':'no')
        }
    }
    if (typeof YDECloud.closeModal !== 'function'){
        YDECloud.closeModal = function(pageId){
            var index = $('#'+pageId).attr('data-layer-index')
            layer.close(index)
        }
    }
    if (typeof YDECloud.layerTop !== 'function'){
        YDECloud.layerTop = function(pageId) {
            // console.log('layerTop('+pageId+')')
            layer.setTop($('#' + pageId).parents('.layui-layer'))
        }
    }
    if (typeof YDECloud.alert !== 'function'){
        YDECloud.alert = function(msg) {
            layer.alert(msg, {
                icon: -1,
                closeBtn: 0,
                shadeClose: true,
                title: false
            });
        }
    }

}())
