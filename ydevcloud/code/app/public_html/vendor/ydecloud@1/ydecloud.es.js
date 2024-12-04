
export default function (Alpine) {
    const isEmptyObject = function (e) {
        if (!e) return true
        // eslint-disable-next-line no-unreachable-loop
        for (const t in e) {
            return !1
        }
        return !0
    }
    return {
        /**
         * 是否是undefined，null，空数组或空对象
         * @param el
         * @param valueName
         * @return {boolean|*}
         */
        alpinejs_is_empty(value){
            if (value === undefined || value === null) return true
            if (Array.isArray(value) && value.length === 0) return true
            if (typeof value === 'object') return isEmptyObject(value)
            return false
        },
        alpinejs_checked_name(el, values, valueName)
        {
            const name = alpinejs_get_input_data_name(el, valueName);
            const checkValue = Alpine.evaluate(el, name);
            return alpinejs_update_checked_name(values, checkValue);
        },
        /**
         *
         * @param el
         * @param valueName
         * @param ignoreSelf  boolean false 则表示获取上层ui绑定的数据
         * @return {*|undefined}
         */
        alpinejs_get_value(el, valueName, ignoreSelf=false)
        {
            const name = alpinejs_get_input_data_name(el, valueName, ignoreSelf);
            const value = Alpine.evaluate(el, name);
            return value != undefined ? value : undefined;
        },
        alpinejs_in_array(el, valueName, check)
        {
            const name = alpinejs_get_input_data_name(el, valueName);
            const value = Alpine.evaluate(el, name) || undefined;

            if (value == undefined) return false
            if (value instanceof Array) return value.findIndex(item => item == check) !== -1
            if (value instanceof String) return value.split(',').indexOf(check) !== -1
            return value == check
        },
        alpinejs_get_index(el, prefix = '', suffix = '')
        {
            if (!el) return `${prefix}${suffix}`;
            const index = alpinejs_find_index(el);
            if (!index) return `${prefix}${suffix}`;
            return prefix + index.join("-") + suffix;
        },
        /**
         *
         * @param el
         * @param valueName
         * @param value
         * @param ignoreSelf boolean 某些情况下，需要对当前被迭代的元素的上一级数据进行整体赋值，但是调用是在当前元素上，这时传入true，比如rangeinput的x-init中
         */
        alpinejs_set_value(el, valueName, value, ignoreSelf=false)
        {
            let name = valueName;
            if (valueName.match(/\[-1\]/)) {
                const index = alpinejs_find_index(el)
                if (ignoreSelf) {
                    index.pop()
                }
                name = valueName.replace(/\[-1\]$/, '')
                if (index.length > 0) {
                    for (const idx of index) {
                        name += '[' + idx + ']';
                        Alpine.evaluate(el, `if (${name} == undefined) ${name} = []`);
                    }
                }
            }
            Alpine.evaluate(el, `${name} = value`, { scope: { value: typeof value === 'object' ? JSON.parse(JSON.stringify(value)) : value } });
            // Alpine.evaluate(el, `${name} = ${typeof value=="string" ? '"'+value+'"' : value}`)
        },
        alpinejs_set_value_from_list(el, valueName, items, isArray){
            const value = []
            if (!items) return

            let name = valueName;
            if (valueName.match(/\[-1\]/)) {
                const index = alpinejs_find_index(el)
                name = valueName.replace(/\[-1\]$/, '')
                if (index.length > 0) {
                    for (const idx of index) {
                        name += '[' + idx + ']';
                        Alpine.evaluate(el, `if (${name} == undefined) ${name} = []`);
                    }
                }
            }
            for (let i=0; i<items.length; i++){
                const item = items[i]
                if (typeof item !== 'object') {
                    continue;
                }
                if (item.checked) value.push(item.value || item.name || i)
            }

            Alpine.evaluate(el, `${name} = value`, { scope: { value: isArray ? value : value?.[0] } });
        }
    }
}
