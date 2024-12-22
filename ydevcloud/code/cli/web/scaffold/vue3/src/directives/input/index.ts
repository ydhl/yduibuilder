import type { Directive, DirectiveBinding } from "vue";

function findIndex(el: HTMLElement){
  const index: Array<number> = [];
  if (el.dataset?.index != undefined) {
      index.push(parseInt(el.dataset.index))
  }

  let parent: HTMLElement | null | undefined = el;
  do {
      if(parent.matches('[data-index]')){
          parent = parent.parentElement?.closest('[data-index]')
      }else{
          parent = parent.closest('[data-index]') as HTMLElement
      }
      if (parent && parent.dataset.index != undefined){
          index.push(parseInt(parent.dataset.index))
      }
  }while (parent)
  return index.reverse()
}
/**
 * 删除某个el的值，导致值列表顺序改变后，同步更新对应el上的data-realindex：
 * 跟el同一uiid（迭代出来绑定数据）删除的el后面的realindex前移
 * @param el 当前删除值的el
 * @returns 
 */
function adjustRealIndex(el: HTMLElement){
  const uiid = el.dataset.uiid
  if (!uiid) return
  
  if (el.dataset.realindex === undefined) return
  const fromIndex = parseInt(el.dataset.realindex)
  const all = document.querySelectorAll(`[data-uiid=${uiid}]`)

  all.forEach((item: any) => {
    if (item.dataset.realindex === undefined) return
    const myIndex = parseInt(item.dataset.realindex)
    if (myIndex > fromIndex) {
      item.setAttribute('data-realindex', String(myIndex - 1))
    }
  })
}
/**
 * 该指令用于两种绑定的model是数组的情况：
 * 1. 组件被迭代输出并且绑定了数组model
 * 2. 组件没有被循环，但是是单值组件，绑定了数组model
 * 
 * 用了v-input绑定的数据一定是数组
 * 
 * 其它情况直接使用v-model
 */
export const input: Directive = {
  mounted(el, binding: DirectiveBinding) {
    el._updated = false
    if (!binding.value){
      binding.value = []
      return
    }
    if (binding.value.length === 0){
      return
    }
    if (!el.__vueParentComponent?.exposed?.initModelFromXInput){
      return
    }

    const indexs = findIndex(el)
    if (indexs.length ===0) {// 未迭代但是绑定的是数组model
      el.__vueParentComponent.exposed.initModelFromXInput(binding.value)
      return
    }
    
    let value = binding.value
    let idx = 0 // 第几层
    let index // 迭代位置
    do {
      index = indexs[idx]
      // 如果有下级，初始化值，要不然后面value = 得到的value是null
      if (indexs.length > idx + 1) {
        if (value[index] === undefined || value[index] === null){ // 该节点没有，初始化
          value.push([])
        }else if(!Array.isArray(value[index])){
          value[index] = [value[index]]
        }
      }

      if (indexs.length == idx + 1) {// 遍历到最后一个要处理的
        el.setAttribute('data-realindex', index)
        el.__vueParentComponent.exposed.initModelFromXInput(value[index])
        break
      }
      // 继续往下遍历
      value = value[index]
      idx ++
    }while(idx < indexs.length)
  },
  /**
   * 该自定义指令的更新需要元素或里面的子元素dom有变化
   */
  updated(el, binding: DirectiveBinding) {
    if (el._updated) return;
    el._updated = true;
    const indexs = findIndex(el)

    let currValue = undefined
    if(binding.modifiers.checkbox){// checkbox组件
      currValue = []
      const allChecked = el.querySelectorAll("[type='checkbox']:checked")
      for (let i = 0; i < allChecked.length; i++) {  
        currValue.push(allChecked[i].value);  
      }
    } else if(binding.modifiers.select){
      const selectedOptions = el.querySelector('select')?.selectedOptions || []
      currValue = []
      for (let i = 0; i < selectedOptions.length; i++) {  
        currValue.push(selectedOptions[i].value);  
      }
    } else if(binding.modifiers.file){
      const fileEl = el.querySelector('.input')
      const files = (fileEl as HTMLInputElement)?.files
      currValue = fileEl.multiple ? files : files?.[0]
    }else{// 单值情况
      currValue = el.dataset.value || el.querySelector(".input")?.value || el.value || undefined
    }
 
    if (indexs.length ===0) {// 未迭代但是绑定的是数组model
      if(currValue) {
        binding.value.splice(0)
        binding.value.push(currValue)
      }
      setTimeout(() => {
        el._updated = false;
      });
      return
    }

    let value = binding.value

    let idx = 0 //第几层
    let index // 迭代位置
    const realIndex = el.dataset.realindex ? parseInt(el.dataset.realindex) : null //实际在数组中的位置
    do {
      // 触发的元素是尾部元素，如果他有realIndex则用之，realIndex用于存放值在数组中的实际位置
      // 由于元素可能设置了值，也可能没设置值，所以他的迭代位置index不一定等于值的实际位置realIndex
      index = indexs.length == idx + 1 && realIndex != null ? realIndex : indexs[idx]
      // debugger
      // 如果有下级，初始化值，要不然后面value = 得到的value是null
      if (indexs.length > idx + 1) {
        if (value[index] === undefined || value[index] === null){ // 该节点没有，初始化
          value.push([])
        }else if(!Array.isArray(value[index])){ // 该节点不是数组，改成数组，比如多维循环，但是绑定的数组是一维的情况
          value[index] = [value[index]]
        }
      }

      if (indexs.length == idx + 1) {// 遍历到最后一个要处理的
        if(currValue === undefined || !String(currValue)){// 删除
          if (realIndex != null) {
            value.splice(realIndex, 1)
            adjustRealIndex(el)
            el.removeAttribute('data-realindex')
          }
          break;
        }

        if (realIndex !== null) {// 更新
          value[realIndex] = currValue
        }else{// 新增
          value.push(currValue)
          el.setAttribute('data-realindex', value.length - 1)
        }
        break
      }
      // 继续往下遍历
      value = value[index]
      idx ++
    }while(idx < indexs.length)

    setTimeout(() => {
      el._updated = false;
    });
  }
};
