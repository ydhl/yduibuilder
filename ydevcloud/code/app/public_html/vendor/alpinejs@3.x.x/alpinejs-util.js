/**
 * values 值列表有两种格式
 * 1. case 1 一维数组[....]
 * 2. case 2 二维数组[[...],[...]...]
 *
 * 在case 1时checkValue就是要比较的值，从一维数组中找该值
 * 在case 2时checkValue是个一维数组，数组中的每个值就需要从一维中去比较
 *
 * checkValue 有case 1 单值，case 2 [值,值]
 *
 *
 * @param values array 要查询的数据源
 * @param checkValue array | fixed 被检查的数据,可能是数组（比如select 多选），可能是单个值
 */
function alpinejs_update_checked_name(values, checkValue){
    if(!checkValue) return ''

    const compare = (item, itemIndex, value) => {
        if (typeof item === 'object'){
            if (item.hasOwnProperty('value') || item.hasOwnProperty('name')) {
                if (item.value == value || item.name == value) return true
            }else{ // 没有value，name的对象则用对象在数组中的索引来比较
                if (itemIndex == value) return true
            }
        }else if(item === value){
            return true
        }
        return false
    }
    const foundItems = []
    for(const itemIndex in values){
        const item = values[itemIndex]
        if (typeof checkValue === 'array'){
            for(const _value of checkValue){
                if (compare(item, itemIndex, _value)) foundItems.push(item)
            }
        }else{
            if (compare(item, itemIndex, checkValue)) foundItems.push(item)
        }
    }

    const rst = [];
    if(foundItems){
        for(const item of foundItems){
            if(typeof item === 'object'){
                rst.push(item.name || JSON.stringify(item));
            }else{
                rst.push(item);
            }
        }
    }
    return rst.join(',')
}
function alpinejs_find_index(el){
    let index = [];
    if (el.dataset?.index) {
        index.push(el.dataset.index)
    }
    let parent = el;
    do {
        if(parent.matches('[data-index]')){
            parent = parent.parentElement.closest('[data-index]')
        }else{
            parent = parent.closest('[data-index]')
        }
        if (parent){
            index.push(parent.dataset.index)
        }
    }while (parent)
    return index.reverse()
}
function alpinejs_get_input_data_name(el, valueName){
    if (valueName.match(/\[-1\]/)) {
        const index = alpinejs_find_index(el)
        valueName = valueName.replace(/\[-1\]$/, '')
        if (index.length > 0) {
            for (const idx of index) {
                valueName += '?.[' + idx + ']';
            }
        }
    }
    return valueName
}
function alpinejs_init_iterator_value(el, expression, evaluate, isArrayData=false){
    if (!expression.match(/\[-1\]/)) return expression;
    // 处理动态数组
    let index = alpinejs_find_index(el);
    let tmp = expression.replace(/\[-1\]/, '')
    // 绑定了数组类型输入数据，但界面并没有被迭代输出；只初始化为数组。
    if (index.length <= 0) {
        evaluate(`if(!${tmp}) ${tmp} = []`);
        return tmp;
    }
    evaluate(`if(!${tmp}) ${tmp} = []`);
    const last = index.length - 1;
    for(const i in index){
        const idx = index[i]
        tmp += `[${idx}]`
        if (last == i){
            // 最后一个数据项是数组的（checkbox, select的multiple才是数组）其他定义为undefined
            evaluate(`if(!${tmp}) ${tmp} = ${!isArrayData ? 'undefined' : '[]'}`);
        }else{
            evaluate(`if(!${tmp}) ${tmp} = []`);
        }
    }
    return tmp;
}

/**
 * 把value的值初始化给绑定的数据
 * @param el
 * @param expression
 * @param evaluate
 * @param effect
 * @param evaluateLater
 */
function alpinejs_init_bind_value(uitype, el, expression, evaluate, effect, evaluateLater, isArrayData){
    const isInput = uitype == 'textarea' || uitype === 'input' || uitype === 'rangeinput';

    const xInputExp = alpinejs_init_iterator_value(el, expression, evaluate, isArrayData)
    if (!xInputExp) return

    if (isInput) { // 直接体现值类
        // 绑定的输入值没有数据的情况下，同步输出到输入
        const inputEl = el.querySelector('.input') || el;
        const value = inputEl.value;
        if (value){
            const code = `
if (${xInputExp} instanceof String) {
    if(!${xInputExp}.length)  ${xInputExp} = "${value}";
}else if (!${xInputExp}){
    ${xInputExp} = "${value}";
}
`;
            evaluate(code);
        }

        const inputValueWatch = evaluateLater(xInputExp);
        // 绑定的输入数据改变后同步到ui上
        let firstTime = true;
        effect(() => {
            inputValueWatch(inputValue => {
                // 初始绑定输入没有内容的情况下不同步到UI上
                if(firstTime && !inputValue) {
                    return;
                }
                firstTime = false
                const input = el.querySelector('.input')
                if(input)input.value = inputValue || ''
            });
        })
        return
    }

    // 其他把data-default初始化给exp
    const isArray = expression.match(/\[-1\]/)
    const defaultValue = [];
    el.querySelectorAll('[data-default]:not([data-default=""])')?.forEach((item) => {
        if (item.dataset.default) defaultValue.push(item.dataset.default)
    })
    const value = isArray ? "defaultValue" : `"${defaultValue}"`;
    const code = `
if (${xInputExp} instanceof Array) {
    if(${xInputExp}.length==0) ${xInputExp} = ${value};
}else if (${xInputExp} instanceof String) {
    if(${xInputExp}.length == 0) ${xInputExp} = ${value};
}else if (${xInputExp} instanceof Object) {
    if(Object.keys(${xInputExp}).length === 0) ${xInputExp} = ${value};
}else if (!${xInputExp}){
    ${xInputExp} = ${value};
}
`;
    if (isArray){
        evaluate(code, { scope: { defaultValue } });
    }else{
        evaluate(code);
    }

}
function alpinejs_init_directive(Alpine){
    // 输出key-value属性
    Alpine.directive('keyvalue', (el, { expression }, { effect, evaluate }) => {
        effect(() => {
            const keyValue = evaluate(expression)
            if (!keyValue || (typeof keyValue) !== 'object') return
            for(const key in keyValue){
                if (keyValue[key] === undefined || keyValue[key] === null){
                    el.removeAttribute(key)
                }else{
                    el.setAttribute(key, keyValue[key])
                }
            }
        });
    });
    // 把object或者array输出到style属性
    Alpine.directive('style', (el, { expression }, { effect, evaluate }) => {
        effect(() => {
            const keyValue = evaluate(expression)
            if (!keyValue) return
            let style = []
            if (Object.prototype.toString.call(keyValue) === '[object Object]'){
                for(const key in keyValue){
                    style.push(`${key}: ${keyValue[key]}`)
                }
            }else if (Object.prototype.toString.call(keyValue) === '[object Array]') {
                style = keyValue
            }else {
                return
            }
            const old = el.getAttribute('style');
            el.setAttribute('style', old ? old + ';' + style.join(';') : style.join(';'))
        });
    });
    // 把array输出到class属性
    Alpine.directive('class', (el, { expression }, { effect, evaluate }) => {
        effect(() => {
            const keyValue = evaluate(expression)
            if (!keyValue) return
            if (Object.prototype.toString.call(keyValue) !== '[object Array]') {
                return
            }
            const old = el.getAttribute('class');
            el.setAttribute('class', old ? old + ' ' + keyValue.join(' ') : keyValue.join(' '))
        });
    });
    //{expression, modifiers,original,type,value},{Alpine, cleanup, effect, evaluate,evaluateLater}
    Alpine.directive('input', (el, { expression, modifiers }, { effect, evaluate, Alpine, evaluateLater }) => {
        const uiType = el.dataset.type;
        if (!uiType) return;
        let isArrayData = false
        if (el.hasAttribute('data-index')){// ui被循环输出时
            isArrayData = !!((uiType === 'select' && el.querySelector("[multiple]")) || uiType === 'checkbox')
        }else{// 绑定了数组数据
            isArrayData = !!expression.match(/[-1]/)
        }

        Alpine.nextTick(() => {
            alpinejs_init_bind_value(uiType, el, expression, evaluate, effect, evaluateLater, isArrayData)
        })
        const execExp = (exp, value) => {
            if (!exp) return
            if (isArrayData){
                evaluate(`${exp} = [value]`, { scope: { value } });
            }else{
                evaluate(`${exp} = value`, { scope: { value } });
            }
        }

        if (['checkbox', 'radio'].indexOf(uiType) !== -1){
            Alpine.bind(el, { '@click'(event) {
                const exp = alpinejs_init_iterator_value(event.target, expression, evaluate, isArrayData);
                if (!exp) return;
                const hasInput = el.querySelector('input[type]');
                let eventTarget = event.target;

                if (hasInput && eventTarget.tagName != 'INPUT') return; // 如果有input标签，则只处理input元素上的click；有些ui没有用input元素
                if(!hasInput) eventTarget = eventTarget.closest(`[role='${uiType}']`)
                const uiid = eventTarget?.dataset?.uiid

                if (!uiid) return;
                let values = isArrayData ? [] : '';
                if (isArrayData){// 多值
                    el.querySelectorAll(`[data-uiid='${uiid}']`).forEach((el) => {
                        if(hasInput){
                            if (el.checked) values.push(el.value);
                        }else{
                            if (el.dataset.checked) values.push(el.dataset.value);
                        }
                    })
                }else{// 单值，就用触发事件EL的值
                    if(hasInput){
                        values = eventTarget.checked ? eventTarget.value : '';
                    }else{
                        values = eventTarget.dataset.checked ? eventTarget.dataset.value : '';
                    }
                }
                evaluate(`${exp} = values`, { scope: { values } });
            }})
        }else if (['input', 'textarea', 'rangeinput'].indexOf(uiType) !== -1){
            const inputEl = el.querySelector('.input') || el
            const subtype = inputEl.getAttribute('type')?.toLowerCase()
            if (['color', 'date', 'range'].indexOf(subtype) !== -1){
                Alpine.bind(inputEl, { '@input'(event) {
                    const exp = alpinejs_init_iterator_value(event.target, expression, evaluate, isArrayData)
                    execExp(exp, event.target.value)
                }})
            }else{
                Alpine.bind(inputEl, { '@keyup'(event) {
                    const exp = alpinejs_init_iterator_value(event.target, expression, evaluate, isArrayData)
                    execExp(exp, event.target.value)
                }})
            }
        }else if ('select' === uiType){
            Alpine.bind(el.querySelector('.input'), { '@change'(event) {
                const exp = alpinejs_init_iterator_value(event.target, expression, evaluate)
                if (!exp) return
                const selectedValues = Array.from(event.target.options).filter(option => option.selected).map(option=>option.value);

                if (isArrayData){
                    evaluate(`${exp} = selectedValues`, { scope: { selectedValues }});
                }else{
                    evaluate(`${exp} = "${selectedValues?.[0]}" ? "${selectedValues?.[0]}" : undefined`);
                }
            }})
        }else if ('file' === uiType){
            Alpine.bind(el, { '@change'(event) {
                const exp = alpinejs_init_iterator_value(event.target, expression, evaluate, isArrayData);
                const isArray = expression.match(/\[-1\]/)
                execExp(exp, isArray ? Array.from(event.target.files) : event.target.files?.[0]);
            }})
        }else{
            // 其他迭代类元素
            Alpine.bind(el, { '@click'(event) {
                const eventTarget = event.target.closest('[data-value]');
                if (!eventTarget) return;

                const exp = alpinejs_init_iterator_value(event.target, expression, evaluate, isArrayData)
                execExp(exp, eventTarget.dataset?.value)
            }})
        }
    });
}
function alpinejs_input_keyup(page, el, uiid, inputName){
    const value = el.value;
    const suffix = inputName.match(/[-1]/) ? '[-1]' : ''
    page.alpinejs_set_value(el,`${uiid}_wordCount${suffix}`, value?.length||'');
    if (value?.length>0){
        page.alpinejs_set_value(el, `${uiid}_clearButtonVisible${suffix}`, true);
    }else{
        page.alpinejs_set_value(el, `${uiid}_clearButtonVisible${suffix}`, false);
    }
}
function alpinejs_input_clear(page, el, uiid, inputName){
    page.alpinejs_set_value(el, inputName, "");
    const suffix = inputName.match(/[-1]/) ? '[-1]' : ''
    if(page.alpinejs_get_value(el, `${uiid}_wordCount${suffix}`) != undefined) page.alpinejs_set_value(el, `${uiid}_wordCount${suffix}`, 0);
    if(page.alpinejs_get_value(el, `${uiid}_clearButtonVisible${suffix}`) != undefined) page.alpinejs_set_value(el, `${uiid}_clearButtonVisible${suffix}`, false);
}

function alpinejs_pagination_pages(pageObj, el, totalPage, outputData, inputData){
    const pages = [];
    const maxPage = outputData || totalPage;
    let startPage = inputData || 1;
    startPage = Math.max(startPage - 2, 1);
    const endPage = Math.min(startPage + 9, maxPage);
    if (endPage - startPage < 10){
        startPage = Math.max(endPage - 9, 1)
    }
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i)
    }
    // console.log(pages)
    return pages;
}
