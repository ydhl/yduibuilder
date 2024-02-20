<template>
  <template v-if="pageIsLoaded">
    <template v-if="isPopup">
      <div class="popup-design-tip" v-if="isEmptyPopup">
        <div class="list-group w-50">
          <div class="list-group-item disabled">{{t('page.pleaseChoosePopupType')}}</div>
          <a href="javascript:;" class="list-group-item list-group-item-action" @click="addPopup('toast')">{{t('page.toast')}}</a>
          <a href="javascript:;" class="list-group-item list-group-item-action" @click="addPopup('alert')">{{t('page.alert')}}</a>
          <a href="javascript:;" class="list-group-item list-group-item-action" @click="addPopup('confirm')">{{t('page.confirm')}}</a>
          <a href="javascript:;" class="list-group-item list-group-item-action" @click="addPopup('prompt')">{{t('page.prompt')}}</a>
          <a href="javascript:;" class="list-group-item list-group-item-action" @click="addPopup('custom')">{{t('page.custom')}}</a>
        </div>
      </div>
      <div class="popup-background" :style="popPlacementStyle">
        <template v-if="!isEmptyPopup">
          <UIBase :uiconfig="currPage" :pageid="currPage.meta.id"></UIBase>
        </template>
      </div>
      <div class="popup-mask" :style="backgroundImage">
      </div>
    </template>
    <template v-else>
      <UIBase :uiconfig="currPage" :pageid="currPage.meta.id"></UIBase>
      <div id="ui-events" v-if="showEventPanel">
        <UIEventBadge :uiconfig="currPage" :pageid="currPage.meta.id"></UIEventBadge>
      </div>
    </template>
  </template>
  <div v-if="!pageIsLoaded" class="d-flex bg-light justify-content-center align-items-center w-100" style="padding:300px;">
    {{t('page.loading')}}
  </div>
  <!--拖动提示-->
  <div id="drop-placeholder" v-if="dragoverUIItemId" :style="dragoverHolderStyle"></div>
  <div style="display:none">
    <Upload v-model="image" width="50px" @click.prevent.stop height="50px" :project-id="projectId"></Upload>
  </div>

  <div class="full-backdrop" v-if="backdropVisible"></div>
</template>

<script lang="ts">
/* eslint-disable */
import { computed, onMounted, onUnmounted, onUpdated, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useRoute } from 'vue-router'
import { useStore } from 'vuex'
import UIBase from '@/components/ui/UIBase.vue'
import keyevent from '@/lib/keyevent'
import uidrag from '@/lib/uidrag'
import baseUIDefines from '@/components/ui/define'
import ydhl from '@/lib/ydhl'
import UIEventBadge from '@/components/ui/UIEventBadge.vue'
import { YDJSStatic } from '@/lib/ydjs'
import { Boot, ISelectMenu, IButtonMenu } from '@wangeditor/editor'
import Upload from '@/components/common/Upload.vue'
import UIExport from '@/components/sidebar/UIExport.vue'

declare const YDJS: YDJSStatic
declare const ports: any
declare const $: any
declare const bootstrap: any
declare let globalEditor: any
class WangEditorImageMenu implements ISelectMenu {
  public title;
  public iconSvg;
  public tag;
  public uploadLabel;
  public selectLabel;
  public width;
  constructor (title: string, uploadLabel, selectLabel) {
    this.title = title
    this.uploadLabel = uploadLabel
    this.iconSvg = '<svg viewBox="0 0 1024 1024"><path d="M959.877 128l0.123 0.123v767.775l-0.123 0.122H64.102l-0.122-0.122V128.123l0.122-0.123h895.775zM960 64H64C28.795 64 0 92.795 0 128v768c0 35.205 28.795 64 64 64h896c35.205 0 64-28.795 64-64V128c0-35.205-28.795-64-64-64zM832 288.01c0 53.023-42.988 96.01-96.01 96.01s-96.01-42.987-96.01-96.01S682.967 192 735.99 192 832 234.988 832 288.01zM896 832H128V704l224.01-384 256 320h64l224.01-192z"></path></svg>'
    this.selectLabel = selectLabel
    this.tag = 'select'
    this.width = 60
  }

  getValue (editor) {
    return 'upload'
  }

  isActive (editor) {
    return false // or true
  }

  isDisabled (editor) {
    return false // or true
  }

  exec (editor, value) {
    const el = document.getElementById(value)
    const event = document.createEvent('HTMLEvents')
    event.initEvent('click', false, true)
    event.preventDefault()
    if (el) el.dispatchEvent(event)
    globalEditor = editor
    // $("#test1").trigger('click', )
    // editor.insertText(value) // value 即 this.getValue(editor) 的返回值
  }

  getOptions (editor) {
    const options = [
      { value: 'upload', text: this.uploadLabel },
      { value: 'select', text: this.selectLabel }
    ]
    return options
  }
}
class WangEditorQuitMenu implements IButtonMenu {
  public title;
  public iconSvg;
  public tag;
  public cb;
  constructor (title: string, cb: any) {
    this.title = title
    this.iconSvg = '<svg t="1664593643151" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="3797" width="200" height="200"><path d="M209.92 220.95644445c11.49155555 0 22.18666667-5.68888889 28.44444445-15.24622223 5.12-7.73688889 11.03644445-15.01866667 17.74933334-21.73155555 25.94133333-25.94133333 60.07466667-40.16355555 96.36977776-40.16355556L744.10666667 143.81511111c36.29511111 0 70.42844445 14.22222222 96.36977777 40.16355556S880.64 244.05333334 880.63999999 280.34844445L880.64 744.10666667c0 36.29511111-14.22222222 70.42844445-40.16355555 96.36977778S780.40177778 880.64 744.10666667 880.64l-391.50933334 0c-36.29511111 0-70.42844445-14.22222222-96.36977778-40.16355555-6.71288889-6.71288889-12.74311111-14.10844445-17.86311111-21.84533334-6.25777778-9.55733333-16.95288889-15.36000001-28.44444444-15.36-27.19288889 0-43.46311111 30.26488889-28.44444445 52.90666666 36.75022222 55.75111111 99.78311111 92.61511111 171.12177778 92.61511112L744.10666667 948.79288889c112.64 0 204.8-92.16 204.8-204.8l0-463.75822222c0-112.64-92.16-204.8-204.8-204.8l-391.50933334-1e-8c-71.22488889 0-134.25777778 36.864-171.008 92.50133334-15.01866667 22.75555555 1.13777778 53.02044445 28.33066667 53.02044445z" p-id="3798"></path><path d="M163.84 477.75288889L628.39466667 477.75288889c20.02488889 0 36.40888889 16.384 36.40888888 36.40888889s-16.384 36.40888889-36.40888888 36.40888889L163.84 550.57066667l79.30311111 81.57866666c14.10844445 14.22222222 14.10844445 37.20533333 0 51.42755556-14.22222222 14.10844445-37.20533333 14.10844445-51.42755556 0l-134.94044444-138.80888889c-7.39555555-7.39555555-11.264-17.63555555-10.58133333-28.10311111-0.11377778-1.70666667-0.11377778-3.29955555 0-5.00622222-0.68266667-10.46755555 3.18577778-20.70755555 10.58133333-28.10311112L191.71555555 344.74666667c14.22222222-14.10844445 37.20533333-14.10844445 51.42755556 0 14.10844445 14.22222222 14.10844445 37.20533333 0 51.42755555l-79.30311111 81.57866667z" p-id="3799"></path></svg>'
    this.tag = 'button'
    this.cb = cb
  }

  getValue (editor) {
    return 'quit'
  }

  isActive (editor) {
    return false // or true
  }

  isDisabled (editor) {
    return false // or true
  }

  exec (editor, value) {
    this.cb (value)
    globalEditor = null
  }
}
export default {
  name: 'Page',
  components: { UIExport, Upload, UIEventBadge, UIBase },
  setup (props: any, context: any) {
    const store = useStore()
    const route = useRoute()
    const currPage = computed(() => store.state.page.uiconfig)
    const project = computed(() => store.state.page.project)
    const pageIsLoaded = ref(false)
    const isPopup = computed(() => currPage.value?.pageType === 'popup')
    const isEmptyPopup = computed( () => {
      return isPopup.value && (!currPage.value.items || currPage.value.items.length===0)
    })
    const inlineEditItemId = computed(() => store.state.page.inlineEditItemId)
    const selectedUIItemId = computed(() => store.state.page.selectedUIItemId)
    const selectedUIItem = computed(() => {
      if (!selectedUIItemId.value) return null
      const { uiConfig } = store.getters.getUIItemInPage(selectedUIItemId.value, selectedPageId.value)
      return uiConfig
    })
    const dragoverUIItemId = computed(() => store.state.page.dragoverUIItemId)
    const dragoverPlacement = computed(() => store.state.page.dragoverPlacement)
    const dragoverInParent = computed(() => store.state.page.dragoverInParent)
    const selectedPageId = computed(() => store.state.page.uiconfig?.meta?.id)
    const showEventPanel = computed(() => store.state.page.showEventPanel)
    const backdropVisible = computed(() => store.state.page.backdropVisible)
    const { t } = useI18n()
    const popPlacementStyle = computed(() => {
      // 弹窗时第一个元素只会是Modal, 预览时通过page的布局定位其位置，实际代码web通过layer来实现弹窗
      const rootUI = currPage.value?.items?.[0]
      const placements  = rootUI?.meta?.custom?.position || ['center', 'center']
      let showBackdrop = rootUI?.meta?.custom?.backdrop
      showBackdrop = showBackdrop === undefined || showBackdrop !== 'no' ? true : false
      const items = {
        top: 'start',
        center: 'center',
        bottom: 'end'
      }
      const justify = {
        left: 'start',
        center: 'center',
        right: 'end'
      }
      return `align-items:${items[placements[1]]};justify-content:${justify[placements[0]]};${(showBackdrop ? 'background-color:rgba(0,0,0,0.5)' : '')}`
    })
    const projectId = computed(() => store.state.page.project.id)

    const image = computed({
      get () {
        return {}
      },
      set (v: any) {
        // { url, id, name }
        globalEditor.dangerouslyInsertHtml(`<img src="${v.url}">`)
      }
    })
    let checkUpdated

    Boot.registerMenu({
      key: 'imageMenu',
      factory () {
        return new WangEditorImageMenu(t('ui.image'), t('common.upload'), t('common.selectFile'))
      }
    })
    Boot.registerMenu({
      key: 'quitMenu',
      factory () {
        return new WangEditorQuitMenu(t('common.quit'), () => {
          store.commit('updatePageState', { selectedUIItemId: '' })
        })
      }
    })

    const deleteItem = () => {
      // 如果当前处于某个元素双击后的内部编辑状态
      // console.log($(event.target))
      if (inlineEditItemId.value || !selectedUIItemId.value) return
      // key事件是绑定到document上的，event.target是document，当某些能响应事件的元素，比如按钮，event.target是自己
      // if (!selectedUIItemId.value || ($(event.target).find('#' + selectedUIItemId.value).length === 0 && $(event.target).attr('id') !== selectedUIItemId.value)) return
      // console.log('test')
      postMessage({ type: 'deleteItem', data: { ids: [selectedUIItemId.value] }, pageId: selectedPageId.value })
    }
    const copyItem = () => {
      if (inlineEditItemId.value || !selectedUIItemId.value) return
      postMessage({ type: 'copyItem', data: { ids: [selectedUIItemId.value] }, pageId: selectedPageId.value })
    }
    const cutItem = () => {
      if (inlineEditItemId.value || !selectedUIItemId.value) return
      postMessage({ type: 'cutItem', data: { ids: [selectedUIItemId.value] }, pageId: selectedPageId.value })
    }
    const pasteItem = () => {
      if (inlineEditItemId.value || !selectedUIItemId.value) return
      postMessage({ type: 'pasteItem', data: { ids: [selectedUIItemId.value] }, pageId: selectedPageId.value })
    }
    const postMessage = (msg) => {
      // 这时ports是WorkspacePanel中的onMessage回调
      if (ports.parent) ports.parent(msg)
    }
    const refreshFontFace = () => {
      const node = document.getElementById('custom-font-face')
      if (node){
        node.remove()
      }
      const fontFace:any = []
      for (const face of currPage.value?.meta.custom?.fontFace || []) {
        fontFace.push(`@font-face{
          font-family: "${face.uuid}";
          src: url('${ydhl.api}download?uuid=${face.uuid}') format('${face.type}')
          }`)
      }
      $(window.document.head).append(`<style id="custom-font-face">${fontFace.join('')}</style>`)
    }
    const onMessage = (data) => {
      // console.log('on page message', data)
      // console.log(event)
      if (!data) return
      if (data.type === 'state') {
        store.replaceState(data.state)
        // console.log(store.state)
        const project = data.state.page.project
        $(window.document.head).append(`<link href="${ydhl.api}vendor/uibuilder.css" rel="stylesheet">`)
        $(window.document.head).append(`<link href="${ydhl.api}vendor/${project.ui}@${project.ui_version}/index.css" rel="stylesheet">`)
        $(window.document.head).append(`<link href="${ydhl.api}upload/project/${project.id}/iconfont/iconfont.css" rel="stylesheet">`)
        refreshFontFace()
      } else if (data.type === 'updatePageState' || data.type === 'clearDragoverState' || data.type === 'switchEventShow') {
        store.commit(data.type, data.payload)
        refreshFontFace()
      } else if (data.type === 'deleteItem') {
        deleteItem()
      } else if (data.type === 'copyItem') {
        copyItem()
      } else if (data.type === 'cutItem') {
        cutItem()
      } else if (data.type === 'pasteItem') {
        pasteItem()
      }
    }
    const uiMouseEnter = (event: any) => {
      let el = $(event.target)
      if (!el.hasClass('ui')) el = el.parents('.ui')
      if (el.length === 0) return

      postMessage({ type: 'update', data: { hoverUIItemId: el.attr('id') } })
      el.addClass('hover')
    }
    const uiMouseLeave = (event: any) => {
      let el = $(event.target)
      if (!el.hasClass('ui')) el = el.parents('.ui')
      if (el.length === 0) return

      postMessage({ type: 'update', data: { hoverUIItemId: '' } })
      el.removeClass('hover')
    }
    const mousemove = (event: any) => {
      postMessage({ type: 'mouseover', data: { clientX: event.clientX, clientY: event.clientY } })
    }
    const mouseup = (event: any) => {
      postMessage({ type: 'mouseup' })
    }
    const uiClick = (event: any) => {
      uiChange($(event.target))
    }
    const uiChange = (el: any) => {
      if (!el.hasClass('ui')) el = el.parents('.ui')
      if (el.length === 0) {
        // 如果没有选择ui元素 重置选择的元素，如果当前有内部编辑元素，则也退出（UIBase.ts中watch selectedUIItemId），另外一个重置的地方是page.vue

        // 如果处于富文本编辑，那么只有点击富文本上的退出才退出
        if (selectedUIItem.value?.type === 'RichText' && selectedUIItem.value?.meta.id === inlineEditItemId.value) return
        postMessage({ type: 'update', data: { selectedUIItemId: '' } })
        return
      }
      // 更换当前选择的元素，如果当前元素处于内部编辑，则退出
      postMessage({ type: 'update', data: { selectedUIItemId: el.attr('id') } })
    }
    const rightClick = (event: any) => {
      if (inlineEditItemId.value) return
      if(event.button===2){
        event.returnValue = false
        event.stopPropagation()
        event.preventDefault()
        uiChange($(event.target))
        postMessage({ type: 'contextMenu', data: { x: event.pageX, y: event.pageY } })
        return false
      }
    }

    onMounted(() => {
      //该页面是效果展示页面，但是和uibuilder共同使用index.html，所以要把uibuilder使用的相关css和script移出
      const needRemoveds = $('[data-use="uibuilder"]')
      for (const needRemoved of needRemoveds) {
        $(needRemoved).remove()
      }
      // 在WorkspacePanel 上注册自己的事件回调
      const parentWindow: any = parent
      parentWindow.ports[route.query.uuid as string] = onMessage
      // 通知workspace自己准备好了，让其把事件回调向自己注册
      parent.postMessage({ type: 'loaded', pageId: route.query.uuid }, document.location.origin)

      $('body').on('mouseover', uiMouseEnter)
      $('body').on('mouseout', uiMouseLeave)
      $('body').on('mousemove', mousemove)
      $('body').on('mouseup', mouseup)
      $('body').on('click', uiClick)
      // $('body').on('mousedown', rightClick)
      $('body').on('contextmenu', rightClick)

      const {userAgent} = navigator
      const isMac = userAgent.includes('Macintosh')
      const modKey = (isMac ? 'meta' : 'ctrl') // ⌘
      keyevent.keydown('backspace', (keyEvent) => {
        // console.log('page backspace')
        if (inlineEditItemId.value || !selectedUIItemId.value) return
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        deleteItem()
      })
      keyevent.keydown('delete', (keyEvent) => {
        // console.log('page backspace')
        if (inlineEditItemId.value || !selectedUIItemId.value) return
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        deleteItem()
      })
      keyevent.keydown([modKey, 'c'], (keyEvent) => {
        if (inlineEditItemId.value) return
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        copyItem()
      })
      keyevent.keydown([modKey, 'x'], (keyEvent) => {
        if (inlineEditItemId.value) return
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        cutItem()
      })
      keyevent.keydown([modKey, 'v'], (keyEvent) => {
        if (inlineEditItemId.value) return
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        pasteItem()
      })
      keyevent.keydown([modKey, 's'], (keyEvent) => {
        keyEvent.preventDefault()
        keyEvent.cancelBubble = true // IE
        postMessage({ type: 'save', data: {} })
      })

      uidrag({
        target: '.ui,.subui',
        dragend: () => {
          // console.log('workspace dragend')
          postMessage({ type: 'update', data: { dragoverUIItemId: '', dragoverPlacement: '', dragoverInParent: '' } })
        },
        enter: (uiDragFromWhere, target: Element) => {
          if (target.classList.contains('uicontiner')) {
            return
          }
          // 当可拖动的元素进入可放置的目标，高亮目标节点
          if (target.classList.contains('ui')) {
            target.classList.add('dragover-ui')
          }
        },
        leave: (uiDragFromWhere, target: Element) => {
          if (target.classList.contains('ui')) {
            target.classList.remove('dragover-ui')
          }
        },
        /**
         * 悬浮在某个元素上面时，根据容器和元素的display属性，显示摆放提示符，提示
         * 当前拖动的元素应该可以放在悬浮元素的那一边：
         * 如果容器是flex column的，那么就只能放在上或者下
         * 其他容器时如果悬浮元素是block的（块元素）也只能放上和下，inline的流模型的，就只能放左右
         *
         * 悬浮在自己之上则忽略
         */
        over: (uiDragFromWhere, reference: any, dragOverPosi: Record<string, boolean>, isContainer: boolean, placement: string, uidragged, placeInParent) => {
          // console.log('page over')
          const uIItemId: any = $(reference).attr('id')
          // Tree拖入时不能拖入自己的子元素中
          if (uiDragFromWhere === 'uiTree') {
            const sourceId = $(uidragged).attr('data-uiid')
            if ($(`[data-uiid='${uIItemId}']`).parents(`[data-uiid='${sourceId}']`).length > 0) {
              return
            }
          }
          if (dragoverUIItemId.value === uIItemId && dragoverPlacement.value === placement && placeInParent == dragoverInParent.value) return
          // console.log('over postMessage ' + placement + '，' + placeInParent)
          postMessage({ type: 'update', data: { dragoverUIItemId: uIItemId, dragoverPlacement: placement, dragoverInParent: placeInParent } })
        },
        /**
         * drop处理
         * @param sourceEl 被拖动的元素
         * @param targetEl 目标元素
         */
        drop: (uiDragFromWhere, sourceEl: Element, targetEl: Element, placeInParent) => {
          // console.log(sourceEl)
          let sourceId = $(sourceEl).attr('id')
          const sourcePageId = $(sourceEl).attr('data-pageid')
          const targetId = $(targetEl).attr('id')
          const targetPageId = $(targetEl).attr('data-pageid')
          const placement = dragoverPlacement.value
          // 从组件边栏拖入
          if (uiDragFromWhere === 'uiItem') {
            const uuid: any = $(sourceEl).attr('data-uuid') || ''// 自定义ui组件
            const type: any = uuid ? 'UIComponent' : $(sourceEl).attr('data-type')
            // console.log({ sourceId, sourcePageId, targetId, targetPageId })
            const meta: any = {
              id: ydhl.uuid(5, 0, targetPageId),
              title: type,
              form: baseUIDefines[type]?.isInput ? {} : undefined,
              isContainer: baseUIDefines[type]?.isContainer || false,
            }

            if (uuid){
              // 不能自己包含自己
              if ($(`#${targetId}`).parents(`#${uuid}`).length > 0) {
                ydhl.alert(t("common.uicomponentNestTip"));
                return
              }
              ydhl.loading(t('common.pleaseWait')).then((dialogId) => {
                ydhl.post(`api/uicomponent/detail.json`,{ target_id: targetId, uuid, instance_uuid: meta.id, page_uuid: selectedPageId.value }, [], (rst) => {
                  ydhl.closeLoading(dialogId)
                  if (!rst || !rst.success){
                    postMessage({ type: 'alert', data: rst.msg || t('ui.error') })
                    return
                  }
                  meta.title = rst.data.meta.title
                  const item = rst.data
                  item.subPageId = uuid
                  const data = { type: 'UIComponent', items: [item], meta, placeInParent, pageId: targetPageId, placement, targetId }
                  postMessage({ type: 'addItem', data })
                }, 'json')
              })
            } else {
              postMessage({
                type: 'addItem',
                data: { type, meta, placeInParent, pageId: targetPageId, placement, targetId }
              })
            }
            return
          }
          // 从outline拖入
          if (uiDragFromWhere === 'uiTree') {
            sourceId = $(sourceEl).attr('data-uiid')
            // 不能拖入自己的子元素中
            if ($(`#${targetId}`).parents(`#${sourceId}`).length > 0) {
              postMessage({ type: 'update', data: { dragoverUIItemId: '', dragoverPlacement: '', dragoverInParent: '' } })
              return
            }
          }

          postMessage({ type: 'moveItem', data: { sourceId, sourcePageId, targetId, targetPageId, placeInParent } })
        }
      })
    })
    onUpdated(() => {
      if (checkUpdated) clearInterval(checkUpdated)
      checkUpdated = setInterval(() => {
        const clientHeight = document.querySelector(`#${currPage.value.meta.id}`)?.clientHeight || 0
        if (clientHeight > 50 && currPage.value) {
          // console.log(currPage.value.meta.id + ':' + clientHeight)
          postMessage({
            type: 'updatePageContentHeight',
            data: {
              pageId: currPage.value.meta.id,
              contentHeight: clientHeight
            }
          })
          clearInterval(checkUpdated)
        }
      }, 500)
      $(function() {
        pageIsLoaded.value = true
      })
    })

    const dragoverHolderStyle = computed(() => {
      if (!dragoverUIItemId.value) return
      if (dragoverPlacement.value === 'in') return enterPlaceholderStyle.value
      const dom = document.getElementById(dragoverUIItemId.value)
      if (!dom) return
      const rect = dom.getBoundingClientRect()
      const styles: any = ['position:absolute;top:0;left:0;border:2px solid #dc3545;']
      let x = rect.x
      let y = rect.y
      if (dragoverPlacement.value === 'left' || dragoverPlacement.value === 'right') {
        styles.push(`height: ${rect.height + 4}px;`) // 4是outline的宽度
        styles.push('width: 1px')
        if (dragoverPlacement.value === 'right') {
          x += rect.width
        } else {
          x -= 4
        }
        y -= 2
      } else {
        styles.push(`width: ${rect.width + 4}px;`)
        styles.push('height: 1px')
        if (dragoverPlacement.value === 'bottom') {
          y += rect.height
        } else {
          y -= 4
        }
        x -= 2
      }
      styles.push(`transform:translateX(${x}px) translateY(${y}px)`)
      // console.log(styles.join(';'))
      return styles.join(';')
    })
    const enterPlaceholderStyle = computed(() => {
      if (!dragoverUIItemId.value) return
      const dom = document.getElementById(dragoverUIItemId.value)
      if (!dom) return
      const rect = dom.getBoundingClientRect()
      const styles: any = ['position:absolute;top:0;left:0;pointer-events:none !important;border:4px solid #dc3545;']
      styles.push(`width: ${rect.width}px !important;`)
      styles.push(`height: ${rect.height}px !important;`)
      styles.push(`transform:translateX(${rect.x}px) translateY(${rect.y}px) !important`)
      return styles.join(';')
    })
    const backgroundImage = computed(() => {
      // 当设计对话框页面时，背景展示打开该对话框当原始页面截图
      const fromPageId = route.query.fromPageId
      if (!fromPageId) return ''
      return `background:url(${ydhl.api}api/screenshot?pageid=${fromPageId}) no-repeat;background-size: cover`
    })
    const addToastPopup = () => {
      const pageId = currPage.value.meta.id
      const text ={
        type: 'Text',
        id: ydhl.uuid(5, 0, pageId),
        meta: {
          value: 'This is Toast',
          css:{
            foregroundTheme: 'light'
          }
        }
      }

      const type = 'Modal'
      postMessage({
        type: 'addItem',
        data: {
          type: type,
          meta: {
            id: ydhl.uuid(5, 0, pageId),
            isContainer: true,
            custom:{
              headless: true,
              footless: true
            },
            css: {
              backgroundTheme: 'secondary',
              foregroundTheme: 'light',
              '-': 'move-handler' // 只真对web有用
            },
            title: type
          },
          items: [text],
          placement: 'in',
          pageId: currPage.value.meta.id,
          targetId: currPage.value.meta.id
        }
      })
    }
    const addModal = (items) => {
      const pageId = currPage.value.meta.id
      const type = 'Modal'
      postMessage({
        type: 'addItem',
        data: {
          type: type,
          meta: {
            id: ydhl.uuid(5, 0, pageId),
            isContainer: true,
            title: type
          },
          items,
          placement: 'in',
          pageId: currPage.value.meta.id,
          targetId: currPage.value.meta.id
        }
      })
    }
    const addConfirmPopup = () => {
      const pageId = currPage.value.meta.id
      addModal([{
        type: 'Text',
        placeInParent: 'head',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          value: 'This is head',
          type: 'h3'
        }
      },{
        type: 'Text',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          value: 'This is body',
        }
      },{
        type: 'Button',
        placeInParent: 'foot',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          type: 'button',
          title: 'OK',
          css: {
            backgroundTheme: 'primary'
          }
        }
      },{
        type: 'Button',
        placeInParent: 'foot',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          type: 'button',
          title: 'Cancel',
          css: {
            backgroundTheme: 'secondary'
          }
        }
      }])
    }
    const addPromptPopup = () => {
      const pageId = currPage.value.meta.id
      addModal([{
        type: 'Text',
        placeInParent: 'head',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          value: 'This is head',
          type: 'h3'
        }
      },{
        type: 'Input',
        meta: {
          title: 'Input',
          custom: {
            inputType: 'Textarea',
          },
          value: 'This is input',
          id: ydhl.uuid(5, 0, pageId)
        }
      },{
        type: 'Button',
        placeInParent: 'foot',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          type: 'button',
          title: 'OK',
          css: {
            backgroundTheme: 'primary'
          }
        }
      }])
    }
    const addAlertPopup = () => {
      const pageId = currPage.value.meta.id
      addModal([{
        type: 'Text',
        placeInParent: 'head',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          value: 'This is head',
          type: 'h3'
        }
      },{
        type: 'Text',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          value: 'This is body',
        }
      },{
        type: 'Button',
        placeInParent: 'foot',
        meta: {
          id: ydhl.uuid(5, 0, pageId),
          title: 'OK',
          type: 'button',
          css: {
            backgroundTheme: 'primary'
          }
        }
      }])
    }
    const addCustomPopup = () => {
      const pageId = currPage.value.meta.id
      const type = 'Modal'
      postMessage({
        type: 'addItem',
        data: {
          type: type,
          meta: {
            id: ydhl.uuid(5, 0, pageId),
            isContainer: true,
            custom:{
              headless: true,
              footless: true
            },
            title: type
          },
          items: [],
          placement: 'in',
          pageId: currPage.value.meta.id,
          targetId: currPage.value.meta.id
        }
      })
    }
    const addPopup = (type) => {
      if (type === 'toast'){
        addToastPopup()
        return
      }
      if (type === 'alert'){
        addAlertPopup()
        return
      }
      if (type === 'confirm'){
        addConfirmPopup()
        return
      }
      if (type === 'prompt'){
        addPromptPopup()
        return
      }
      if (type === 'custom'){
        addCustomPopup()
        return
      }
    }

    return {
      postMessage,
      addPopup,
      pageIsLoaded,
      backgroundImage,
      isEmptyPopup,
      isPopup,
      currPage,
      t,
      dragoverUIItemId,
      dragoverPlacement,
      dragoverHolderStyle,
      enterPlaceholderStyle,
      popPlacementStyle,
      showEventPanel,
      projectId,
      backdropVisible,
      image
    }
  }
}
</script>
<style type="text/css">
body, html{
  overflow: hidden;
}

.full-backdrop{
  opacity: 0.5;
  position: absolute;
  top: 0;
  left: 0px;
  z-index: 1040;
  right: 0;
  bottom: 0;
  background-color: #000;
}
#app{
  display: flex;
  align-items: stretch;
  align-content: stretch;
  min-height: 100vh;
}
/**第一个元素撑满**/
#app>div.ui{
  flex-grow: 1;
}
</style>
