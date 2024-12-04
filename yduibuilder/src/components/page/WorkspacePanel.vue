<template>
  <div class="workspace">
    <template v-if="currPage">
      <div class="workspace-header" ref="head" :style="headStyle">
        <div class="left-tip" v-show="leftHasMore"></div>
        <div class="header-scrollbar" ref="headScrollbar" @scroll.passive="headScroll($event, 'head')" @wheel.passive="headWheel($event, 'head')" >
          <div class="p-1"></div>
          <div class="d-flex align-items-center">
            <span class="badge bg-light text-secondary text-truncate" data-bs-toggle="tooltip" style="width: 80px" :title="project.framework">{{ project.kind }} {{ project.framework }}</span>
          </div>
          <div class="d-flex ms-1 align-items-center">
            <span class="badge bg-light text-secondary text-truncate" data-bs-toggle="tooltip" style="width: 100px" :title="ui + ' ' + uiVersion">{{ ui }} {{ uiVersion }} </span>
          </div>
          <div class="d-flex ms-1 align-items-center" v-if="func.name">
            <span class="badge bg-light text-secondary text-truncate" data-bs-toggle="tooltip" style="width: 100px" :title=" module.name +' / ' + func.name">{{ module.name }} / {{ func.name }}</span>
          </div>
          <div class="divider ms-3 me-1" />
          <template v-if="!isMobileKind">
            <div class="d-flex align-items-center">
              <div class="btn-group btn-group-sm" role="group">
                <button type="button" :class="{'btn btn-light': true, 'text-primary': simulateModel=='pc'}" @click="simulateModel='pc'" data-bs-toggle="tooltip" :title="t('common.devicePC')">
                  <i class="iconfont icon-pc" />
                </button>
                <button type="button" :class="{'btn btn-light': true, 'text-primary': simulateModel=='tablet'}" @click="simulateModel='tablet'" data-bs-toggle="tooltip" :title="t('common.deviceTablet')">
                  <i class="iconfont icon-tablet" />
                </button>
                <button type="button" :class="{'btn btn-light': true, 'text-primary': simulateModel=='portrait'}" @click="simulateModel='portrait'" data-bs-toggle="tooltip" :title="t('common.devicePortrait')">
                  <i class="iconfont icon-mobile" />
                </button>
              </div>
            </div>
            <div class="divider ms-1 me-1" />
          </template>
          <div class="d-flex align-items-center">
            <button class="btn btn-light btn-sm" title="+" @click="zoomin">
              <i class="iconfont icon-plus" />
            </button>
            <button class="btn btn-light btn-sm" style="width: 50px;" title="zoom" @click.stop.prevent="openZoomMenu($event)">
              {{parseInt(pageScale*100)}}%
            </button>
            <button class="btn btn-light btn-sm" title="-" @click="zoomout">
              <i class="iconfont icon-minus" />
            </button>
          </div>
          <div class="divider  ms-1 me-1" />
          <button class="btn btn-light btn-sm flex-shrink-0" @click="preview()">
            <i class="iconfont icon-run" />
            {{t('common.preview')}}
          </button>
          <button disabled class="btn btn-light btn-sm flex-shrink-0 disabled">
            <i class="iconfont icon-review" />
            {{t('common.discuss')}}
          </button>
          <button type="button" data-bs-toggle="tooltip" :title="t('common.eventShowDesc')" class="btn btn-light btn-sm flex-shrink-0" @click="switchEventShow = !switchEventShow">
            <i :class="{'iconfont': true, 'icon-switch-off': !switchEventShow, 'icon-switch-on text-primary': switchEventShow}" />
            {{t('common.eventShow')}}
          </button>
          <div class="p-1"></div>
        </div>
        <div class="right-tip" v-show="rightHasMore"></div>
      </div>
      <div class="cooperation" v-if="userList && userList.length > 0">
        <div class="cooperation-body">
          <div class="text-muted fs-7 me-2 cursor" @click="openCooperationTip">{{t('common.somebodyComeIn')}} <i class="iconfont icon-tips"></i>:</div>
          <div v-for="(user, index) in userList" :key="index" class="d-flex justify-content-center align-items-center me-3">
            <div class="rounded-circle me-1 cooperation-avatar" :style="`background-image: url(${user.avatar});`"></div>
            <span class="fs-7 text-truncate" style="width: 50px">{{user.name}}</span>
          </div>
        </div>
      </div>
    </template>
    <div class="workspace-body" :style="workspaceStyle" ref="workspaceBody">
      <div class="opened-pages" ref="pageTab" v-if="currPage">
        <div class="left-tip" v-show="pageLeftHasMore"></div>
        <div class="opened-pages-scrollbar" ref="pageTabScrollbar" @scroll.passive="headScroll($event, 'pageTab')" @wheel.passive="headWheel($event, 'pageTab')">
          <VueDraggableNext :list="openedPages" class="nav nav-tabs ps-5" tag="ul">
            <transition-group>
              <li class="nav-item" v-for="(openedPage, index) in openedPages" :key="index"  @contextmenu="contextMenuTab($event, openedPage)">
              <a :class="{'nav-link position-relative': true, 'active': openedPage.meta.id===currPage.meta.id}" href="javascript:void(0)"
                 @mouseenter="hoverPageId=openedPage.meta.id" @mouseleave="hoverPageId=''" @click="switchPage(openedPage)">
                <i style="position:absolute;left: 6px" :class="' fs-7 iconfont icon-'+openedPage.pageType.toLowerCase()"></i>
                <span :class="{'text-truncate': true, 'text-black fst-italic': pageSaved[openedPage.meta.id]===0, 'text-muted text-light': pageSaved[openedPage.meta.id]!==0}"
                      :title="openedPage.meta.title">{{ openedPage.meta.title || '&nbsp;' }}</span>
                <div style="position:absolute;right: 6px">
                  <template v-if="hoverPageId===openedPage.meta.id">
                    <ConfirmRemove @remove="closePage(openedPage.meta.id)"></ConfirmRemove>
                  </template>
                  <i class="iconfont fs-7 icon-point" v-if="pageSaved[openedPage.meta.id]===0 && hoverPageId!=openedPage.meta.id"></i>
                </div>
              </a>
              </li>
            </transition-group>
          </VueDraggableNext>
        </div>
        <div class="right-tip" v-show="pageRightHasMore"></div>
      </div>
      <div class="workspace-scrollbar">
        <template v-if="currPage">
          <PageDesign :uiconfig="currPage"/>
        </template>
        <template v-else>
          <template v-if="pageLoading">
            <div class="d-flex justify-content-center align-items-center h-100">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </template>
          <template v-else-if="currFunctionId">
            <button class="btn btn-sm btn-primary mb-3" type="button" @click="addPage()">{{t('common.addBlankPage')}}</button>
            <template v-for="(pageList, name) in pages" :key="name">
              <div class="fs-5 p-2 ps-0 fw-light">{{t("ui." + name)}}</div>
              <div class="d-flex align-items-center flex-wrap">
                <template v-if="pageList.length > 0">
                  <div class="card me-3 mb-3" style="width: 200px; height: 200px" v-for="(page, index) in pageList" :key="index">
                  <div class="card-header"><small>{{page.name}}</small></div>
                  <div class="card-body bgtransparent">
                    <div class="w-100 h-100" :style="`background-image:url(${api+'image?pageid='+page.id});background-size:100% auto`">
                    </div>
                  </div>
                  <div class="card-footer"><button class="btn btn-sm btn-primary" type="button" @click="addPage(page.id)">{{t('common.useThisUI')}}</button></div>
                </div>
                </template>
                <template v-else>
                  <div class="text-muted pt-3 pb-3">{{t('ui.empty')}}</div>
                </template>
              </div>
            </template>
          </template>
          <template v-else>
            <div class="d-flex justify-content-center align-items-center h-100">
              {{t('common.clickLeftModule')}}
            </div>
          </template>
        </template>
      </div>
      <ElementPath @contextMenu="contextMenuOnPath"></ElementPath>
    </div>
    <!--缩放菜单-->
    <div id="zoomMenu" class="dropdown-menu show" ref="zoomMenu" v-if="zoomMenuVisible" >
      <a href="javascript:void(0)" class="dropdown-item" @click="changeZoom(zoom)" :key="index" v-for="(zoom, index) in zoomLevel"><i class="iconfont icon-placeholder" />{{zoom*100}}%</a>
    </div>
  </div>
  <a id="openlink" target="_blank"></a>

  <div v-if="showContextMenu" class="context-menu-container" @click="showContextMenu=false">
    <div class="context-menu" ref="contextMenuDom" :style="`left:${contextLeft}px;top:${contextTop}px`">
      <div class="item" @click.stop="rightClickCloseSelf">{{t("common.close")}}</div>
      <div class="item" @click.stop="rightClickCloseAll" v-if="openedPages.length > 1">{{t("common.closeAll")}}</div>
      <div class="item" @click.stop="rightClickCloseUnmodified" v-if="hasModified">{{t("common.closeUnmodified")}}</div>
      <div class="item" @click.stop="rightClickCloseLeft" v-if="hasLeftTab">{{t("common.closeLeft")}}</div>
      <div class="item" @click.stop="rightClickCloseRight" v-if="hasRightTab">{{t("common.closeRight")}}</div>
      <div class="item" @click.stop="rightClickCloseOther" v-if="openedPages.length > 1">{{t("common.closeOther")}}</div>
      <div style="background-color: #cccccc;height: 1px"></div>
      <div class="item" @click.stop="rightClickCopyPage"> {{t('common.copy')}}</div>
      <div class="item" @click.stop="rightClickMovePage"> {{t('common.moveTo')}}</div>
      <div class="item" @click.stop="rightClickDeletePage">{{ t('page.deletePage') }}</div>
      <div class="item" @click.stop="rightClickPreviewTab"> {{t('common.preview')}}</div>
    </div>
  </div>

  <lay-layer v-model="movePageDlgVisible" :title="t('common.moveTo')" :shade="true" :area="['420px', '300px']" :btn="movePageButtons">
    <div class="p-2">
      <FunctionPicker :data="modules" :single="true"
                      @update="(m,f)=>{moveToFunc = f; moveToModule=m}"
                      :defualt-func-uuid="movePageFromFuncUuid"
                      :defualt-module-uuid="movePageFromModuleUuid"></FunctionPicker>
    </div>
  </lay-layer>
</template>
<script lang="ts" setup>
import InitUI from '@/components/Common'
import { ref, computed, watch, onMounted, nextTick, toRaw } from 'vue'
import $ from 'jquery'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import ElementPath from '@/components/page/ElementPath.vue'
import { useI18n } from 'vue-i18n'
import { pickStateFromDesign } from '@/store/page'
import PageDesign from '@/components/page/PageDesign.vue'
import { YDJSStatic } from '@/lib/ydjs'
import keyevent from '@/lib/keyevent'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import { useRouter } from 'vue-router'
import { VueDraggableNext } from 'vue-draggable-next'
import FunctionPicker from '@/components/common/FunctionPicker.vue'
declare const YDJS: YDJSStatic
declare const ports: any

const emit = defineEmits(['contextMenu'])

const workspaceStyle = ref('')
const headStyle = ref('')
const hoverPageId = ref('')
const leftHasMore = ref(false)
const rightHasMore = ref(false)
const movePageDlgVisible = ref(false)
const pageLeftHasMore = ref(false)
const pageRightHasMore = ref(false)
const zoomMenuVisible = ref(false)
const showContextMenu = ref(false)
const pageLoading = ref(true)
const contextMenuDom = ref()
const pages = ref<any>([])
const zoomLevel = ref([3, 2, 1.25, 1, 0.75, 0.5, 0.3])
const head = ref(null)
const headScrollbar = ref(null)
const pageTab = ref()
const pageTabScrollbar = ref()
const zoomMenu = ref()
const workspaceBody = ref()
const modules = ref<Array<any>>()
const contextMenuPosition = ref({ x: 0, y: 0 })
const store = useStore()
const router = useRouter()
let copyedItems: Record<string, any> = {}
const { t } = useI18n()

const movePageFromFuncUuid = ref('')
const movePageFromModuleUuid = ref('')
let movePageFromPageUuid = ''
const moveToFunc = ref<any>({})
const moveToModule = ref<any>({})
const rightClickOnPage = ref({})
let rightClickOnModule = null
let rightClickOnFunc = null

const leftSidebarWidth = computed(() => store.state.design.leftSidebarWidth)
const rightSidebarWidth = computed(() => store.state.design.rightSidebarWidth)
const rightSidebarIsOpen = computed(() => store.state.design.rightSidebarIsOpen)
const leftSidebars = computed(() => store.state.design.leftSidebars)
const currFunctionId = computed(() => store.state.design.function.id)
const simulateWidth = computed(() => store.state.design.simulateWidth)
const inlineEditItemId = computed(() => store.state.design.inlineEditItemId)
const pageScale = computed(() => store.state.design.scale)
const moduleId = computed(() => store.state.design.module.id)
const selectedPageId = computed(() => store.state.design?.page?.meta?.id)
const currPage = computed(() => store.state.design.page)
const versionId = computed(() => store.state.design.pageVersionId[currPage.value.meta.id])
const project = computed(() => store.state.design.project)
const module = computed(() => store.state.design.module)
const func = computed(() => store.state.design.function)
const userList = computed(() => store.state.design.userList)
const openedPages = computed(() => store.state.design.openedPages ? Object.values(store.state.design.openedPages) : [])
const pageSaved = computed(() => store.state.design.pageSaved || {})
const hasModified = computed(() => Object.values(store.state.design.pageSaved).some((item) => item === 0))
const hasLeftTab = computed(() => {
  const index = openedPages.value.findIndex((item) => item.meta.id === rightClickOnPage.value.meta.id)
  return index >= 1
})
const hasRightTab = computed(() => {
  const index = openedPages.value.findIndex((item) => item.meta.id === rightClickOnPage.value.meta.id)
  return index < openedPages.value.length - 1
})
const customColors = ref<Array<any>>(project.value.customColors || [])
const endKind = computed(() => store.state.design.endKind)
const isMobileKind = computed(() => endKind.value === 'mobile')
const { ui, uiVersion, selectedUIItem } = InitUI()
const simulateModel = computed({
  get () {
    return store.state.design.simulateModel || 'pc'
  },
  set (v) {
    store.commit('updateState', { simulateModel: v })
  }
})
const pageTitle = computed(() => {
  return project.value.name + '/' + module.value.name + '/' + func.value.name + '/' + currPage.value?.meta?.title + '【YDUIBuilder】'
})
const switchEventShow = computed({
  get () {
    return store.state.design.showEventPanel
  },
  set (v) {
    store.commit('switchEventShow', v)
  }
})

const changeZoom = (scale: number) => {
  store.commit('updatePageState', { scale })
  zoomMenuVisible.value = false
}
const zoomin = () => {
  let scale = pageScale.value + 0.1
  if (scale > 3) scale = 3
  changeZoom(scale)
}
const zoomout = () => {
  let scale = pageScale.value - 0.1
  if (scale < 0.1) scale = 0.1
  changeZoom(scale)
}
const openZoomMenu = (event: any) => {
  ydhl.togglePopper(zoomMenuVisible, event.target, zoomMenu, 'bottom')
}
/**
 * 根据左右边栏的打开尺寸，计算workdspace的宽度和head的宽度，如果
 * workspace的宽度小于了页面的宽度，则更新design.scale让页面进行缩放
 */
const computeStyle = () => {
  // debugger
  const rightPanelWidth = rightSidebarIsOpen.value ? rightSidebarWidth.value : 0
  const _leftSidebarWidth = leftSidebars.value.length > 0 ? leftSidebarWidth.value : 0
  const dom = document.getElementById('left-panel')
  const leftPanelWidth = dom ? dom.getBoundingClientRect().width || 0 : 0

  const leftWidth = _leftSidebarWidth + leftPanelWidth
  const pageWidth = simulateWidth.value // 模拟页面的宽度，0表示自适应的

  const _workspaceStyle: any = []
  _workspaceStyle.push(`left:${leftWidth}px`)
  _workspaceStyle.push(`right:${rightPanelWidth}px`)
  // _workspaceStyle.push(`min-height:${document.body.clientHeight}px`)
  // workspaceStyle.push(`transform:scale(${scale})`)
  // workspaceStyle.push(`width:${originWidth}px`)
  _workspaceStyle.push(`width: ${document.body.clientWidth - leftWidth - rightPanelWidth}px`)

  const _headStyle: any = []
  _headStyle.push(`left:${leftWidth}px`)
  _headStyle.push(`right:${rightSidebarWidth.value}px;`)
  _headStyle.push(`width:${document.body.clientWidth - leftWidth - rightSidebarWidth.value}px`)

  workspaceStyle.value = _workspaceStyle.join(';')
  headStyle.value = _headStyle.join(';')

  nextTick(() => {
    // 计算workspace的缩放比例
    const workspace = document.querySelector('.workspace-scrollbar')
    let newWidth = 0
    if (workspace) {
      const paddingRightOfWorkspace = ydhl.formatFloat(window.getComputedStyle(workspace).getPropertyValue('padding-right'))
      const paddingLeftOfWorkspace = ydhl.formatFloat(window.getComputedStyle(workspace).getPropertyValue('padding-left'))
      // debugger
      newWidth = workspace.getBoundingClientRect().width - paddingLeftOfWorkspace - paddingRightOfWorkspace
    } else {
      newWidth = document.body.clientWidth - leftWidth - rightPanelWidth - 100
    }
    const originWidth = pageWidth || (document.body.clientWidth - leftPanelWidth - 100) // 100是workspace body左右的间距
    const scale = Math.min(newWidth / originWidth, 1)
    // console.log(`${newWidth} / ${originWidth} ${scale}`)
    store.commit('updatePageState', { scale })
  })
}

const refreshCustomColor = (v: any) => {
  const node = document.getElementById('custom-color')
  if (node) {
    node.remove()
  }
  const style = [':root{']
  for (const customColor of v) {
    style.push(`--${customColor.uuid}: ${customColor.color};`)
    if (customColor.dark) {
      style.push(`--${customColor.uuid}-dark: ${customColor.dark};`)
    }
  }
  style.push('}')
  $(window.document.head).append(`<style id="custom-color">${style.join('\r\n')}</style>`)
}
const changeHeadMoreState = (type) => {
  if (!currPage.value) return // 当前没有页面的话，没有显示

  const left = type === 'head' ? leftHasMore : pageLeftHasMore
  const right = type === 'head' ? rightHasMore : pageRightHasMore
  const _headScrollbar = type === 'head' ? headScrollbar.value as any : pageTabScrollbar.value as any
  const _head = type === 'head' ? $(head.value as any) : $(pageTab.value as any)
  if (!_headScrollbar) return

  left.value = ($(_headScrollbar).scrollLeft() || 0) > 0
  right.value = ((_head.width() || 0) + ($(_headScrollbar).scrollLeft() || 0)) < _headScrollbar.scrollWidth
  // console.log($(this.$refs.head), $(this.$refs.headScrollbar).scrollLeft(), $(this.$refs.head).width(), this.$refs.headScrollbar.scrollWidth)
}
const headScroll = (event: any, type) => {
  const left = type === 'head' ? leftHasMore.value : pageLeftHasMore.value
  const right = type === 'head' ? rightHasMore.value : pageRightHasMore.value
  if (left || right) {
    event.stopPropagation()
    event.preventDefault()
    event.cancelBubble = true
  }
  changeHeadMoreState(type)
}
const headWheel = (event: any, type) => {
  const left = type === 'head' ? leftHasMore.value : pageLeftHasMore.value
  const right = type === 'head' ? rightHasMore.value : pageRightHasMore.value
  const _headScrollbar = type === 'head' ? $(headScrollbar.value as any) : $(pageTabScrollbar.value as any)
  if (left || right) {
    event.stopPropagation()
    event.preventDefault()
    event.cancelBubble = true
  }
  // console.log(event);
  // event.deltaY 滚动方向与距离
  const scrollLeft = _headScrollbar.scrollLeft() || 0
  _headScrollbar.scrollLeft((scrollLeft + event.deltaY))
}
const postMessage = (pageid, msg) => {
  // console.log(pageid, ports[pageid])
  msg.pageId = pageid
  if (pageid && ports?.[pageid]) {
    ports[pageid](msg)
  }
}
const copyItem = (ids, pageId) => {
  copyedItems = {}
  if (!ids) return
  for (const id of ids) {
    const { uiConfig } = store.getters.getUIItemInPage(id, pageId)
    copyedItems[id] = uiConfig
  }
}
const onMessage = (data) => {
  if (!data) return
  if (data.type === 'mouseup') {
    const rect = document.getElementById('wrapper' + selectedPageId.value)?.getBoundingClientRect()
    const xy = data.data.split('_')
    store.commit('updateState', { mouseupInFrame: `${xy[0] * pageScale.value + (rect?.left || 0)}_${xy[1] * pageScale.value + (rect?.top || 0)}` })
    return
  }
  if (data.type === 'updatePageContentHeight' && data.data) {
    store.commit('updatePageContentHeight', data.data)
    return
  }
  if (data.type === 'update' && data.data) {
    store.commit('updatePageState', data.data)
    return
  }
  if (data.type === 'mouseover' && data.data) {
    store.commit('updateState', { mouseXYInIframe: { x: data.data.clientX, y: data.data.clientY } })
    return
  }
  if (data.type === 'undo') {
    store.commit('undo', { pageId: selectedPageId.value })
    return
  }
  if (data.type === 'redo') {
    store.commit('redo', { pageId: selectedPageId.value })
    return
  }
  if (data.type === 'contextMenu' && data.data) {
    const rect = document.getElementById('wrapper' + selectedPageId.value)?.getBoundingClientRect()
    // console.log(rect?.left, rect?.top, data.data)
    emit('contextMenu', { x: data.data.x * pageScale.value + (rect?.left || 0), y: data.data.y * pageScale.value + (rect?.top || 0) })
    return
  }
  if (data.type === 'deleteItem' && data.pageId) {
    store.commit('deleteItem', { ids: data.data.ids, pageId: data.pageId })
    return
  }

  if (data.type === 'cutItem' && data.pageId) {
    copyItem(data.data.ids, data.pageId)
    store.commit('deleteItem', { ids: data.data.ids, pageId: data.pageId })
    return
  }
  if (data.type === 'copyItem' && data.pageId) {
    copyItem(data.data.ids, data.pageId)
    return
  }
  if (data.type === 'pasteItem' && data.pageId) {
    if (!data.data.ids || data.data.ids.length === 0) return
    const { uiConfig } = store.getters.getUIItemInPage(data.data.ids[0], data.pageId)
    if (!uiConfig) return
    // console.log(copyedItems)
    for (const copyId in copyedItems) {
      ydhl.postJson('api/copy/ui.json', { page_uuid: data.pageId, uiconfig: copyedItems[copyId] }).then((rst: any) => {
        if (!rst.success) {
          ydhl.alert(rst?.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        const pasteItem = rst.data
        store.commit('addItem', {
          type: pasteItem.type,
          meta: pasteItem.meta,
          items: pasteItem.items,
          placeInParent: '',
          pageId: data.pageId,
          placement: uiConfig.meta.isContainer && uiConfig.type !== 'Table' ? 'in' : 'bottom',
          targetId: data.data.ids[0]
        })
      })
    }
    return
  }
  if (data.type === 'moveItem') {
    store.commit('moveItem', data.data)
    return
  }
  if (data.type === 'addItem') {
    store.commit('addItem', data.data)
    return
  }
  if (data.type === 'updateItemMeta') {
    store.commit('updateItemMeta', data.data)
    return
  }
  if (data.type === 'alert') {
    ydhl.alert(data.data)
    return
  }
  if (data.type === 'save') {
    ydhl.saveAll(store)
  }
}
const addPage = (id = '') => {
  store.commit('addPage', { pageType: 'page' })
}
const preview = () => {
  const loadingId = YDJS.loading(t('common.pleaseWait'))
  ydhl.savePage(currFunctionId.value, currPage.value, versionId.value, (rst) => {
    YDJS.hide_dialog(loadingId)
    if (rst?.success) {
      store.commit('updateSavedState', { pageUuid: currPage.value.meta.id, saved: 1, versionId: rst.data.versionId })
    }
    const link = document.getElementById('openlink')
    if (link) {
      const type = currPage.value.pageType
      link.setAttribute('href', ydhl.api + 'preview/' + project.value.id + '?module=' + (type === 'page' ? moduleId.value : '') + '&type=' + type + '&page=' + currPage.value?.meta.id)
      link.click()
    }
  })
}
const openCooperationTip = () => {
  YDJS.alert(t('common.somebodyComeInTip'), 'Tips')
}

watch(leftSidebars, () => {
  computeStyle()
})
watch(rightSidebarIsOpen, () => {
  computeStyle()
})
watch(rightSidebarWidth, () => {
  computeStyle()
  changeHeadMoreState('head')
  changeHeadMoreState('pageTab')
})
watch(leftSidebarWidth, () => {
  computeStyle()
  changeHeadMoreState('head')
  changeHeadMoreState('pageTab')
})
watch(pageTitle, (v) => {
  document.title = v
}, {
  immediate: true
})
watch(currPage, () => {
  if (currPage.value) return
  ydhl.get('api/uicomponent.json', { uuid: project.value.id, is_page: 1 }, (rst) => {
    pageLoading.value = false
    if (rst && rst.success) {
      pages.value = rst.data
    }
  })
}, {
  immediate: true
})

onMounted(() => {
  refreshCustomColor(customColors.value)
  $('body').on('click', (event: any) => {
    if ($(event.target).parents('#zoomMenu').length === 0) {
      zoomMenuVisible.value = false
    }
  })

  // workspace 点击空白区域时重置选择的元素，如果当前有内部编辑元素，则也退出（UIBase.ts中watch selectedUIItemId），另外一个重置的地方是page.vue
  $('body').on('click', '.workspace-scrollbar', (event) => {
    // 如果处于富文本编辑，那么只有点击富文本上的退出才退出
    if (selectedUIItem.value?.type === 'RichText' && selectedUIItem.value?.meta.id === inlineEditItemId.value) return
    // console.log(selectedUIItem.value?.type, selectedUIItem.value?.meta.id, inlineEditItemId.value)
    store.commit('updatePageState', { selectedUIItemId: '' })
  })

  computeStyle()

  nextTick(() => {
    changeHeadMoreState('head')
    changeHeadMoreState('pageTab')
  })

  // 注册消息通知函数, 当有postmessage消息过来时，说明iframe已经准备好了，这时想iframe的ports注册自己的回调函数
  window.addEventListener('message', (event) => {
    if (!event.data) return
    if (event.data.type === 'loaded' && event.data.pageId) {
      const iframe: any = document.getElementById(event.data.pageId)
      if (!iframe) return
      iframe.contentWindow.ports.parent = onMessage

      const rawState = toRaw(store.state.design)
      postMessage(event.data.pageId, { type: 'state', state: { page: pickStateFromDesign(event.data.pageId, rawState), css: toRaw(store.state.css) } })
    }
  }, false)

  const { userAgent } = navigator
  const isMac = userAgent.includes('Macintosh')
  const modKey = (isMac ? 'meta' : 'ctrl') // ⌘
  // 删除事件通知给iframe去处理
  keyevent.keydown('backspace', (event) => {
    // console.log('workspace backspace')
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    event.preventDefault()
    event.cancelBubble = true // IE
    postMessage(selectedPageId.value, { type: 'deleteItem' })
  })
  // 删除事件通知给iframe去处理
  keyevent.keydown('delete', (event) => {
    // console.log('workspace backspace')
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    event.preventDefault()
    event.cancelBubble = true // IE
    postMessage(selectedPageId.value, { type: 'deleteItem' })
  })
  keyevent.keydown([modKey, 'c'], (event) => {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    event.preventDefault()
    event.cancelBubble = true // IE
    postMessage(selectedPageId.value, { type: 'copyItem' })
  })
  keyevent.keydown([modKey, 'x'], (event) => {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    event.preventDefault()
    event.cancelBubble = true // IE
    postMessage(selectedPageId.value, { type: 'cutItem' })
  })
  keyevent.keydown([modKey, 'v'], (event) => {
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'TEXTAREA') return
    event.preventDefault()
    event.cancelBubble = true // IE
    postMessage(selectedPageId.value, { type: 'pasteItem' })
  })
  // 保存
  keyevent.keydown([modKey, 's'], (event) => {
    event.preventDefault()
    event.cancelBubble = true
    ydhl.save(store)
  })
  keyevent.keydown([modKey, 'z'], (event) => {
    event.preventDefault()
    event.cancelBubble = true
    postMessage(selectedPageId.value, { type: 'undo' })
  })
  keyevent.keydown([modKey, 'shift', 'z'], (event) => {
    event.preventDefault()
    event.cancelBubble = true
    postMessage(selectedPageId.value, { type: 'redo' })
  })
})
const contextLeft = computed(() => {
  if (!showContextMenu.value || !contextMenuDom.value) return ''
  const menuRect = contextMenuDom.value?.getBoundingClientRect()
  if (document.body.clientWidth < contextMenuPosition.value.x + menuRect?.width) {
    return contextMenuPosition.value.x - menuRect?.width
  } else {
    return contextMenuPosition.value.x
  }
})
const contextTop = computed(() => {
  if (!showContextMenu.value || !contextMenuDom.value) return ''
  const menuRect = contextMenuDom.value?.getBoundingClientRect()
  if (document.body.clientHeight < contextMenuPosition.value.y + menuRect?.height) {
    return contextMenuPosition.value.y - menuRect?.height
  } else {
    return contextMenuPosition.value.y
  }
})
const contextMenuTab = (event, page) => {
  if (event.button === 2) {
    rightClickOnPage.value = page
    rightClickOnModule = store.state.design.pageModule[page.meta.id]
    rightClickOnFunc = store.state.design.pageFunction[page.meta.id]
    showContextMenu.value = true
    event.returnValue = false
    event.stopPropagation()
    event.preventDefault()
    contextMenuPosition.value = { x: event.pageX, y: event.pageY }
    return false
  }
}
const contextMenuOnPath = (data) => {
  emit('contextMenu', { x: data.x, y: data.y })
}
const switchPage = (data) => {
  if (data.meta.id === currPage.value.meta.id) return
  router.push({
    path: '/',
    query: {
      uuid: data.meta.id
    }
  })
}
const closePage = (pageUuid) => {
  if (pageSaved.value[pageUuid] === 0) {
    ydhl.confirm(t('common.notSaveInfo'), t('common.close'), t('common.cancel')).then((dialogId) => {
      ydhl.closeLoading(dialogId)
      store.commit('closePage', pageUuid)
    })
  } else {
    store.commit('closePage', pageUuid)
  }
}

const movePageButtons = [
  {
    text: t('common.moveTo'),
    callback: () => {
      movePageDlgVisible.value = false
      ydhl.post('project/' + project.value.id + '/recovery', { page: movePageFromPageUuid, to: moveToFunc.value.id }, [], (rst) => {
        if (!rst || !rst.success) {
          ydhl.alert(rst ? rst.msg : 'Oops, Please try again')
          return
        }
        store.commit('movePage', { pageUuid: movePageFromPageUuid, module: moveToModule.value, func: moveToFunc.value })
      })
    }
  }
]
const rightClickCopyPage = () => {
  showContextMenu.value = false
  // console.log(props)
  ydhl.loading(t('common.pleaseWait')).then((dlg) => {
    ydhl.closeLoading(dlg)
    ydhl.postJson('api/copy/page.json', { copy_page_uuid: rightClickOnPage.value.meta.id }).then((rst: any) => {
      if (!rst || !rst.success) {
        ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        return
      }
      router.push({
        path: '/',
        query: {
          uuid: rst.data.meta.id
        }
      })
    })
  })
}

const rightClickMovePage = () => {
  showContextMenu.value = false
  // modules.value = []
  ydhl.get('api/module', { uuid: project.value.id, hide_page: 1, hide_popup: 1, hide_component: 1, hide_sub_page: 1 }, (rst) => {
    if (rst && rst.success) {
      modules.value = rst.data.modules
      movePageFromModuleUuid.value = rightClickOnModule?.id
      movePageFromFuncUuid.value = rightClickOnFunc?.id
      movePageFromPageUuid = rightClickOnPage.value.meta.id
      movePageDlgVisible.value = true
    } else {
      ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
    }
  })
}

const rightClickDeletePage = function () {
  showContextMenu.value = false
  if (!rightClickOnPage.value?.meta?.id) return
  ydhl.confirm(t('page.deletePageConfirm'), t('page.deletePage'), t('common.cancel')).then((dialogid: any) => {
    ydhl.closeLoading(dialogid)
    store.commit('deletePage', { pageid: rightClickOnPage.value.meta.id })
    setTimeout(() => {
      closePage(rightClickOnPage.value.meta.id)
    }, 1000)
  })
}

const rightClickPreviewTab = () => {
  showContextMenu.value = false
  const moduleId: any = rightClickOnModule.id
  const pageId: any = rightClickOnPage.value.meta.id
  const type = rightClickOnPage.value.pageType
  const link = document.getElementById('openlink')
  if (link) {
    link.setAttribute('href', ydhl.api + 'preview/' + project.value.id + '?type=' + type + '&module=' + moduleId + '&page=' + pageId)
    link.click()
  }
}

function rightClickCloseSelf () {
  showContextMenu.value = false
  closePage(rightClickOnPage.value.meta.id)
}
function rightClickCloseAll () {
  showContextMenu.value = false
  for (const page of openedPages.value) {
    closePage(page.meta.id)
  }
}
function rightClickCloseUnmodified () {
  showContextMenu.value = false
  for (const page of openedPages.value) {
    if (pageSaved.value[page.meta.id] !== 0) {
      closePage(page.meta.id)
    }
  }
}
function rightClickCloseLeft () {
  showContextMenu.value = false
  const endIndex = openedPages.value.findIndex((item) => item.meta.id === rightClickOnPage.value.meta.id)
  if (endIndex === -1) return
  for (let i = 0; i < endIndex; i++) {
    closePage(openedPages.value[i].meta.id)
  }
}
function rightClickCloseRight () {
  showContextMenu.value = false
  const endIndex = openedPages.value.findIndex((item) => item.meta.id === rightClickOnPage.value.meta.id)
  if (endIndex === -1) return
  for (let i = endIndex + 1; i < openedPages.value.length; i++) {
    closePage(openedPages.value[i].meta.id)
  }
}
function rightClickCloseOther () {
  showContextMenu.value = false
  for (const page of openedPages.value) {
    if (page.meta.id === rightClickOnPage.value.meta.id) continue
    closePage(page.meta.id)
  }
}
</script>
