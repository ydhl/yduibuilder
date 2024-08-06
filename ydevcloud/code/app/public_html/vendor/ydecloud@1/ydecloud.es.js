
export default function (Alpine) {
    return {
        alpinejs_checked_name(el, values, valueName)
        {
            const name = alpinejs_get_input_data_name(el, valueName);
            const checkValue = Alpine.evaluate(el, name);
            return alpinejs_update_checked_name(values, checkValue);
        },
        alpinejs_get_value(el, valueName)
        {
            const name = alpinejs_get_input_data_name(el, valueName);
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
        alpinejs_set_value(el, valueName, value)
        {
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
            Alpine.evaluate(el, `${name} = ${typeof value=="string" ? '"'+value+'"' : value}`)
        }
    }
}
