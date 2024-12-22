<template>
    <div v-attr="attrs" :class="css" :style="style" data-root :data-index="iterateIndex" :data-value="myValue">
        <template v-if="isSplit">
            <button :class="btnCss" :style="btnStyle" type="button"
            @click="y.emit($el, 'y-click', $event, myValue)"
            @dblclick="y.emit($el, 'y-dblclick', $event, myValue)"
            @mousedown="y.emit($el, 'y-mousedown', $event, myValue)"
            @mouseup="y.emit($el, 'y-mouseup', $event, myValue)"
            @mouseover="y.emit($el, 'y-mouseover', $event, myValue)"
            @mouseout="y.emit($el, 'y-mouseout', $event, myValue)"
            @mousemove="y.emit($el, 'y-mousemove', $event, myValue)"
            @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue)"
            @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue)"
            @blur="y.emit($el, 'y-blur', $event, myValue)"
            @focus="y.emit($el, 'y-focus', $event, myValue)">
                <IconWraper :icon="icon" :iconPosition="iconPosition">
                    <span>{{btnTitle}}</span>
                </IconWraper>
            </button>
            <button role="button" type="button" :class="splitBtnCss" :style="btnStyle" data-bs-toggle="dropdown" aria-expanded="false"></button>
        </template>
        <template v-else>
            <button role="button" type="button" data-bs-toggle="dropdown" aria-expanded="false" :class="['dropdown-toggle', btnCss]" :style="btnStyle">
                <IconWraper :icon="icon" :iconPosition="iconPosition">
                    <span>{{syncTitle ? (myValueTitle || btnTitle) : btnTitle}}</span>
                </IconWraper>
            </button>
        </template>
        <div :class="dropdownMenuCss">
            <template v-for="(item, index) in items" :key="index">
                <h6 v-if="typeof item =='object' && item.type=='header'" class='dropdown-header'>{{item.name}}</h6>
                <div v-else-if="typeof item =='object' && item.type=='divider'" class="dropdown-divider"></div>
                <div v-else-if="typeof item =='object' && item.type=='text'" class="ps-3 pr-3 text-muted"><p>{{item.name}}</p></div>
                <a v-else href='javascript:;' :class="{'dropdown-item': true, 'bg-light': myValueIndex == index}"
                @click="updateValue(index, item);y.emit($el, 'y-click', $event, myValue)"
                @dblclick="y.emit($el, 'y-dblclick', $event, myValue, item)"
                @mousedown="y.emit($el, 'y-mousedown', $event, myValue, item)"
                @mouseup="y.emit($el, 'y-mouseup', $event, myValue, item)"
                @mouseover="y.emit($el, 'y-mouseover', $event, myValue, item)"
                @mouseout="y.emit($el, 'y-mouseout', $event, myValue, item)"
                @mousemove="y.emit($el, 'y-mousemove', $event, myValue, item)"
                @mouseenter="y.emit($el, 'y-mouseenter', $event, myValue, item)"
                @mouseleave="y.emit($el, 'y-mouseleave', $event, myValue, item)"
                @blur="y.emit($el, 'y-blur', $event, myValue, item)"
                @focus="y.emit($el, 'y-focus', $event, myValue, item)">{{valueList.getItemTitle(item)}}</a>
            </template>
        </div>
    </div>
</template>
<script lang="ts" setup>
import y from '@/lib/ydecloud'
import {ref, watch, onMounted,defineExpose} from 'vue'
import IconWraper from './IconWraper.vue';
import ValueList from './ValueList'

const { iterateIndex, isSplit, syncTitle, btnCss, btnStyle, btnTitle, splitBtnCss, dropdownMenuCss, icon, iconPosition,  attrs, css, style, items } = defineProps({
// 该组件被迭代时的索引
iterateIndex: Number,
// 父组件被迭代时的索引
parentIterateIndex: Number,
// icon 样式
icon: String,
// icon位置，left top right bottom
iconPosition: String,
attrs: Object,
syncTitle: Boolean,
isSplit: Boolean,
// 静态按钮文案
btnTitle: String,
css: {
    type: [Object , String]
},
dropdownMenuCss: {
    type: [Object , String]
},
btnCss: {
    type: [Object , String]
},
splitBtnCss: {
    type: [Object , String]
},
style: String,
btnStyle: String,
items:{
    required: true,
    type:[Array<string|number>, Array<{ [key: string]: string | number | boolean | undefined }>]
},
})
const model = defineModel<any>()
const myValue = ref<string>('')
const myValueTitle = ref<string>('')
const myValueIndex = ref<number>(-1)
const emit = defineEmits(['change'])
const valueList = new ValueList(items)
let needEmitChange = true

defineExpose({initModelFromXInput})
// 由x-input指令调用
function initModelFromXInput(n: any){
  model.value = n
}
watch(myValue, (n, old) => {
  model.value = n
  if (!needEmitChange) {
    needEmitChange = true
    return
  }
  emit('change', n, old)
})
function updateValue(index: number, item: string|number|{ [key: string]: string | number | boolean | undefined }) {
  myValue.value = valueList.getItemValue(index, item)
  myValueIndex.value = index
  myValueTitle.value = valueList.getItemTitle(item)
}
onMounted(() => {
  needEmitChange = model.value === undefined
  const defValue = !valueList.isEmpty(model.value) ? model.value : valueList.getDefaultValue()
  myValue.value = defValue || ''
  myValueIndex.value = valueList.getItemIndexByValue(defValue)
  if (myValueIndex.value !== -1){
    myValueTitle.value = valueList.getItemTitle(items[myValueIndex.value])
  }
})
</script>
  