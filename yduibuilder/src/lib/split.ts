import { Frame } from 'scenejs'
import JQuery from 'jquery'
import MouseDownEvent = JQuery.MouseDownEvent
import MouseUpEvent = JQuery.MouseUpEvent
import MouseMoveEvent = JQuery.MouseMoveEvent

let downX = 0
let isSpliting = false
let startX = 0
let splitingCallback: any
let splitedCallback: any

/**
 * 左右边栏的split按钮，拖动时改变边栏的宽度
 * @param target
 * @param splitStartCallback
 * @param splitingCallback
 * @param splitedCallback
 */
function split (target: string, splitCallback: Function | undefined = undefined) {
  const move = (event: MouseMoveEvent) => {
    if (!isSpliting) return
    if (splitingCallback) {
      isSpliting = splitingCallback(event.clientX - downX + startX)
      // console.log(isSpliting)
    }
  }
  const up = (event: MouseUpEvent) => {
    if (isSpliting) {
      if (splitedCallback) splitedCallback()
      isSpliting = false
    }

    JQuery('body').off('mouseup', up)
    JQuery('body').off('mousemove', move)
  }
  const down = (event: MouseDownEvent) => {
    downX = event.clientX
    const frmae = new Frame(event.target.style.cssText)
    startX = parseFloat(frmae.get('transform', 'translateX')) || 0
    // console.log(startX)
    isSpliting = true

    if (splitCallback) {
      const callback = splitCallback()
      splitingCallback = callback.spliting
      splitedCallback = callback.splited

      JQuery('body').on('mouseup', up)
      JQuery('body').on('mousemove', move)
    }
  }
  JQuery('body').on('mousedown', target, down)
}
export default split
