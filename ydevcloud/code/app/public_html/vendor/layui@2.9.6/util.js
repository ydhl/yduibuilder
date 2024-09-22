
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

function layui_dropdown_translate_data(datas) {
    if(!datas || !Array.isArray(datas) || datas.length === 0) return [];
    const items = []
    const isObject = typeof datas[0] === 'object'
    for (const index in datas) {
        const data = datas[index]
        const item = { title: null, id: null }
        if (!isObject) {
            item.title = data;
            item.id = data;
            items.push(item)
            continue
        }

        if (data.hasOwnProperty('name')) {
            item.title = data.name
        }else{
            item.title = JSON.stringify(data)
        }
        if (data.hasOwnProperty('value')) {
            item.id = data.value
        }else{
            item.id = index
        }
        items.push(item)
    }
    return items
}

function layui_dropdown_init(uiid, menuAlign, staticDatas, outputDataName, inputName, Alpine){
    const el = document.querySelectorAll(`[data-uiid="${uiid}"]`);
    if (!el.length) return;
    const dropdown = layui.dropdown;
    if (el.length > 1) {
        let isArr = false
        if (inputName && inputName.match(/\[-1\]$/)) {
            isArr = true
            // inputName = inputName.replace(/\[-1\]$/, '')
        }
        for (let index = 0; index < el.length; index++) {
            const root = el[index].dataset.root
            const uiIndex = el[index].closest(`[data-uiid=${root}]`).dataset.index
            const evaluate = Alpine.evaluate.bind(Alpine.evaluate, el[index])
            dropdown.render({
                elem: el[index]
                , data: outputDataName ? layui_dropdown_translate_data(Alpine.evaluate(el[index], `${outputDataName}[${uiIndex}]`)) : staticDatas
                , align: menuAlign
                , click: function (data) {
                    if (!inputName) return
                    const xInputExp = alpinejs_init_iterator_value(el[index], inputName, evaluate)
                    evaluate(`${xInputExp} = value`, { scope: { value: data.id }})
                }
            })
        }
    }else{
        const evaluate = Alpine.evaluate.bind(Alpine.evaluate, el[0])
        dropdown.render({
            elem: el[0]
            , data: outputDataName ? layui_dropdown_translate_data(Alpine.evaluate(el[0], `${outputDataName}`)) : staticDatas
            , align: menuAlign
            , click: function (data) {
                if (inputName){
                    const xInputExp = alpinejs_init_iterator_value(el[0], inputName, evaluate)
                    evaluate(`${xInputExp} = value`, { scope: { value: data.id }})
                }
            }
        })
    }
}
function layui_nav_init(uiid){
    const el = document.querySelectorAll(`[data-uiid="${uiid}"]`);
    if (!el.length) return;
    const element = layui.element;
    for (let index = 0; index < el.length; index++) {
        element.render('tab', el[index])
    }
}

function layui_pagination_init(uiid, total, pageSize, limits, theme, inputName, Alpine){
    const el = document.querySelectorAll(`[data-uiid="${uiid}"]`);
    if (!el.length) return;
    const laypage = layui.laypage;
    if (el.length > 1){
        let isArr = false
        if (inputName && inputName.match(/\[-1\]$/)){
            isArr = true
            inputName = inputName.replace(/\[-1\]$/, '')
        }
        for(let index = 0; index < el.length; index++){
            const curr = el[index].dataset?.value || 1;
            const uiIndex = el[index].dataset.index
            laypage.render({
                elem: el[index]
                ,count: total
                ,curr: curr
                ,limit: pageSize
                ,limits: limits
                ,theme: ` ${theme}`
                ,layout: [ 'page', 'count', 'limit', 'skip']
                ,jump: (obj, first)=>{
                    if (!inputName) return
                    if (isArr) {
                        Alpine.evaluate(el[index], `${inputName}[${uiIndex}] = value`, { scope: { value: obj.curr }})
                    }else{
                        Alpine.evaluate(el[index], `${inputName} = value`, { scope: { value: obj.curr }})
                    }
                }
            });
        }
    }else{
        if (inputName && inputName.match(/\[-1\]$/)){
            inputName = inputName.replace(/\[-1\]$/, '')
        }
        const curr = el[0].dataset?.value || 1;
        laypage.render({
            elem: el[0]
            ,count: total
            ,curr: curr
            ,limit: pageSize
            ,limits: limits
            ,theme: ` ${theme}`
            ,layout: [ 'page', 'count', 'limit', 'skip']
            ,jump: (obj, first)=>{
                if(inputName){
                    Alpine.evaluate(el[0], `${inputName} = value`, { scope: { value: obj.curr }})
                }
            }
        });
    }
}
