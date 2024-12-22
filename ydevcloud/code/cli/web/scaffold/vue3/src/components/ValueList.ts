export default class ValueList {
    protected items: Array<string|number> | Array<{ [key: string]: string | number | boolean | undefined }>

    constructor (items: any) {
        this.items = items
    }

    /**
     * 
     * 获取数组项中的“名”：
     * 
     * 1）如果数组项是对象，则里面的name是名，没有name则取value，没有那么则JSON.stringify该数组项
     * 
     * 2）如果不是对象，则名就是数组项本身
     * 
     * @param item 数组项
     * @returns string
     */
    public getItemTitle(item: string|number|{ [key: string]: string | number | boolean | undefined }) {
        if (typeof item === 'object') {
            if (item.name !== undefined) return String(item.name)
            if (item.value !== undefined) return String(item.value)
            return JSON.stringify(item)
        }else{
            return String(item)
        }
    }

    /**
     * 
     * 获取数组项中的“值”：
     * 
     * 1）如果数组项是对象，则里面的value是值，没有value则取name，没有那么则用该数组项的索引
     * 
     * 2）如果不是对象，则值就是数组项本身
     * 
     * @param index 数组项的索引
     * @param item 数组项
     * @returns string
     */
    public getItemValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
        if (typeof item === 'object') {
            if (item.value !== undefined) return String(item.value)
            if (item.name !== undefined) return String(item.name)
            return String(index)
        }else{
            return String(item)
        }
    }
    public getItemIndexByValue(defValue: string|number) {
        return this.items.findIndex((item) => {
            const isObject = typeof item === 'object'
            if (isObject){
                return String(item.value) === String(defValue) || String(item.name) === String(defValue)
            }
            return String(item) === String(defValue)
        })
    }
    /**
     * 从输出对象值列表中找出默认选中的值；也就是列表中checked=true的值，适用于值列表只有一个值选中的情况
     * 非对象值输出列表，无法判断默认值
     * @returns string
     */
    public getDefaultValue(){
        const index = this.items.findIndex((item: any) => typeof item === 'object' && item.checked)
        if (index === -1) return ''
        const item: any = this.items[index]
        return this.getItemValue(index, item)
    }

    /**
     * 从输出对象值列表中找出所有默认选中的值；也就是列表中checked=true的值，适用于值列表有多选的情况
     * 非对象值输出列表，无法判断默认值
     * @returns Array<string>
     */
    public getDefaultValues(){
        if (this.items.length === 0 ) return []
        if (typeof this.items[0] !== 'object') return []

        const checkedValues: Array<string> = []
        for(let i=0; i < this.items.length; i++){
            const item: any = this.items[i]
            if (item.checked) checkedValues.push(this.getItemValue(i, item))
        }

        return checkedValues
    }
    public isEmptyObject (e: any) {
        if (!e) return true
        for (const t in e) {
            return !1
        }
        return !0
    }
    public isEmpty(value: any){
        if (value === undefined || value === null) return true
        if (Array.isArray(value) && value.length === 0) return true
        if (typeof value === 'object') return this.isEmptyObject(value)
        if (typeof value === 'string') return value.length === 0
        return false
    }
}
