import type { Directive, DirectiveBinding } from "vue";
declare type AttrValue = boolean | string | number;
declare type Attr = Record<string, AttrValue>;

function updateAttr(el: HTMLElement, oldAttrs:Attr | null, newAttrs: Attr){
  if (oldAttrs){
    for(const name in oldAttrs){
      el.removeAttribute(name)
    }
  }
  if (newAttrs){
    for(const name in newAttrs){
      el.setAttribute(name, newAttrs[name] as string)
    }
  }
}

export const attr: Directive = {
  mounted(el: HTMLElement, binding: DirectiveBinding<Attr>) {
    updateAttr(el, binding.oldValue, binding.value)
  },
  updated(el: HTMLElement, binding: DirectiveBinding) {
    updateAttr(el, binding.oldValue, binding.value)
  }
};
