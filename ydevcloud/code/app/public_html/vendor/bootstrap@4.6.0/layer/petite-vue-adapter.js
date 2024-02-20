/**
 * petite vue component
 * @param props
 * @return {{pageMaximize: pageMaximize, pageMinimize: pageMinimize, state: *, pageResume: pageResume}}
 */
function layerPageComponent(props) {
    return {
        state: props?.state || '',
        pageId: props?.pageId,
        title: decodeURI(props?.title||'loading'),
        url: props?.url,
        iframeLoaded: function (event, obj){
            this.title = $('#' + this.pageId + ' iframe').get(0).contentDocument.title
        },
        pageMaximize: function(pageId){
            var index = $('#'+pageId).attr('data-layer-index')
            this.state = 'max'
            layer.full(index)
        },
        pageMinimize: function(pageId){
            var index = $('#'+pageId).attr('data-layer-index')
            this.state = 'min'
            layer.min(index)
            $('#'+pageId).attr('data-layer-state','minimize')
            var length = $("[data-layer-state='minimize']").length
            $('#layui-layer'+index).css({height: '68px', width: '300px', bottom: '0', left:((length-1) * 300 + 16) + 'px', top:'unset'})
        },
        pageResume: function (pageId){
            var index = $('#'+pageId).attr('data-layer-index')
            this.state = ''
            layer.restore(index)
        }
    }
}
