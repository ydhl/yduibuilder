import { createStore, createLogger } from 'vuex'
import design from './design'
import user from './user'
import page from './page'
import css from './css'
const debug = false // process.env.NODE_ENV !== 'production'
const plugins: any = []
declare const ports: any

const findPageUIConfig = (state: any, pageId: any) => {
  if (pageId !== state.design.page.meta.id) return null
  return JSON.parse(JSON.stringify(state.design.page))
}

const postMessage = (store) => {
  // 当 store 初始化后调用
  store.subscribe((mutation, state) => {
    if (window.top !== window) return

    if (mutation.type === 'addItem' || mutation.type === 'deleteItem' || mutation.type === 'updateItemMeta' || mutation.type === 'deleteSubpage') { // 通知目标页面
      if (!ports?.[mutation.payload.pageId]) return
      const msg = {
        type: 'updatePageState',
        payload: {
          uiconfig: findPageUIConfig(state, mutation.payload.pageId),
          dragoverUIItemId: state.design.dragoverUIItemId,
          dragoverPlacement: state.design.dragoverPlacement,
          dragoverInParent: state.design.dragoverInParent,
          selectedUIItemId: state.design.selectedUIItemId
        }
      }
      // console.log(mutation.type, new Date())
      ports[mutation.payload.pageId](msg)
      return
    }
    if (mutation.type === 'moveItem') { // 通知源和目标页面
      const sourceFrame: any = document.getElementById(mutation.payload.sourcePageId)
      const targetFrame: any = document.getElementById(mutation.payload.targetPageId)
      if (sourceFrame) {
        const msg = {
          type: 'updatePageState',
          payload: {
            uiconfig: findPageUIConfig(state, mutation.payload.sourcePageId),
            dragoverUIItemId: state.design.dragoverUIItemId,
            dragoverPlacement: state.design.dragoverPlacement,
            dragoverInParent: state.design.dragoverInParent,
            selectedUIItemId: state.design.selectedUIItemId
          }
        }
        ports[mutation.payload.sourcePageId](msg)
      }
      if (targetFrame && mutation.payload.sourcePageId !== mutation.payload.targetPageId) {
        const msg = {
          type: 'updatePageState',
          payload: {
            uiconfig: findPageUIConfig(state, mutation.payload.targetPageId),
            dragoverUIItemId: state.design.dragoverUIItemId,
            dragoverPlacement: state.design.dragoverPlacement,
            dragoverInParent: state.design.dragoverInParent,
            selectedUIItemId: state.design.selectedUIItemId
          }
        }
        ports[mutation.payload.targetPageId](msg)
      }
      return
    }
    if (mutation.type === 'updatePageState' || mutation.type === 'clearDragoverState' || mutation.type === 'updateExtraInfo' || mutation.type === 'switchEventShow') { // 更新所有页面
      const pageid = state.design?.page?.meta?.id
      if (ports?.[pageid]) ports[pageid](mutation)
    }
  })
}
plugins.push(postMessage)
if (debug) {
  plugins.push(createLogger())
}

let store
if (window.top !== window) {
  store = createStore({
    modules: { page, css },
    strict: debug,
    plugins: [/* createLogger() */]
  })
} else {
  store = createStore({
    modules: { design, user, css },
    strict: debug,
    plugins: plugins
  })
}
export default store
