import $ from 'jquery'

/**
 * uiTree 拖动的是ui组件树
 * ui 拖动的是设计器中的ui元素
 * uiItem 拖动的是ui面板中的元素
 */
export declare type UIDragType = 'uiTree' | 'ui' | 'uiItem' | 'unknown'

/**
 * 被拖动的元素, 为了和iframe中的uidrag共享，挂载在top.windows上
 */
// let uidragged: any = null
// let uiDragFromWhere: UIDragType = 'unknown'
let drgoverStartTime: number = 0
/**
 * 找出有dragable（可能是true可能是false）的dom
 * @param target
 */
function findDragableDom (target: any) {
  if (!$(target).attr('draggable')) {
    return $(target).parents('[draggable]').get(0)
  }
  return target
}
function findSubContainer (target: any) {
  if (!$(target).attr('data-placeInParent')) {
    return $(target).parents('[data-placeInParent]').get(0)
  }
  return target
}
function findDragPosition (target: any) {
  if ($(target).hasClass('ui') || $(target).parents('.ui').length > 0) {
    return 'ui'
  }
  if ($(target).hasClass('uitree') || $(target).parents('.uitree').length > 0) {
    return 'uiTree'
  }
  if ($(target).hasClass('ui-item') || $(target).parents('.ui-item').length > 0) {
    return 'uiItem'
  }
  return 'unknown'
}

function getUIDragged () {
  return (top.window as any).uidragged
}
function setUIDragged (el: any) {
  (top.window as any).uidragged = el
}
function getUIDragFromWhere () {
  return (top.window as any).uiDragFromWhere
}
function setUIDragFromWhere (type: UIDragType) {
  (top.window as any).uiDragFromWhere = type
}
/**
 * 处理设计器中的ui元素和UITree中的元素拖动
 * 当相关的拖动事件发生时，会同时通知workspace和uitree中注册的回调
 * @param option
 */
function uidrag (option: Record<any, any>) {
  const { target, start, over, enter, leave, drop, dragend } = option
  // console.log('--- uidrag ' + target)
  // 防止事件多次注册，先off掉
  $('body').off('dragstart', target)
  $('body').off('drag', target)
  $('body').off('dragend')
  $('body').off('dragover', target)
  $('body').off('dragenter', target)
  $('body').off('dragleave', target)
  $('body').off('drop', target)

  $('body').on('dragstart', target, (event: any) => {
    // console.log('--- dragstart')
    if (getUIDragged()) return // is draging
    const uiDragFromWhere = findDragPosition(event.target)
    setUIDragFromWhere(uiDragFromWhere)
    // console.log(uiDragFromWhere, event.target)
    if (uiDragFromWhere === 'unknown') return

    const uidragged = findDragableDom(event.target)
    uidragged.style.opacity = 0.35
    setUIDragged(uidragged)
    if (start) start(uiDragFromWhere, event)
  })

  $('body').on('drag', target, (event: any) => {
    // console.log('drag')
  })

  $('body').on('dragend', (event: any) => {
    // console.log('dragend')
    const uiDragFromWhere = getUIDragFromWhere()
    if (dragend) dragend(uiDragFromWhere, event)
    const uidragged = getUIDragged()
    if (uidragged) uidragged.style.opacity = ''
  })

  $('body').on('dragover', target, (event: any) => {
    // console.log('dragover')
    event.preventDefault()
    const now = (new Date()).getTime()
    if (now < drgoverStartTime + 500) {
      return
    }
    drgoverStartTime = now
    // dragoverUIItem: 设计器中的ui元素，如果是拖动uitree时需要取得对应的ui元素
    // dragoverEl: 拖动悬浮的目标对象，对于一个ui dragable下面的子元素时，需要获取到上层指定的ui元素，对于uitree，需要取得具体的uitree-item
    // ==dragoverEl和dragoverUIItem的区别主要体现在拖动outline tree和组件库时不同，如果拖动的是设计区域内容的ui，那么两者是一样的==
    // dragoverEl的rect位置和当前的鼠标位置决定拖动的元素应该放于over元素的什么位置：left top right bottom in
    // innerDragoverEl 是dragoverUIItem中的内部可以放置子组件的元素，比如像Card，其head和foot支持方其他组件，那么通过innerDragoverEl
    //   来区分是悬浮于内部那个位置，内部能够放置子组件的元素上通过data-placeInParent=""来指定
    //   所悬浮的子组件的归组名，见UIMeta.subitems说明

    let dragoverUIItem: any, dragoverEl: any
    const dragOverTo = findDragPosition(event.target)
    dragoverUIItem = dragoverEl = findDragableDom(event.target)
    const innerDragoverEl = findSubContainer(event.target)
    // console.log(innerDragoverEl)
    // console.log(dragoverEl)
    const isInDragPlacement = $(event.target).hasClass('uitree-placement')
    if (isInDragPlacement) return // uitree拖动时，已经在placement提示区域上了

    if (dragOverTo === 'uiTree') { // uitree拖动的情况
      dragoverEl = $(dragoverUIItem).find('.uitree-item').get(0)
      const pageid = $(dragoverUIItem).attr('data-pageid') as string
      const iframe = window.document.getElementById(pageid) as any
      // console.log(pageid, $(dragoverUIItem).attr('data-uiid'))
      if (!iframe) return
      dragoverUIItem = $('#' + $(dragoverUIItem).attr('data-uiid'), iframe.contentWindow.document).get(0) // 设计器中的ui item元素
    }
    // console.log(dragoverUIItem)
    if (!dragoverUIItem) return
    const uidragged = getUIDragged()
    if ($(dragoverUIItem).attr('id') === $(uidragged).attr('id')) return
    const isContainer = $(dragoverUIItem).attr('data-isContainer') === 'true'
    const uiType = $(dragoverUIItem).attr('data-type')?.toLowerCase()

    const rect = innerDragoverEl ? innerDragoverEl.getBoundingClientRect() : dragoverEl.getBoundingClientRect()
    // console.log(dragoverEl)
    const currX = (event as DragEvent).clientX
    const currY = (event as DragEvent).clientY
    let blood = 0.5
    if (isContainer) {
      // 对于容器，dragover时可以表示要放在该容器中（子元素），也可能表示放在容器的四周（兄弟元素），所以容器元素留25%的四周空间
      // 在这25%的四周空间中，则表示放在容器的四周；剩下的部分表示拖入容器中
      // 对应page，则只能拖入，不存在四周问题
      blood = 0.25
    }
    if (uiType === 'page' || innerDragoverEl) blood = 0
    // 被拖动的元素悬浮于目标元素的那个位置, 每次dragover都重新计算
    const dragOverPosi = { left: false, right: false, top: false, bottom: false }
    if (currX >= rect.x && currX <= rect.x + rect.width * blood) dragOverPosi.left = true
    if (currX >= rect.x + rect.width * (1 - blood) && currX <= rect.x + rect.width) dragOverPosi.right = true
    if (currY >= rect.y && currY <= rect.y + rect.height * blood) dragOverPosi.top = true
    if (currY >= rect.y + rect.height * (1 - blood) && currY <= rect.y + rect.height) dragOverPosi.bottom = true
    const uiDragFromWhere = getUIDragFromWhere()
    if (uiDragFromWhere === 'uiTree') { // 拖动uitree时，实际上只有top in bottom
      dragOverPosi.left = dragOverPosi.top
      dragOverPosi.right = dragOverPosi.bottom
    }

    // console.log(dragOverPosi)
    const overElDisplay = window.getComputedStyle(dragoverUIItem).getPropertyValue('display')
    const overElIsInline = /inline/.test(overElDisplay)
    const container = $(dragoverUIItem).parents('.uicontainer').get(0)
    const containerElDisplay = container ? window.getComputedStyle(container).getPropertyValue('display') : ''
    const containerFlexDirection = container ? window.getComputedStyle(container).getPropertyValue('flex-direction') : ''

    let placement: any = null
    if (/flex/.test(containerElDisplay)) { // 容器是flex布局
      if (/column/.test(containerFlexDirection)) {
        if (dragOverPosi.top) placement = 'top'
        if (dragOverPosi.bottom) placement = 'bottom'
      } else {
        if (dragOverPosi.left) placement = 'left'
        if (dragOverPosi.right) placement = 'right'
      }
    } else { // 容器是传统的block布局
      if (overElIsInline) {
        if (dragOverPosi.left) placement = 'left'
        if (dragOverPosi.right) placement = 'right'
      } else {
        if (dragOverPosi.top) placement = 'top'
        if (dragOverPosi.bottom) placement = 'bottom'
      }
    }
    // console.log(dragoverUIItem, dragOverPosi, isContainer, placement || 'in')
    // console.log(placement)
    const placeInParent = $(innerDragoverEl).attr('data-placeInParent')
    // console.log('dragover placeInParent: ' + placeInParent)
    if (over) over(uiDragFromWhere, dragoverUIItem, dragOverPosi, isContainer, placement || (isContainer ? 'in' : ''), uidragged, placeInParent)
  })

  $('body').on('dragenter', target, (event: any) => {
    // console.log('dragenter')
    event.preventDefault()
    const uiDragFromWhere = getUIDragFromWhere()
    if (enter) enter(uiDragFromWhere, findDragableDom(event.target))
  })

  $('body').on('dragleave', target, (event: any) => {
    // console.log('dragleave')
    event.preventDefault()
    const uiDragFromWhere = getUIDragFromWhere()
    if (leave) leave(uiDragFromWhere, findDragableDom(event.target))
  })

  $('body').on('drop', target, (event: any) => {
    // console.log('drop')
    event.preventDefault()
    const uidragged = getUIDragged()
    if (!uidragged) return
    let dropTarget = findDragableDom(event.target)
    const uiDrop = findDragPosition(event.target)
    const innerDragoverEl = findSubContainer(event.target)

    if (uiDrop === 'unknown' || uiDrop === 'uiItem') {
      if (uidragged) uidragged.style.opacity = ''
      setUIDragged(null)
      return
    }

    if (uiDrop === 'uiTree') { // 拖入uitree的情况
      if (!$(dropTarget).hasClass('uitree')) {
        dropTarget = $(dropTarget).parents('.uitree').get(0)
      }
    } else if (uiDrop === 'ui') {
      if (!$(dropTarget).hasClass('ui')) {
        dropTarget = $(dropTarget).parents('.ui').get(0)
      }
    }

    const placeInParent = $(innerDragoverEl).attr('data-placeInParent')
    // console.log($(event.target).html(), $(innerDragoverEl).html(), placeInParent)
    const uiDragFromWhere = getUIDragFromWhere()
    if (drop) drop(uiDragFromWhere, uidragged, dropTarget, placeInParent)
    if (uidragged) uidragged.style.opacity = ''
    setUIDragged(null)
  })
}

export default uidrag
