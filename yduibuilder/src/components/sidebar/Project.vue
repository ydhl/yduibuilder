<template>
  <div class="ps-2 mt-2 mb-2 d-flex justify-content-between align-items-center">
    <div class="flex-grow-1">
      {{t('project.name')}}
    </div>
    <div class="d-flex align-items-center">
      <div @click="positionToPage" class="me-2"><i class="iconfont icon-position hover-primary"></i></div>
      <div v-if="!openState" @click="expandAll"><i class="iconfont icon-expandall hover-primary"></i></div>
      <div v-if="openState" @click="collapseAll"><i class="iconfont icon-collapseall hover-primary"></i></div>
    </div>
  </div>
  <div class="list-group list-group-flush ms-2">
    <div class="list-group-item-action d-flex justify-content-between align-items-center"
         @contextmenu="contextMenu($event, 'project', 'project')"
         @mouseleave="mouseleave($event, 'project', 'project')"
         @mouseover="hoverId='project'"
         @click="openProject=!openProject">
      <div class="d-flex align-items-center fw-bold">
        <i :class="{'iconfont': true, 'icon-tree-open': openProject, 'icon-tree-close': !openProject}"></i>
        <i class="iconfont icon-folder"></i>&nbsp;<small>{{project.name}}</small></div>
      <div :class="{'dropdown': true, 'invisible':hoverId!='project'}">
        <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
        <ul class="dropdown-menu dropdown-menu-end" id="projectproject">
          <li><a href="#" @click.stop="addModule(project)" class="dropdown-item">{{t('module.addModule')}}</a></li>
        </ul>
      </div>
    </div>

    <template v-if="openState || openProject">
      <template v-for="(module,index) in modules" :key="index">
        <div class="tab-1 list-group-item-action d-flex justify-content-between align-items-center"
             @contextmenu="contextMenu($event, 'module', module.id)"
             @mouseleave="mouseleave($event, 'module', module.id)"
             @mouseover="hoverId=module.id"
             @click="toggleModule($event, module.id)">
          <div class="flex-grow-1">
            <i :class="{'iconfont': true, 'icon-tree-open': openModuleIds[module.id], 'icon-tree-close': !openModuleIds[module.id]}"></i>
            <i class="iconfont icon-folder"></i>&nbsp;<small>{{module.name}}</small>
          </div>
          <div :class="{'dropdown': true, 'invisible':hoverId!=module.id}">
            <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
            <ul class="dropdown-menu dropdown-menu-end" :id="'module' + module.id">
              <li><a href="#" @click.stop="editModule(module)" class="dropdown-item"><i class="iconfont icon-edit"></i> {{t('common.edit')}}</a></li>
              <li><a href="#" class="dropdown-item text-danger" @click.stop="deleteModuleConfirm(module)"><i class="iconfont icon-remove"></i>
                {{ t('module.deleteModule') }}</a></li>
              <li><a href="#"  @click.stop="addFunction(module)" class="dropdown-item"><i class="iconfont icon-function"></i> {{t('module.addFunction')}}</a></li>
            </ul>
          </div>
        </div>

        <!--function-->
        <template :key="index" v-for="(func, index) in module.functions">
          <div @click="toggleFunction($event, func)" v-if="openModuleIds[module.id]" :data-uuid="func.id"
               @mouseleave="mouseleave($event, 'function', func.id)"
               @mouseover="hoverId=func.id"
               @contextmenu="contextMenu($event, 'function',func.id)"
               class="list-group-item-action d-flex align-items-center tab-2">
            <div class="flex-grow-1">
              <i :class="{'iconfont': true, 'icon-tree-close': !openFunctionIds[func.id], 'icon-tree-open': openFunctionIds[func.id]}"></i>
              <i class="iconfont icon-folder"></i>&nbsp;<small>{{func.name}}</small>
            </div>

            <div :class="{'dropdown': true, 'invisible':hoverId!=func.id}">
              <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
              <ul class="dropdown-menu dropdown-menu-end" :id="'function'+func.id">
                <li><a href="#" @click.stop="editFunction(func)" class="dropdown-item"><i class="iconfont icon-edit"></i> {{t('common.edit')}}</a></li>
                <li><a href="#" class="dropdown-item text-danger" @click.stop="deleteFunctionConfirm(func)"><i class="iconfont icon-remove"></i>
                  {{ t('module.deleteFunction') }}</a></li>
                <li><a href="#" @click.stop="gotoFunction(func.id)" class="dropdown-item"><i class="iconfont icon-plus"></i> {{t('common.addPage')}}</a></li>
              </ul>
            </div>
          </div>
          <!-- page -->
          <template v-if="openFunctionIds[func.id] && openModuleIds[module.id]">
            <div :key="pageIndex"
                 @mouseover="hoverId=page.id"
                 @mouseleave="mouseleave($event, 'page', page.id)"
                 @contextmenu="contextMenu($event, 'page', page.id)"
                 :class="{'list-group-item-action d-flex align-items-center tab-3': true, 'bg-light': page.id==currPageId}"
                 v-for="(page, pageIndex) in func.pages" @click="gotoPage(page.id)">
              <div class="flex-grow-1" @mouseover="openPagePreview($event, page.screen)">
                <i class="iconfont icon-page"></i>&nbsp;<small>{{ page.name }}</small>
              </div>

              <div :class="{'dropdown': true, 'invisible':hoverId!=page.id}">
                <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
                <ul class="dropdown-menu dropdown-menu-end" :id="'page'+page.id">
                  <li><a href="#" @click.stop="copyPage(page.id)" class="dropdown-item"><i class="iconfont icon-copy"></i> {{t('common.copy')}}</a></li>
                  <li><a href="#" class="dropdown-item text-danger" @click.stop="deletePage(page.id)"><i class="iconfont icon-remove"></i>
                    {{ t('page.deletePage') }}</a></li>
                  <li><a href="#" @click.stop="preview(currModuleId, page.id)" class="dropdown-item"><i class="iconfont icon-preview"></i> {{t('common.preview')}}</a></li>
                </ul>
              </div>
            </div>
          </template>
        </template>
      </template>
    </template>

    <div class="list-group-item-action d-flex align-items-center fw-bold"
         @mouseleave="mouseleave($event, 'popup', 'popup')"
         @mouseover="hoverId='popup'"
         @click="openPopup=!openPopup">
      <i :class="{'iconfont': true, 'icon-tree-open': openPopup, 'icon-tree-close': !openPopup}"></i>
      <i class="iconfont icon-popup"></i>&nbsp;<small>{{t('common.popup')}}</small>
    </div>
    <template v-if="openState || openPopup">
      <template v-for="(popup,index) in popups" :key="index">
        <div :class="{'tab-2 list-group-item-action d-flex justify-content-between align-items-center': true, 'bg-light': popup.id==currPageId}"
             @contextmenu="contextMenu($event, 'popup', popup.id)"
             @mouseleave="mouseleave($event, 'popup', popup.id)"
             @mouseover="hoverId=popup.id"
             @click="gotoPage(popup.id)">
          <div class="flex-grow-1" @mouseover="openPagePreview($event, popup.screen)">
            <i class="iconfont icon-popup"></i>&nbsp;<small>{{popup.name}}</small>
          </div>

          <div :class="{'dropdown': true, 'invisible':hoverId!=popup.id}">
            <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
            <ul class="dropdown-menu dropdown-menu-end" :id="'popup'+popup.id">
              <li><a href="#" @click.stop="copyPage(popup.id)" class="dropdown-item"><i class="iconfont icon-copy"></i> {{t('common.copy')}}</a></li>
              <li><a href="#" class="dropdown-item text-danger" @click.stop="deletePage(popup.id)"><i class="iconfont icon-remove"></i>
                {{ t('page.deletePage') }}</a></li>
              <li><a href="#" @click.stop="preview('', popup.id, 'popup')" class="dropdown-item"><i class="iconfont icon-preview"></i> {{t('common.preview')}}</a></li>
            </ul>
          </div>
        </div>
      </template>
    </template>

    <div class="list-group-item-action d-flex align-items-center fw-bold"
         @mouseleave="mouseleave($event, 'component', 'component')"
         @mouseover="hoverId='component'"
         @click="openComponent=!openComponent">
      <i :class="{'iconfont': true, 'icon-tree-open': openComponent, 'icon-tree-close': !openComponent}"></i>
      <i class="iconfont icon-uicomponent"></i>&nbsp;<small>{{t('common.uicomponent')}}</small>
    </div>
    <template v-if="openState || openComponent">
      <template v-for="(component,index) in components" :key="index">
        <div :class="{'tab-2 list-group-item-action d-flex justify-content-between align-items-center': true, 'bg-light': component.id==currPageId}"
             @contextmenu="contextMenu($event, 'component', component.id)"
             @mouseleave="mouseleave($event, 'component', component.id)"
             @mouseover="hoverId=component.id"
             @click="gotoPage(component.id)">
          <div class="flex-grow-1"  @mouseover="openPagePreview($event, component.screen)">
            <i class="iconfont icon-uicomponent"></i>&nbsp;<small>{{component.name}}</small>&nbsp;
            <span v-if="component.instance_count>0" class="text-muted ms-1" style="font-size: 11px">
              {{t('common.uicomponentInstance', [component.instance_count])}}
            </span>
          </div>

          <div :class="{'dropdown': true, 'invisible':hoverId!=component.id}">
            <i class="iconfont icon-more" @click.stop data-bs-toggle="dropdown" aria-expanded="false"></i>
            <ul class="dropdown-menu dropdown-menu-end" :id="'component'+component.id">
              <li><a href="#" class="dropdown-item text-danger" @click.stop="deletePage(component.id)"><i class="iconfont icon-remove"></i>
                {{ t('page.deletePage') }}</a></li>
              <li><a href="#" @click.stop="preview('', component.id, 'component')" class="dropdown-item"><i class="iconfont icon-preview"></i> {{t('common.preview')}}</a></li>
            </ul>
          </div>
        </div>
      </template>
    </template>
  </div>
  <div v-if="pagePreviewVisible && pagePreviewURL" ref="pagePreviewPopup" style="padding-left: 20px">
    <img style="width: 200px; height: 200px;object-fit:contain" class="bg-white"  :src="`${uploadApi+pagePreviewURL}`"/>
  </div>
</template>

<script lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import { YDJSStatic } from '@/lib/ydjs'
import { useRouter } from 'vue-router'
import { createPopper } from '@popperjs/core'
declare const YDJS: YDJSStatic
declare const $

export default {
  name: 'Project',
  setup (props: any, context: any) {
    const store = useStore()
    const currFunctionId = computed(() => store.state.design.function.id)
    const currModuleId = computed(() => store.state.design.module.id)
    const contextMenuDom = ref()
    const openState = ref(false)
    const openProject = ref(true)
    const openPopup = ref(true)
    const openComponent = ref(true)
    const pagePreviewVisible = ref(false)
    const contextLeft = ref(0)
    const contextTop = ref(0)
    const pagePreviewURL = ref('')
    const pagePreviewPopup = ref()
    const hoverId = ref('')
    const openModuleIds = ref({})
    const openFunctionIds = ref({})
    openModuleIds.value[currModuleId.value] = true
    openFunctionIds.value[currFunctionId.value] = true

    const currPageId = computed(() => store.state.design?.page?.meta?.id)
    const router = useRouter()
    const project = computed(() => store.state.design.project)
    const modules = ref<Array<any>>()
    const popups = ref<Array<any>>()
    const components = ref<Array<any>>()
    const api = ydhl.api
    const refresh = (cb) => {
      // modules.value = []
      ydhl.get('api/module', { uuid: project.value.id }, (rst) => {
        if (rst && rst.success) {
          modules.value = rst.data.modules
          popups.value = rst.data.popups
          components.value = rst.data.components
        } else {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        }
        if (cb) cb()
      })
    }
    onMounted(() => {
      refresh(null)
    })
    watch(currPageId, () => {
      refresh(null)
    })
    const gotoPage = (pid) => {
      router.push({
        path: '/',
        query: {
          uuid: pid
        }
      })
    }
    const toggleModule = (event, id) => {
      if (openModuleIds.value[id]) {
        delete openModuleIds.value[id]
      } else {
        openModuleIds.value[id] = true
      }
    }
    const toggleFunction = (event, func) => {
      if (openFunctionIds.value[func.id]) {
        delete openFunctionIds.value[func.id]
      } else {
        openFunctionIds.value[func.id] = true
      }
    }

    const deleteModuleConfirm = (item) => {
      YDJS.prompt(t('module.deleteModuleConfirm', [item.name]), '', 'input', (dialogId, value) => {
        ydhl.post(`module/${item.id}/delete.json`, { value: value }, [], function (rst) {
          if (!rst || !rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          YDJS.hide_dialog(dialogId)
          refresh(() => {
            store.commit('deleteModule', { moduleId: value })
            // 如果删除的是当前在编辑的module
            if (item.id === currModuleId.value && modules.value) {
              for (const m of modules.value) {
                if (m.functions && m.functions.length > 0) {
                  const f = m.functions.pop()
                  if (f.pages && f.pages.length > 0) {
                    for (const p of f.pages) {
                      window.location.href = '?uuid=' + p.id
                    }
                  }
                  return
                }
              }
              // 没有模块了跳转到后台
              window.location.href = api + `project/${project.value.id}/module`
            }
          })
        })
      })
    }
    const deleteFunctionConfirm = (item) => {
      YDJS.prompt(t('module.deleteModuleConfirm', [item.name]), '', 'input', (dialogId, value) => {
        ydhl.post(`module/${item.id}/delete.json`, { value: value }, [], function (rst) {
          if (!rst || !rst.success) {
            YDJS.toast(rst?.msg || 'Oops', YDJS.ICON_ERROR)
            return
          }
          YDJS.hide_dialog(dialogId)
          refresh(() => {
            store.commit('deleteModule', { moduleId: value })
            // 如果删除的是当前在编辑的module
            if (item.id === currModuleId.value && modules.value) {
              for (const m of modules.value) {
                if (m.functions && m.functions.length > 0) {
                  const f = m.functions.pop()
                  if (f.pages && f.pages.length > 0) {
                    for (const p of f.pages) {
                      window.location.href = '?uuid=' + p.id
                    }
                  }
                  return
                }
              }
              // 没有模块了跳转到后台
              window.location.href = api + `project/${project.value.id}/module`
            }
          })
        })
      })
    }
    const addModule = (project) => {
      const dialogid = YDJS.dialog(api + 'project/' + project.id + '/addmodule'
        , ''
        , t('module.addModule')
        , YDJS.SIZE_SMALL
        , YDJS.BACKDROP_STATIC
        , []
        , undefined
        , (rst) => {
          YDJS.hide_dialog(dialogid)
          refresh(null)
        })
    }
    const editFunction = (item) => {
      const dialogid = YDJS.dialog(api + 'function/' + item.id + '/edit'
        , ''
        , t('common.edit')
        , YDJS.SIZE_SMALL
        , YDJS.BACKDROP_STATIC
        , []
        , undefined
        , (rst) => {
          YDJS.hide_dialog(dialogid)
          // console.log(rst)
          if (!rst || !rst.success) {
            YDJS.toast(rst?.msg || t('common.operationFail'), YDJS.ICON_ERROR)
          }
          refresh(null)
        })
    }
    const editModule = (item) => {
      const dialogid = YDJS.dialog(api + 'module/' + item.id + '/edit'
        , ''
        , t('common.edit')
        , YDJS.SIZE_SMALL
        , YDJS.BACKDROP_STATIC
        , []
        , undefined
        , (rst) => {
          YDJS.hide_dialog(dialogid)
          // console.log(rst)
          if (!rst || !rst.success) {
            YDJS.toast(rst?.msg || t('common.operationFail'), YDJS.ICON_ERROR)
          }
          refresh(null)
        })
    }
    const addFunction = (item) => {
      const dialogid = YDJS.dialog(api + 'module/' + item.id + '/addfunction'
        , ''
        , t('module.addFunction')
        , YDJS.SIZE_SMALL
        , YDJS.BACKDROP_STATIC
        , []
        , undefined
        , (rst) => {
          YDJS.hide_dialog(dialogid)
          refresh(null)
        })
    }
    const gotoFunction = (funcid: string) => {
      router.push({
        path: '/',
        query: {
          functionId: funcid
        }
      })
    }

    const collapseAll = () => {
      openState.value = false
      openProject.value = false
      openPopup.value = false
      openComponent.value = false
      openModuleIds.value = {}
      openFunctionIds.value = {}
    }
    const expandAll = () => {
      openState.value = true
      openProject.value = true
      openPopup.value = true
      openComponent.value = true
      if (!modules.value) return
      for (const module of modules.value) {
        openModuleIds.value[module.id] = true
        for (const func of module.functions) {
          openFunctionIds.value[func.id] = true
        }
      }
    }
    const openPagePreview = (event, src: any) => {
      // return
      // pagePreviewVisible.value = true
      //
      // _.debounce(() => {
      //   console.log('test')
      //   if (!pagePreviewVisible.value) return
      //   pagePreviewURL.value = src
      //   ydhl.openPopper(event.fromElement, pagePreviewPopup, 'right-start')
      // }, 2000)()
    }
    const mouseleave = (event, type, id) => {
      pagePreviewVisible.value = false
      hoverId.value = ''
      const el = document.getElementById(type + id)
      $(el).removeClass('show')
      $(el).removeAttr('style')
      $(el).removeAttr('data-popper-placement')
    }
    const contextMenu = (event, type: any, id: any) => {
      const el = document.getElementById(type + id)
      if (event.button !== 2 || el === null) return false
      pagePreviewVisible.value = false

      event.returnValue = false
      event.stopPropagation()
      event.preventDefault()
      $(el).addClass('show')
      hoverId.value = id
      createPopper(event.target, el, {
        placement: 'auto',
        modifiers: [
          {
            name: 'offset',
            options: {
              offset: [0, 0]
            }
          }
        ]
      })
    }

    const copyPage = (pageid) => {
      // console.log(props)
      store.commit('copyPage', { pageid })
    }
    const positionToPage = function () {
      collapseAll()
      openModuleIds.value[currModuleId.value] = true
      openFunctionIds.value[currFunctionId.value] = true
      openProject.value = true
    }
    const deletePage = function (pageid) {
      ydhl.confirm(t('page.deletePageConfirm'), t('page.deletePage'), t('common.cancel')).then((dialogid: any) => {
        ydhl.closeLoading(dialogid)
        store.commit('deletePage', { pageid })
        refresh(null)
      })
    }
    const preview = (moduleId: any, pageId: any, type = '') => {
      const link = document.getElementById('openlink')
      if (link) {
        link.setAttribute('href', ydhl.api + 'preview/' + project.value.id + '?type=' + type + '&module=' + moduleId + '&page=' + pageId)
        link.click()
      }
    }
    const { t } = useI18n()
    return {
      t,
      currFunctionId,
      currModuleId,
      project,
      modules,
      popups,
      components,
      api,
      uploadApi: ydhl.uploadApi,
      contextMenuDom,
      currPageId,
      openModuleIds,
      openState,
      openComponent,
      openFunctionIds,
      pagePreviewVisible,
      pagePreviewURL,
      openProject,
      hoverId,
      contextLeft,
      contextTop,
      openPopup,
      openPagePreview,
      copyPage,
      preview,
      deletePage,
      pagePreviewPopup,
      timestamp: Date.parse((new Date()).toTimeString()),
      contextMenu,
      mouseleave,
      gotoPage,
      deleteModuleConfirm,
      deleteFunctionConfirm,
      addModule,
      editFunction,
      editModule,
      addFunction,
      toggleFunction,
      toggleModule,
      gotoFunction,
      collapseAll,
      positionToPage,
      expandAll
    }
  }
}
</script>
<style scoped>
.tab-1{
  padding-left: 16px;
}
.tab-2{
  padding-left: 32px;
}
.tab-3{
  padding-left: 64px;
}
</style>
