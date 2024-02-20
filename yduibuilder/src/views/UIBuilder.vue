<template>
  <template v-if="loaded">
    <TopPanel />
    <LeftPanel />
    <RightPanel v-if="currPage" @contextMenu="contextMenu"/>
    <WorkspacePanel ref="workspace" @contextMenu="contextMenu"/>
    <div class="full-backdrop" v-if="backdropVisible"></div>
    <lay-layer v-model="exportDialogVisible" :title="t('common.createComponent')" :shade="true" :area="['500px', '500px']">
      <div class="p-3">
        <UIExport></UIExport>
      </div>
    </lay-layer>
    <div class="context-menu" ref="contextMenuDom" :style="`left:${contextLeft}px;top:${contextTop}px`" v-if="showContextMenu">
      <div class="item" @click.stop="exportUI()">{{t("common.createComponent")}}</div>
      <div class="item" @click.stop="deleteUI()">{{t("common.remove")}}</div>
      <div class="item" @click.stop="copyUI()">{{t("common.copy")}}</div>
    </div>
  </template>
</template>

<script lang="ts">
import UIExport from '@/components/sidebar/UIExport.vue'
import TopPanel from '@/components/page/TopPanelUI.vue'
import RightPanel from '@/components/page/RightPanel.vue'
import LeftPanel from '@/components/page/LeftPanelUI.vue'
import WorkspacePanel from '@/components/page/WorkspacePanel.vue'
import { computed, onMounted, getCurrentInstance, toRaw, ref, nextTick, watch } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import { onBeforeRouteUpdate, useRoute, useRouter } from 'vue-router'
import { YDJSStatic } from '@/lib/ydjs'
import { pickStateFromDesign } from '@/store/page'
import $ from 'jquery'
import InitUI from '@/components/Common'
declare const bootstrap: any

declare const YDJS: YDJSStatic

export default {
  components: {
    TopPanel,
    LeftPanel,
    WorkspacePanel,
    RightPanel,
    UIExport
  },
  setup (props: any, context: any) {
    const store = useStore()
    const route = useRoute()
    const workspace = ref()
    const router = useRouter()
    const exportDialogVisible = ref(false)
    const showContextMenu = ref(false)
    const contextLeft = ref(0)
    const contextTop = ref(0)
    const contextMenuDom = ref()
    const loaded = computed(() => {
      return store.state.design.project.id
    })
    const backdropVisible = computed(() => {
      return store.state.design.backdropVisible
    })
    const { t } = useI18n()
    const { selectedUIItemId, selectedPageId } = InitUI()
    const currPage = computed(() => store.state.design.page)
    const saved = computed(() => store.state.design.pageSaved[currPage.value.meta.id])
    const currFunction = computed(() => store.state.design.function)
    const openedPages = computed(() => store.state.design.openedPages)

    const loadContent = (pageId, functionId, projectId, cb) => {
      // 已经打开了
      const openedPage = openedPages.value[pageId]
      if (openedPage) {
        store.commit('switchPage', openedPage)
        return
      }
      const loadingId = YDJS.loading(t('common.pleaseWait'))
      if (socket.value && pageId) socket.value.send(JSON.stringify({ uuid: pageId, token: ydhl.getJwt(), leavePageId: page.value?.meta?.id, action: 'uibuilder', lang: ydhl.getLanguage() }))

      const justPage = currPage.value ? 1 : 0
      ydhl.get('api/load.json', { pageId, functionId, projectId, justPage }, (rst) => {
        YDJS.hide_dialog(loadingId)
        if (!rst || !rst.success) {
          if (justPage) {
            ydhl.alert(rst ? rst.msg : 'Oops, Please try again')
          } else {
            router.push({
              path: '/error',
              query: {
                error: rst ? rst.msg : 'Oops, Please try again'
              }
            })
          }
          return
        }
        const designData: any = rst.data.design
        store.commit('cleanWorkspaceState')

        // 已经有打开页面的情况下，就只加载要打开的页面数据，其他信息不返回
        if (justPage) {
          store.commit('openLoadedPage', designData)
          if (cb) cb()
          return
        }
        const userData: any = rst.data.user
        const lang: any = rst.data.lang
        const css = rst.data.predefineCSS
        const design: any = JSON.parse(JSON.stringify(oldDesign.value))
        for (const dataKey in designData) {
          design[dataKey] = designData[dataKey]
        }
        // 初始化本地相关数据
        design.socket = socket.value
        if (designData.page) {
          design.openedPages[designData.page.meta.id] = designData.page
          design.pageModule[designData.page.meta.id] = designData.module
          design.pageFunction[designData.page.meta.id] = designData.function
          design.pageVersionId[designData.page.meta.id] = designData.versionId
        }
        const user: any = JSON.parse(JSON.stringify(oldUser.value))
        for (const dataKey in userData) {
          user[dataKey] = userData[dataKey]
        }

        const ctx:any = getCurrentInstance()
        if (ctx != null) ctx.$i18n.locale = lang

        css.cssTranslate = rst.data.cssTranslate
        store.replaceState({ design, user, css })
        if (cb) cb()
      })
    }
    const socket = computed(() => store.state.design.socket)
    const page = computed(() => store.state.design.page)
    const project = computed(() => store.state.design.project)
    const canEdit = computed(() => store.state.design.canEdit)
    const oldDesign = computed(() => store.state.design)
    const oldUser = computed(() => store.state.user)
    onBeforeRouteUpdate((to, from) => {
      if (to?.fullPath === from?.fullPath) return // 点击右边边栏会触发onBeforeRouteUpdate，原因未知
      // 地址发生变化，跳转到其他页面，加载目标页面内容
      loadContent(to.query.uuid, to.query.functionId, to.query.projectId, null)
    })

    const connectSocket = () => {
      // 建立socket连接
      const socket = new WebSocket(ydhl.socket)
      socket.addEventListener('open', function (event) {
        store.commit('updateState', { socket })
        if (route.query.uuid) socket.send(JSON.stringify({ uuid: route.query.uuid, token: ydhl.getJwt(), action: 'uibuilder', lang: ydhl.getLanguage() }))
      })
      socket.addEventListener('close', function (event) {
        // socket 关闭后 server端会把我打开端所有页面信息page user清空
        store.commit('updateState', { socket: null, userList: [] })
      })
      socket.addEventListener('error', function (event) {
      })
      socket.addEventListener('message', function (event) {
        let rst
        try {
          rst = JSON.parse(event.data) || null
        } catch (e) {}
        if (!rst || !rst.type) return
        switch (rst.type) {
          case 'error': {
            YDJS.alert(rst.msg, 'Tip', YDJS.ICON_INFO, [
              {
                label: t('common.ok'),
                cb: function (dialogid) {
                  document.location.href = project?.value?.id ? (ydhl.api + 'project/' + project?.value?.id) : ydhl.api + 'dashboard'
                }
              }])
            return
          }
          case 'userList': {
            store.commit('updateState', { userList: rst.userList })
            break
          }
          case 'deletedPage': {
            if (rst.pageid === page.value?.meta?.id) {
              store.commit('updateState', { userList: [], page: null })
              YDJS.alert(t('common.pageHasBeenDeletedByOtherUser', [rst.user]), 'Tip', YDJS.ICON_INFO, [
                {
                  label: t('common.ok'),
                  cb: function (dialogid) {
                    document.location.href = ydhl.api + 'project/' + project.value.id
                  }
                }])
            }
            break
          }
          case 'modifiedPage': {
            if (rst.pageid === page.value?.meta?.id) {
              YDJS.alert(t('common.pageHasBeenModifiedByOtherUser', [rst.user]), 'Tip', YDJS.ICON_INFO, [
                {
                  label: t('common.ok'),
                  cb: function (dialogid) {
                    YDJS.hide_dialog(dialogid)
                    loadContent(page.value.meta.id, currFunction.value.id, '', () => {
                      const rawState = toRaw(store.state.design)
                      workspace.value.postMessage(page.value.meta.id, { type: 'state', state: { page: pickStateFromDesign(event.data.pageId, rawState), css: toRaw(store.state.css) } })
                    })
                  }
                }])
            }
            break
          }
        }
      })
    }
    onMounted(() => {
      loadContent(route.query.uuid, route.query.functionId, route.query.projectId, null)

      // 未保存时退出提示
      window.addEventListener('beforeunload', function (e) {
        if (saved.value !== 0 || !canEdit.value) return
        e.preventDefault()
        e.returnValue = t('common.notSaveInfo')
        return t('common.notSaveInfo')
      })
      // 定时检查socket 是否连接上
      setInterval(() => {
        if (socket.value) return
        connectSocket()
      }, 5000)
    })
    watch(selectedUIItemId, (o, v) => {
      if (o !== v) {
        showContextMenu.value = false
      }
    })
    const openExportUIDialog = () => {
      exportDialogVisible.value = true
      nextTick(() => {
        const myModalEl = document.getElementById('exportUiDialog') as HTMLElement
        const myModal = new bootstrap.Modal(myModalEl)
        myModalEl.addEventListener('hide.bs.modal', function (event) {
          exportDialogVisible.value = false
          $('#exportUiDialog').remove()
        })
        myModal.show()
      })
    }
    const deleteUI = () => {
      workspace.value.postMessage(selectedPageId.value, { type: 'deleteItem' })
    }
    const copyUI = () => {
      showContextMenu.value = false
      workspace.value.postMessage(selectedPageId.value, { type: 'copyItem' })
    }
    const exportUI = () => {
      showContextMenu.value = false
      openExportUIDialog()
    }
    const contextMenu = (data) => {
      showContextMenu.value = true
      nextTick(() => {
        const menuRect = contextMenuDom.value?.getBoundingClientRect()
        // console.log(document.body.clientWidth, document.body.clientHeight, menuRect, data)
        if (document.body.clientWidth < data.x + menuRect?.width) {
          contextLeft.value = data.x - menuRect?.width
        } else {
          contextLeft.value = data.x
        }
        if (document.body.clientHeight < data.y + menuRect?.height) {
          contextTop.value = data.y - menuRect?.height
        } else {
          contextTop.value = data.y
        }
      })
    }
    return {
      backdropVisible,
      loaded,
      canEdit,
      currPage,
      contextMenuDom,
      workspace,
      t,
      exportDialogVisible,
      showContextMenu,
      contextLeft,
      contextTop,
      exportUI,
      openExportUIDialog,
      deleteUI,
      copyUI,
      contextMenu
    }
  },
  name: 'UIBuilder'
}
</script>
<style type="text/css">
.context-menu{
  position: absolute;
  z-index: 999;
  border: 1px solid #ccc;
  box-shadow: 3px 5px 7px 0rem rgb(45 47 52 / 75%);
}
.context-menu .item{
  padding: 5px 10px;
  background-color: #efefef;
  font-size: 14px;
  word-break: keep-all;
  white-space: nowrap;
  cursor: pointer;
}
.context-menu .item:hover{
  background-color: #ffffff;
}
</style>
