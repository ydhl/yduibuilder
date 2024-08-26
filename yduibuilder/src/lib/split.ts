import { Frame } from 'scenejs'
import _ from 'lodash'
import JQuery from 'jquery'
import MouseDownEvent = JQuery.MouseDownEvent
import MouseUpEvent = JQuery.MouseUpEvent
import MouseMoveEvent = JQuery.MouseMoveEvent

/**
 * 左右边栏的split按钮，拖动时改变边栏的宽度
 * @param target
 * @param splitCallback 包含spliting和splited 两个回调
 * @param direction X｜Y
 */
function split (target: string, splitCallback: Function | undefined = undefined, direction = 'X') {
  let downOrig = 0
  let isSpliting = false
  let startOrig = 0
  let clientXY = 'clientX'
  let _direction = 'X'
  let splitingCallback: any
  let splitedCallback: any

  clientXY = `client${direction}`
  _direction = direction
  const move = _.throttle((event: MouseMoveEvent) => {
    if (!isSpliting) return
    if (splitingCallback) {
      isSpliting = splitingCallback(event[clientXY] - downOrig + startOrig)
      // console.log(isSpliting)
    }
  }, 100)

  const up = (event: MouseUpEvent) => {
    if (isSpliting) {
      if (splitedCallback) splitedCallback()
      isSpliting = false
    }

    JQuery('body').off('mouseup', up)
    JQuery('body').off('mousemove', move)
  }
  const down = (event: MouseDownEvent) => {
    downOrig = event[clientXY]
    const frmae = new Frame(event.target.style.cssText)
    startOrig = parseFloat(frmae.get('transform', `translate${_direction}`)) || 0
    // console.log(downOrig, clientXY, _direction, startOrig)
    isSpliting = true

    if (splitCallback) {
      const callback = splitCallback(event)
      splitingCallback = callback.spliting
      splitedCallback = callback.splited

      JQuery('body').on('mouseup', up)
      JQuery('body').on('mousemove', move)
    }
  }
  JQuery('body').off('mousedown', target, down)
  JQuery('body').on('mousedown', target, down)
}
export default split
