import type { Directive, DirectiveBinding } from "vue";
const systemModifiers = ["ctrl", "shift", "alt", "meta"];
const keyNames: Record<string, any> = {
  esc: "escape",
  space: " ",
  up: "arrow-up",
  left: "arrow-left",
  right: "arrow-right",
  down: "arrow-down",
  delete: "backspace"
};
const modifierGuards: Record<string, any> = {
  stop: (e:any) => e.stopPropagation(),
  prevent: (e:any) => e.preventDefault(),
  self: (e:any) => e.target !== e.currentTarget,
  ctrl: (e:any) => !e.ctrlKey,
  shift: (e:any) => !e.shiftKey,
  alt: (e:any) => !e.altKey,
  meta: (e:any) => !e.metaKey,
  left: (e:any) => "button" in e && e.button !== 0,
  middle: (e:any) => "button" in e && e.button !== 1,
  right: (e:any) => "button" in e && e.button !== 2,
  exact: (e:any, modifiers: Array<string>) => systemModifiers.some((m) => e[`${m}Key`] && !modifiers.includes(m))
}
const cacheStringFunction = (fn: any) => {
  const cache = Object.create(null);
  return (str: string) => {
    const hit = cache[str];
    return hit || (cache[str] = fn(str));
  };
};
const hyphenateRE = /\B([A-Z])/g;
const hyphenate = cacheStringFunction(
  (str: string) => str.replace(hyphenateRE, "-$1").toLowerCase()
);
const getKeyModifiers  = (modifiers: Array<string>): Array<string> => {
  const keys: any = []
  for(const modifier of modifiers){
    if (modifier.match(/\d+ms/)) continue
    if (["stop", "prevent", "self", "ctrl", "shift", "alt", "meta", "left", "middle", "right", "exact", "passive","once","capture","immediate"].includes(modifier)) {
      continue;
    }
    keys.push(modifier)
  }
  return keys
}
const withModifiers = (fn: any, modifiers: Array<string>) => {
  const cache = fn._withMods || (fn._withMods = {});
  const cacheKey = modifiers.join(".");
  const keyModifiers = getKeyModifiers(modifiers)

  return cache[cacheKey] || (cache[cacheKey] = (event: any) => {
    const realEvent = event.detail[0]

    for (const modifier of modifiers) {
      const guard: any = modifierGuards[modifier];
      if (guard && guard(realEvent, modifiers)) return;
    }
    // 捕获标志时，只在捕获阶段执行
    if (modifiers.includes('capture')) {
      if (fn._capture) return
      fn._capture = true
    }
    
    if (realEvent.key && keyModifiers.length > 0) {
      const eventKey = hyphenate(realEvent.key);
      
      if (keyModifiers.some( (k) => k === eventKey || keyNames[k] === eventKey )) {
        return fn(...event.detail);
      }else{
        return
      }
    }
    return fn(...event.detail);
  });
};
function debounce(context: any, func: any, wait: number, immediate = false) {
  let timer: any = null;

  return function(...args: any) { 
    clearTimeout(timer);
    if(immediate && timer === null) {
      func.apply(context,args);
      timer = 0;
      return;
    }
    timer = setTimeout(function() { 
      func.apply(context,args);
      timer = null;
    }, wait);
  }
}
function throttle(context: any, func: any, wait: number) {
  let timer: any = null;
  let timeStamp: number = Date.now();

  return function(...args: any) { 
    if(Date.now() - timeStamp >= wait){
      timeStamp = Date.now();
      timer = setTimeout(function(){ 
        func.apply(context, args);
      },wait);
    }
  }
}
/**
 * 组件事件当有修饰符时，用v-event
 */
export const event: Directive = {
  mounted(el: HTMLElement, binding: DirectiveBinding) {
    if (!binding.arg) return
    const eventType = binding.arg
    const options:Record<string, boolean> = {}
    const listener = binding.value
    let wrapListener = binding.value

    const modifiers = binding.modifiers
    const modifierKeys = Object.keys(modifiers)
    // console.log(modifiers);
    wrapListener = withModifiers(listener, modifierKeys)
    if (!wrapListener) return

    if (modifiers.once ) options.once = true;
    if (modifiers.passive ) options.passive = true;
    if (modifiers.capture ) options.capture = true;
    
    const delayStr = modifierKeys.find((item) => item.match(/\d+ms/))
    const delay = delayStr ? parseInt(delayStr.replace(/ms$/, '')) : 0
    
    if (modifiers.debounce) {
      wrapListener = debounce(el, wrapListener, delay, modifiers.immediate ? true : false)
    } if (modifiers.throttle) {
      wrapListener = throttle(el, wrapListener, delay)
    }

    el.addEventListener(eventType, wrapListener, options)
  }
}
