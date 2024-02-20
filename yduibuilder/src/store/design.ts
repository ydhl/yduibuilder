import { UIBase } from '@/store/model'
import ydhl from '@/lib/ydhl'
import baseUIDefines from '@/components/ui/define'
import { YDJSStatic } from '@/lib/ydjs'
import router from '@/router'
import { toRaw } from 'vue'
declare const YDJS: YDJSStatic

/**
 * 某个UiItem在ui树结构中的关系
 */
interface UIItemStruct{
  /**
   * 在其所在容器中的位置索引
   */
  index: number;
  /**
   * ui配置结构体
   */
  uiConfig: UIBase|null;
  /**
   * 上层ui配置结构体
   */
  parentConfig: UIBase|null
}

function idHasExists (uiitems: Array<UIBase>, id) {
  for (const item of uiitems) {
    if (item.meta.id === id) return true
    if (item.items) {
      const hasEdist = idHasExists(item.items, id)
      if (hasEdist) return true
    }
  }
  return false
}
function regenerateId (uiconfig: UIBase, prefix) {
  const newId = ydhl.uuid(5, 0, prefix)
  uiconfig.meta.id = newId
  if (uiconfig.items !== undefined) {
    for (let index = 0; index < uiconfig.items.length; index++) {
      uiconfig.items[index] = regenerateId(uiconfig.items[index], prefix)
    }
  }
  return uiconfig
}
/**
 * 在页面中查找item的信息：序号和其ui配置 { index, uiConfig, parentConfig }
 * @param state
 * @param uiid
 * @return UIItemStruct
 */
function findUIItemInfo (state: Record<any, any>, uiid: string) {
  const _find = (uiid: string, parent: UIBase) => {
    const findStruct: UIItemStruct = {
      index: -1,
      uiConfig: null,
      parentConfig: null
    }
    if (!parent) return findStruct
    if (parent.meta.id === uiid) {
      findStruct.index = 0
      findStruct.uiConfig = parent
      return findStruct
    }
    if (!parent.items) return findStruct

    for (let i = 0; i < parent.items.length; i++) {
      if (parent.items[i].meta.id === uiid) {
        findStruct.index = i
        findStruct.uiConfig = parent.items[i]
        findStruct.parentConfig = parent
        return findStruct
      }
      const subItems: any = parent.items[i].items || null
      if (subItems && subItems.length > 0) {
        const findInfo: any = _find(uiid, parent.items[i])
        if (findInfo.index !== -1) {
          return findInfo
        }
      }
    }

    return findStruct
  }
  return _find(uiid, state.page)
}

/**
 * 在targetItemInfo中添加sourceItemInfo，成功返回true，识别返回false
 * @param placement
 * @param sourceItemInfo
 * @param targetItemInfo
 * @return boolean
 */
function addItemInfo (placement, sourceItemInfo: UIBase|null, targetItemInfo: UIItemStruct) {
  if (sourceItemInfo === null) return false
  if (targetItemInfo.uiConfig === null) return false
  if (placement === 'in') { // in 表示放在目标容器内
    const subItemType = baseUIDefines[targetItemInfo.uiConfig.type]?.subItemType
    if (subItemType && subItemType.indexOf(sourceItemInfo.type) === -1) {
      YDJS.toast(`you can not add ${sourceItemInfo.type} in ${targetItemInfo.uiConfig.type}, just allow item type: ${subItemType.join(',')}`, YDJS.ICON_INFO)
      return false
    }

    if (targetItemInfo.uiConfig.items) {
      targetItemInfo.uiConfig.items.push(sourceItemInfo)
    } else {
      targetItemInfo.uiConfig.items = [sourceItemInfo]
    }
    return true
  }

  let newIndex = -1
  switch (placement) {
    case 'left':
    case 'top':
      newIndex = targetItemInfo.index
      break
    case 'right':
    case 'bottom':
      newIndex = targetItemInfo.index + 1
      break
  }

  // 插入到目标位置
  if (targetItemInfo.parentConfig !== null) {
    if (!targetItemInfo.parentConfig.items) targetItemInfo.parentConfig.items = []
    targetItemInfo.parentConfig.items.splice(newIndex, 0, sourceItemInfo)
    return true
  } else { // targetItemInfo 就是顶层元素时
    if (!targetItemInfo.uiConfig?.meta?.isContainer) {
      ydhl.alert('Only containers can place components')
      return false
    }
    if (!targetItemInfo.uiConfig.items) targetItemInfo.uiConfig.items = []
    if (placement === 'top' || placement === 'left') {
      targetItemInfo.uiConfig.items.splice(0, 0, sourceItemInfo)
    } else {
      targetItemInfo.uiConfig.items.push(sourceItemInfo)
    }
  }
  // console.log(targetItemInfo, placement)
  return false
}
function isSubItem (targetId, uiconfig:UIBase|null) {
  if (!uiconfig) return false
  if (uiconfig.meta.id === targetId) return true
  if (!uiconfig.items) return false
  for (const item of uiconfig.items) {
    const rst = isSubItem(targetId, item)
    if (rst) return true
  }
  return false
}
function cleanWorkspaceState (state) {
  state.dragoverUIItemId = ''
  state.dragoverPlacement = ''
  state.dragoverInParent = ''
  state.inlineEditItemId = ''
  state.hoverUIItemId = ''
  state.selectedUIItemId = ''
  state.showEventPanel = false
  state.backdropVisible = false
}
function updateMeta (state, type, props, item, isMerge) {
  // if (type) console.log(item.meta[type])
  const merge = (isMerge, name, node: any, newValue: any) => {
    const oldValue = node[name]
    if (Array.isArray(newValue)) { // Array
      if (!isMerge && oldValue) oldValue.splice(0, oldValue.length)
      if (oldValue) {
        node[name].push(...newValue)
      } else {
        node[name] = newValue
      }
      return
    }
    if (newValue === 'object') { // Object
      if (isMerge && oldValue) {
        node[name] = ydhl.deepMerge(oldValue, newValue)
      } else {
        node[name] = newValue
      }
      return
    }
    node[name] = newValue
  }
  state.pageSaved[state.page.meta.id] = 0
  if (type && !item.meta[type]) item.meta[type] = {}
  for (const name in props) {
    if (type) {
      if (props[name] !== undefined) {
        merge(isMerge, name, item.meta[type], props[name])
      } else {
        delete item.meta[type][name]
        if (ydhl.isEmptyObject(item.meta[type])) delete item.meta[type]
      }
      continue
    }

    if (props[name] !== undefined) {
      merge(isMerge, name, item.meta, props[name])
    } else {
      delete item.meta[name]
    }
  }
}

/**
 * 切换到一个已经存在到页面
 * @param state
 * @param targetPage
 */
function switchPage (state, targetPage) {
  const currPage = state.page

  // 如果当前页面没有被关闭，先把当前页面的状态缓存起来，如果关闭了会先删除state.openedPages中的数据
  if (state.openedPages[currPage.meta.id]) state.openedPages[currPage.meta.id] = currPage

  state.module = state.pageModule[targetPage.meta.id] || {}
  state.function = state.pageFunction[targetPage.meta.id] || {}
  state.page = targetPage

  cleanWorkspaceState(state)
}
function closePage (state, pageUuid) {
  const ids = Object.keys(state.openedPages)
  const closePageIndex = ids.indexOf(pageUuid)
  if (closePageIndex === -1) return
  const deletedFunction = state.pageFunction[pageUuid]
  delete state.pageVersionId[pageUuid]
  delete state.pageFunction[pageUuid]
  delete state.pageModule[pageUuid]
  delete state.pageSaved[pageUuid]
  delete state.openedPages[pageUuid]
  // 删除的是当前打开的页面
  if (state.page.meta.id === pageUuid) {
    const willOpenIdIndex = closePageIndex === ids.length - 1 ? closePageIndex - 1 : closePageIndex + 1
    const willOpenId = ids[willOpenIdIndex]

    if (willOpenId) {
      router.replace({
        path: '/',
        query: {
          uuid: willOpenId
        }
      })
    } else {
      router.replace({
        path: '/',
        query: {
          functionId: deletedFunction.id,
          projectId: state.project.id
        }
      })
    }
  }
}
export default {
  state: {
    saving: false, // true 保存中
    project: {}, // 当前设计的项目信息
    module: {}, // 当前设计的模块信息
    function: {}, // 当前设计的功能信息
    pageVersionId: {}, // openedPages中每个页面对应的版本:pageid: versionid
    pageSaved: {}, // openedPages中每个页面对应的状态 -1 初始状态（第一次加载） 1已保存 0未保存:pageid: saved
    pageModule: {}, // openedPages中每个页面对应的模块信息 pageid:{}
    pageFunction: {}, // openedPages中每个页面对应的功能信息 pageid:{}
    page: null, // 当前正在编辑的页面
    openedPages: {}, // 当前打开的所有页面数据 pageid: {}
    showEventPanel: false, // 是否显示事件层
    backdropVisible: false,
    canEdit: false, // 是否能编辑页面
    userRole: '', // 用户在项目中的角色
    codeTypes: [], // 当前功能框架能导出的代码类型，比如wxmp一个功能单元能导出wxml wcss js json几个文件
    endKind: 'pc', // 终端类型
    pageContentHeight: {}, // 保存page在iframe中的内容高度，用于计算wrapper和iframe的实际高度
    scale: 1, // 当前页面被缩放的比例
    simulateModel: 'pc', // pc, tablet, portrait
    simulateWidth: 0, // 模拟器的宽度（px）
    simulateHeight: 0, // 模拟器的高度（px）
    /**
     * 页面下元素的的额外信息，key是id，value是自定义值，比如表格的数据
     * tableId: { header: footer: row }
     */
    extraInfo: {},
    /**
     * 打开当前页面的用户列表
     */
    userList: [],
    /**
     * 左右边栏的状态
     */
    leftSidebars: [], // 当前打开的左边栏模块
    rightSidebars: [], // 当前打开的右边栏模块 { sidebar: 'UITree', tab: 'UITree' }
    rightSidebarIsOpen: false, // 又右边栏是否处于打开状态
    rightSidebarWidth: 350, // 右边栏默认的宽度大小
    rightSidebarMinWidth: 250, // 右边栏最小宽度大小，控制right header
    leftSidebarWidth: 280, // 左边栏默认的宽度
    leftSidebarMinWidth: 280, // 左边栏最小的宽度
    /**
     * 设计器中ui元素悬浮，拖动状态
     */
    inlineEditItemId: '', // 当前处于内联编辑的元素id
    hoverUIItemId: '', // 鼠标悬浮的元素id
    highlightUIItemIds: [], // 需要高亮显示的元素id
    selectedUIItemId: '', // 当前选中的元素id
    dragoverUIItemId: '', // 拖动时悬浮在那个元素之上
    dragoverPlacement: '', // 拖动时悬浮在那个元素的位置 left right top bottom in
    dragoverInParent: '', // 对于有多个区域可放置组件的容器，目前悬浮于那个部分之上
    updateInlineItemValue: false, // 保存前通知处于内联编辑的元素更新自己的内容（如text richtext）
    /**
     * WebSocket 对象
     */
    socket: null,
    mouseXYInIframe: {}, // 鼠标在iframe中移动的坐标
    mouseupInFrame: '', // 在iframe 中点击的事件通知
    previewStyleItem: {} // 设置style selector时用于预览，uibase结构体, 但只用到其中到meta部分内容
  },
  mutations: {
    updateSavedState (state: any, { pageUuid, saved, versionId, saving }) {
      state.pageSaved[pageUuid] = saved
      state.pageVersionId[pageUuid] = versionId
      if (saving !== undefined) {
        state.saving = saving
      }
    },
    /**
     * 已经有打开页面的情况下，就只加载要打开的页面数据，其他信息不返回
     * @param state
     * @param design
     */
    openLoadedPage (state: any, design) {
      if (design.page) {
        state.pageModule[design.page.meta.id] = design.module
        state.pageFunction[design.page.meta.id] = design.function
        state.pageSaved[design.page.meta.id] = -1
        state.pageVersionId[design.page.meta.id] = design.versionId
        state.openedPages[design.page.meta.id] = design.page
        switchPage(state, design.page)
      } else {
        cleanWorkspaceState(state)
        state.module = design.module
        state.function = design.function
        state.page = null
      }
    },
    /**
     * 把当前的设计页面切换到一个已经加载的页面
     * @param state
     * @param targetPage
     */
    switchPage (state: any, targetPage) {
      switchPage(state, targetPage)
    },
    cleanWorkspaceState (state: any) {
      cleanWorkspaceState(state)
    },
    updateExtraInfo (state: any, props: Record<any, any>) {
      for (const name in props) {
        state.extraInfo[name] = props[name]
      }
    },
    /**
     * 关闭打开的页面
     * @param pageUuid string 页面的uuid
     */
    closePage (state: any, pageUuid) {
      closePage(state, pageUuid)
    },
    /**
     * 增加页面
     * @param PageType pageType 页面的类型
     */
    addPage (state: any, { pageType }) {
      const newPageId = ydhl.uuid(5, 0, 'Page' + state.project.keyId)
      cleanWorkspaceState(state)
      const page = {
        type: 'Page',
        pageType,
        meta: {
          id: newPageId,
          isContainer: true,
          title: 'unnamed ' + pageType
        },
        items: []
      }
      ydhl.savePage(state.function.id, page, -1, (rst) => {
        if (!rst || !rst.success) {
          ydhl.alert(rst.msg || 'Oops')
          return
        }
        router.push({
          path: '/',
          query: {
            uuid: newPageId
          }
        })
      })
    },
    /**
     * 新的page中所有元素的id都将被重新计算
     * @param state
     */
    copyPage (state: any) {
      // const page = JSON.parse(JSON.stringify(state.page))
      const page = toRaw(state.page)

      // 保存新页面
      ydhl.savePage(state.function.id, page, -1, (rst) => {
        if (!rst || !rst.success) {
          ydhl.alert(rst.msg || 'Oops')
          return
        }
        router.push({
          path: '/',
          query: {
            uuid: rst.data.page_uuid
          }
        })
      }, false, '', true)
    },
    /**
     * 删除页面
     * @param state
     * @param pageid
     */
    deletePage (state: any, { pageid }) {
      const loadingid = YDJS.loading('')
      ydhl.deletePage(pageid, (rst) => {
        YDJS.hide_dialog(loadingid)
        if (!rst || !rst.success) return
        if (state.socket) {
          state.socket.send(JSON.stringify({
            action: 'deletedPage',
            id: rst.data.deletedPageId, // 被删除页面的id主键
            pageid: pageid, // 被删除页面到uuid
            token: ydhl.getJwt()
          }))
        }
        closePage(state, pageid)
      })
    },
    updatePage (state: any, { pageId, props }) {
      for (const name in props) {
        state.page[name] = ydhl.deepMerge(state.page[name], props[name])
      }
      state.pageSaved[pageId] = 0
    },
    clearDragoverState (state: any) {
      state.dragoverUIItemId = ''
      state.dragoverPlacement = ''
      state.dragoverInParent = ''
    },
    updatePageContentHeight (state: any, { pageId, contentHeight }) {
      state.pageContentHeight[pageId] = contentHeight
    },
    /**
     * 对state中涉及到设计状态的属性进行处理，这些数据会同步到page.ts中，以便iframe中的store能够同步
     * @param state
     * @param props
     */
    updatePageState (state: any, props: Record<any, any>) {
      // console.log(props)
      for (const name in props) {
        // eslint-disable-next-line no-eval
        // eval(`state.${name}=props[name]`)
        state[name] = props[name]
      }
    },
    /**
     * 对state中的单个属性（非page.ts中的内容）的统一处理
     * @param state
     * @param props
     */
    updateState (state: any, props: Record<any, any>) {
      // console.log(state)
      for (const name in props) {
        // eslint-disable-next-line no-eval
        // eval(`state.${name}=props[name]`)
        state[name] = props[name]
      }
    },
    switchEventShow (state: any, showEventPanel) {
      state.showEventPanel = showEventPanel
    },
    /**
     * 改变sourceId的元素到targetId的placement（由dragoverPlacement指定）位置，可能跨页
     * @param state
     * @param sourceId 被拖动的元素id
     * @param targetId 目标元素id
     * @param placeInParent 移入目标容器的那个部分中
     */
    moveItem (state: any, { sourceId, targetId, placeInParent }: any) {
      const clean = () => {
        cleanWorkspaceState(state)
        state.selectedUIItemId = sourceId
        state.pageSaved[state.page.meta.id] = 0
      }

      if (sourceId === targetId || !targetId) {
        clean()
        return
      }
      const placement = (state.dragoverPlacement as string).toLowerCase()
      if (!placement) {
        clean()
        return
      }
      const sourceItemInfo: UIItemStruct = findUIItemInfo(state, sourceId)
      // 如targetItemInfo是sourceItemInfo的子元素，则移动终止
      if (isSubItem(targetId, sourceItemInfo?.uiConfig)) {
        clean()
        return
      }

      // 删除原来的位置
      sourceItemInfo?.parentConfig?.items?.splice(sourceItemInfo.index, 1)

      if (!sourceItemInfo.uiConfig) {
        clean()
        return
      }
      const targetItemInfo = findUIItemInfo(state, targetId)
      sourceItemInfo.uiConfig.placeInParent = placeInParent

      addItemInfo(placement, sourceItemInfo.uiConfig, targetItemInfo)
      clean()
    },
    /**
     * 在pageId页面的targetId元素的placement位置增加类型为type，指定meta的ui元素
     * @param state
     * @param type
     * @param id
     * @param pageId
     * @param placeInParent
     */
    addItem (state: any, { type, meta, items, placement, targetId, pageId, placeInParent, subPageId }) {
      const sourceItemInfo: any = {
        type,
        placeInParent,
        subPageId,
        meta,
        items: items || []
      }

      const targetItemInfo: UIItemStruct = findUIItemInfo(state, targetId)
      if (targetItemInfo.index === -1) return
      placement = (placement || '').toLowerCase()
      const rst = addItemInfo(placement, sourceItemInfo, targetItemInfo)
      // 把添加的元素标记为选中
      if (rst) {
        state.selectedUIItemId = sourceItemInfo.meta.id
      }
      state.pageSaved[state.page.meta.id] = 0
    },

    /**
     * 更新item的meta普通的内容，对于meta中的array，object等复合型属性通过type指定（比如style，custom，css），直接
     * 在meta下的名值对不用传type
     *
     * @param state
     * @param itemid
     * @param props <strong color="red">特别注意 props中的value如果是undefined，为了减少json等结构体内容，会把这种假删除掉（delete），比如如果一个json结构体是{a:true|false, b:[]},那么当设置当设置a: undefined时，保存当结果就是{b:[]}</strong>
     * @param isMerge, 默认情况下对props中的设置是覆盖，但如果新值和旧值要合并请，设置为true
     * @param pageId
     * @param type
     */
    updateItemMeta (state: any, { type, itemid, props, isMerge, pageId }) {
      let item: UIBase | null = null

      const obj = findUIItemInfo(state, itemid)
      if (obj.index !== -1) {
        item = obj.uiConfig
      }
      if (!item) return
      updateMeta(state, type, props, item, isMerge)
    },
    /**
     * 更新预览style
     *
     * @param state
     * @param itemid
     * @param props <strong color="red">特别注意 props中的value如果是undefined，为了减少json等结构体内容，会把这种值删除掉（delete），比如如果一个json结构体是{a:true|false, b:[]},那么当设置当设置a: undefined时，保存的结果就是{b:[]}</strong>
     * @param isMerge, 默认情况下对props中的设置是覆盖，但如果新值和旧值要合并请，设置为true
     * @param pageId
     * @param type
     */
    updatePreviewStyle (state: any, { type, props, isMerge }) {
      if (!state.previewStyleItem.meta) {
        state.previewStyleItem.meta = {}
      }
      updateMeta(state, type, props, state.previewStyleItem, isMerge)
    },
    /**
     * 删除传入的ui id
     * @param state
     * @param ids
     * @param pageId
     */
    deleteItem (state: any, { ids, pageId }) {
      for (const id of ids) {
        const { index, uiConfig, parentConfig } = findUIItemInfo(state, id)
        if (uiConfig.meta.isLock || index === -1) continue
        if (parentConfig != null) {
          parentConfig.items.splice(index, 1)
        }
      }
      state.selectedUIItemId = ''
      state.pageSaved[state.page.meta.id] = 0
    },
    /**
     * 添加事件绑定
     * @param state
     * @param itemid
     * @param eventId
     */
    addEventBind (state: any, { itemid, eventId }) {
      const { index, uiConfig } = findUIItemInfo(state, itemid)
      if (index === -1) return
      if (!uiConfig?.events) uiConfig.events = []
      uiConfig.events.push(eventId)
    },
    /**
     * 删除事件绑定
     * @param state
     * @param itemid
     * @param bindId
     */
    removeEventBind (state: any, { itemid, bindId }) {
      const { index, uiConfig } = findUIItemInfo(state, itemid)
      if (index === -1) return
      if (!uiConfig.events) return
      const i = uiConfig.events.findIndex(item => item === bindId)
      if (i === -1) return
      uiConfig.events.splice(i, 1)
    },
    /**
     * 初始创建弹窗事件绑定
     * @param state
     * @param newPageId
     */
    createPopupBind (state: any, { newPageId }) {
      const oldPageId = state.page.meta.id

      // 新页面，跳转到新页面设计
      cleanWorkspaceState(state)
      const page = {
        type: 'Page',
        pageType: 'popup',
        meta: {
          id: newPageId,
          isContainer: true,
          title: 'unnamed popup'
        },
        items: []
      }

      ydhl.savePage(state.function.id, page, -1, (rst) => {
        if (!rst || !rst.success) {
          ydhl.alert(rst.msg || 'Oops')
          return
        }
        router.push({
          path: '/',
          query: {
            uuid: newPageId,
            fromPageId: oldPageId
          }
        })
      })
    },
    /**
     * 创建子页面, 子页面作为itemid原始的内容，比如幻灯片的一张幻灯片
     *
     * @param state
     * @param itemid
     * @param newPageId 当修改子页面时传入
     * @param copyFromPageId copy子页面，如果传入，忽略newPageId
     * @param includeUI 子页面只能包含的元素，传入时忽略excludeUI
     * @param excludeUI 子页面不能包含的元素
     * @param rootUI 新建页面的根元素
     */
    createSubpage (state: any, { itemid, newPageId, copyFromPageId, includeUI, excludeUI, rootUI }) {
      const isNewPage = copyFromPageId || !newPageId
      newPageId = isNewPage ? ydhl.uuid(5, 0, 'Page' + state.project.keyId) : newPageId
      const targetItem = findUIItemInfo(state, itemid)
      if (targetItem.index === -1) return
      let newItemInfo: any = {
        type: 'Page',
        pageType: 'subpage',
        subPageId: newPageId,
        meta: {
          id: newPageId, // 组件的id也就是子页的id
          isContainer: true,
          title: 'unnamed subpage'
        },
        items: []
      }
      if (copyFromPageId) {
        for (const item of targetItem.uiConfig.items) {
          if (item.subPageId === copyFromPageId) {
            newItemInfo = regenerateId(JSON.parse(JSON.stringify(item)), newPageId)
            newItemInfo.subPageId = newPageId
            break
          }
        }
      }
      if (isNewPage) {
        newItemInfo.meta.id = newPageId
        addItemInfo('in', newItemInfo, targetItem)
      }

      const oldPageId = state.page.meta.id

      // 保存现有页面数据，并打开子页面进行设计
      cleanWorkspaceState(state)
      // 页面已经存在
      if (!isNewPage) {
        router.push({
          path: '/',
          query: {
            uuid: newPageId,
            fromPageId: oldPageId
          }
        })
        return
      }
      const page: any = {
        type: 'Page',
        pageType: 'subpage',
        meta: newItemInfo.meta,
        items: newItemInfo.items
      }
      if (!page.meta.custom) page.meta.custom = {}
      if (includeUI) page.meta.custom.includeUI = includeUI
      if (excludeUI) page.meta.custom.excludeUI = excludeUI
      if (rootUI) {
        page.items = [rootUI]
      }

      ydhl.savePage(state.function.id, page, -1, (rst) => {
        if (!rst || !rst.success) {
          ydhl.alert(rst.msg || 'Oops')
          return
        }
        router.push({
          path: '/',
          query: {
            uuid: newPageId,
            fromPageId: oldPageId
          }
        })
      })
    },
    /**
     * 删除itemid中指定索引的子页面
     *
     * @param state
     * @param itemid
     * @param index
     */
    deleteSubpage (state: any, { itemid, index }) {
      const { uiConfig } = findUIItemInfo(state, itemid)
      if (!uiConfig) return
      const deleteItem = uiConfig.items[index]
      if (uiConfig.meta.custom?.activeSlide === index) uiConfig.meta.custom.activeSlide = 0
      uiConfig.items.splice(index, 1)
      state.pageSaved[state.page.meta.id] = 0
      ydhl.deletePage(deleteItem.subPageId, (rst) => {
        if (!rst || !rst.success) return
        if (state.socket) {
          state.socket.send(JSON.stringify({
            action: 'deletedPage',
            id: rst.data?.deletedPageId,
            pageid: deleteItem.subPageId,
            token: ydhl.getJwt()
          }))
        }
      })
    }
  },
  actions: {
  },
  getters: {
    /**
     * 验证id是否唯一
     * @param state
     */
    idHasExists: (state) => (newid) => {
      return idHasExists([state.page], newid)
    },
    /**
     * 获取当前选中页面中指定id的元素
     * @param state
     * @return { index, uiConfig, parentConfig }
     */
    getUIItemInPage: (state) => (uiid: string, pageid: string) => {
      return findUIItemInfo(state, uiid)
    },
    /**
     * 获取当前选中页面中指定id的元素
     * @param state
     * @return { index, uiConfig, parentConfig }
     */
    getUIItem: (state) => (uiid: string) => {
      if (!uiid) {
        return {
          index: -1,
          uiConfig: null,
          parentConfig: null
        }
      }
      const obj = findUIItemInfo(state, uiid)
      if (obj.index !== -1) return obj
      // Not Found
      return {
        index: -1,
        uiConfig: null,
        parentConfig: null
      }
    },
    /**
     * 获取指定的页面
     * @param state
     * @return uiConfig
     */
    getPage: (state) => (pageid: string) => {
      return state.page?.meta?.id === pageid ? state.page : null
    }
  }
}
