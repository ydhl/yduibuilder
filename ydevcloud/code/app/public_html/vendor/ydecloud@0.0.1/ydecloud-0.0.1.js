/*! ydecloud v0.0.1 */
if (!this.YDECloud) {
    this.YDECloud = {};
}
( function() {
    function isPlainObject (val) {
        return toString.call(val) === '[object Object]'
    }

    /**
     *
     * @param args
     * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
     * @return {{}}
     */
    function formatObjectData(args, idSuffix=''){
        const data = {}
        for (const key in args) {
            data[key] = args[key]['id'] ? YDECloud.val(args[key]['id'], idSuffix) : args[key]['value']
        }
        return data
    }

    /**
     *
     * @param args
     * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
     * @return {[]}
     */
    function formatArrayData(args, idSuffix=''){
        const data = []
        for (const key in args) {
            data.push(key + '=' + (args[key]['id'] ? YDECloud.val(args[key]['id'], idSuffix) : args[key]['value']))
        }
        return data
    }

    /**
     *
     * @param args
     * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
     * @return {FormData}
     */
    function parseFormData(args, idSuffix=''){
        const fromData = new FormData()
        for (const key in args) {
            // TODO 上传文件
            fromData.append(key, (args[key]['id'] ? YDECloud.val(args[key]['id'], idSuffix) : args[key]['value']))
        }
        return fromData
    }

    /**
     *
     * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
     * @param args
     */
    function parseJsonData(args, idSuffix=''){

    }

    /**
     *
     * @param el
     * @param as
     * @param data
     * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
     */
    function outputAS(el, as, data, idSuffix=''){
        if (!el) return
        const outputAs = as.toUpperCase()
        const type = el.getAttribute('data-type')
        import(`./${type}.js`).then((module) => {
            // console.log(module)
            module.render(el, outputAs, data)
        })
    }
    if (typeof YDECloud.val !== 'function') {
        /**
         *
         * @param id
         * @param value
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {*}
         */
        YDECloud.val = function(id, idSuffix='', value = undefined) {
            id = id + idSuffix
            if (value === undefined){
                return document.querySelector(`#${id} [name="${id}"]`).value
            }else{
                document.querySelector(`#${id} [name="${id}"]`).value = value
            }
        }
    }
    if (typeof YDECloud.parsePath !== 'function') {
        /**
         * 对路径参数进行赋值
         * @param path string
         * @param pathArgs Object
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {*}
         */
        YDECloud.parsePath = function(path, pathArgs, idSuffix='') {
            for (const key in pathArgs) {
                path = path.replace(new RegExp(`\{${key}\}`), pathArgs[key]['id'] ? YDECloud.val(pathArgs[key]['id'], idSuffix) : pathArgs[key]['value'])
            }
            return path
        }
    }
    if (typeof YDECloud.setCookie !== 'function') {
        /**
         * 处理cookie数据
         * @param cookieArgs Object
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {{}}
         */
        YDECloud.setCookie = function(cookieArgs, idSuffix='') {
            const cookies = formatArrayData(cookieArgs, idSuffix)
            for(const item of cookies) {
                document.cookie = `${item}; path=/`
            }
        }
    }
    if (typeof YDECloud.bindOutput !== 'function') {
        /**
         * 处理输出绑定
         * @param outputData Object 输出的数据
         * @param outputArgs Object 数据id及其绑定的uiid
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {{}}
         */
        YDECloud.bindOutput = function(outputData, outputArgs, idSuffix='') {
            // {"arr":[{"name":{"id":{"Text004FC04C9D":"HTML","Text86E52225FD":""}}}]}
            for(const key in outputData) {
                if (Array.isArray(outputData[key]) || isPlainObject(outputData[key])){
                    YDECloud.bindOutput(outputData[key], outputArgs?.[key], idSuffix)
                }else{
                    if( ! outputArgs?.[key]?.['id']) continue
                    for (const id in outputArgs[key]['id']) {
                        const el =document.getElementById(id)||null
                        if (!el) continue
                        outputAS(el, outputArgs[key]['id'][id]||'', outputData[key], idSuffix)
                    }
                }
            }
        }
    }
    if (typeof YDECloud.parseParam !== 'function') {
        /**
         * 对请求参数或x-www-form-urlencoded格式对数据赋值
         * @param args Object
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {string}
         */
        YDECloud.parseParam = function(args, idSuffix='') {
            return formatObjectData(args, idSuffix)
        }
    }
    if (typeof YDECloud.parseBodyData !== 'function') {
        /**
         * 处理body请求体数据
         *
         * @param bodyType string
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @param args Object
         * @return {string|*}
         */
        YDECloud.parseBodyData = function(bodyType, args, idSuffix='') {
            switch (bodyType){
                case 'none': return '';
                case 'form-data': return parseFormData(args, idSuffix);
                case 'x-www-form-urlencoded': return YDECloud.parseParam(args, idSuffix);
                case 'json': return parseJsonData(args, idSuffix);
                default: return 'not support ' + bodyType;
            }
        }
    }
    if (typeof YDECloud.parseHeader !== 'function') {
        /**
         * 处理header和cookie数据
         * @param headerArgs Object
         * @param idSuffix string args中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {{}}
         */
        YDECloud.parseHeader = function(headerArgs, idSuffix='') {
            const header = formatObjectData(headerArgs, idSuffix)
            return header
        }
    }
    if (typeof YDECloud.parseAuth !== 'function') {

        /**
         * 处理auth数据
         * @param idargs Object
         * @param idSuffix string idargs中的id后缀，主要用于解决组件被重复使用时，通过idSuffix来区分是那个组件中
         * @return {{}}
         */
        YDECloud.parseAuth = function(idargs, idSuffix='') {
            return formatObjectData(args, idSuffix)
        }
    }
    if (typeof YDECloud.isEmptyObject !== 'function') {

        /**
         * 判断对象是否是空对象
         * @param args Object
         * @return boolean
         */
        YDECloud.isEmptyObject = function(args) {
            if (!args) return false
            for (const t in args) {
                return !1
            }
            return !0
        }
    }
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
