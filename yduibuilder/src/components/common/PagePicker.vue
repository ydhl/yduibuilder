<template>
  <div class="list-group list-group-flush">
    <div class="list-group-item-action d-flex justify-content-between align-items-center"
         @click="openProject=!openProject">
      <div class="d-flex align-items-center fw-bold">
        <i :class="{'iconfont': true, 'icon-tree-open': openProject, 'icon-tree-close': !openProject}"></i>
        <i :class="{'iconfont': true,'icon-folder':!openProject,'icon-folder-open':openProject}"></i>&nbsp;<small>{{project.name}}</small>
      </div>
      <div class="d-flex align-items-center">
        <div v-if="!openState" @click="expandAll"><i class="iconfont icon-expandall hover-primary"></i></div>
        <div v-if="openState" @click="collapseAll"><i class="iconfont icon-collapseall hover-primary"></i></div>
      </div>
    </div>

    <template v-if="(openState || openProject) && !hidePage">
      <template v-for="(module,index) in modules" :key="index">
        <div class="tab-1 list-group-item-action d-flex justify-content-between align-items-center"
             @click="toggleModule($event, module.id)">
          <div class="flex-grow-1">
            <i :class="{'iconfont': true, 'icon-tree-open': openModuleIds[module.id], 'icon-tree-close': !openModuleIds[module.id]}"></i>
            <i :class="{'iconfont': true, 'icon-folder':!openModuleIds[module.id],'icon-folder-open': openModuleIds[module.id]}"></i>&nbsp;<small>{{module.name}}</small>
          </div>
        </div>

        <!--function-->
        <template :key="index" v-for="(func, index) in module.functions">
          <div @click="toggleFunction($event, func)" v-if="openModuleIds[module.id]" :data-uuid="func.id"
               class="list-group-item-action d-flex align-items-center tab-2">
            <div class="flex-grow-1">
              <i :class="{'iconfont': true, 'icon-tree-close': !openFunctionIds[func.id], 'icon-tree-open': openFunctionIds[func.id]}"></i>
              <i :class="{'iconfont': true, 'icon-folder':!openFunctionIds[func.id],'icon-folder-open': openFunctionIds[func.id]}"></i>&nbsp;<small>{{func.name}}</small>
            </div>
          </div>
          <!-- page -->
          <template v-if="openFunctionIds[func.id] && openModuleIds[module.id]">
            <div :key="pageIndex"
                 :class="{'list-group-item-action d-flex align-items-center tab-3': true, 'bg-light': page.id==checkedPageUuid}"
                 v-for="(page, pageIndex) in func.pages">
              <div class="flex-grow-1" @click="checkPage(module, func, page,'page')">
                <input style="margin-left:1px;margin-right: 6px" type="checkbox" :checked="page.id==checkedPageUuid"><small>{{ page.name }}</small>
              </div>
            </div>
          </template>
        </template>
      </template>
    </template>
    <div v-if="!hidePopup" class="list-group-item-action d-flex align-items-center fw-bold"
         @click="openPopup=!openPopup">
      <i :class="{'iconfont': true, 'icon-tree-open': openPopup, 'icon-tree-close': !openPopup}"></i>
      <i class="iconfont icon-popup"></i>&nbsp;<small>{{t('common.popup')}}</small>
    </div>
    <template v-if="(openState || openPopup) && !hidePopup">
      <template v-for="(popup,index) in popups" :key="index">
        <div @click="checkPage(null, null,popup, 'popup')"
              :class="{'tab-1 list-group-item-action d-flex justify-content-between align-items-center': true, 'bg-light': popup.id==checkedPageUuid}">
          <input style="margin-left:1px;margin-right: 6px" type="checkbox" :checked="popup.id==checkedPageUuid">
          <div class="flex-grow-1">
            <small>{{popup.name}}</small>
          </div>
        </div>
      </template>
    </template>

    <div v-if="!hideSubpage" class="list-group-item-action d-flex align-items-center fw-bold"
         @click="openSubpage=!openSubpage">
      <i :class="{'iconfont': true, 'icon-tree-open': openSubpage, 'icon-tree-close': !openSubpage}"></i>
      <i class="iconfont icon-popup"></i>&nbsp;<small>{{t('common.subPage')}}</small>
    </div>
    <template v-if="(openState || openSubpage) && !hideSubpage">
      <template v-for="(subpage,index) in subpages" :key="index">
        <div @click="checkPage(null, null,subpage, 'subpage')"
             :class="{'tab-1 list-group-item-action d-flex justify-content-between align-items-center': true, 'bg-light': subpage.id==checkedPageUuid}">
          <input style="margin-left:1px;margin-right: 6px" type="checkbox" :checked="subpage.id==checkedPageUuid">
          <div class="flex-grow-1">
            <small>{{subpage.name}}</small>
          </div>
        </div>
      </template>
    </template>
  </div>
</template>

<script lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'

export default {
  name: 'PagePicker',
  props: {
    defualtPageUuid: String,
    pageTypes: {
      type: Array,
      default: () => ['page', 'popup']
    }
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const store = useStore()
    const checkedPageUuid = computed(() => props.defualtPageUuid)

    const openState = ref(false)
    const openProject = ref(true)
    const openPopup = ref(true)
    const openSubpage = ref(true)
    const hidePage = computed(() => {
      return props.pageTypes?.indexOf('page') === -1
    })
    const hidePopup = computed(() => {
      return props.pageTypes?.indexOf('popup') === -1
    })
    const hideSubpage = computed(() => {
      return props.pageTypes?.indexOf('subpage') === -1
    })

    const openModuleIds = ref({})
    const openFunctionIds = ref({})

    const project = computed(() => store.state.design.project)
    const modules = ref<Array<any>>([])
    const popups = ref<Array<any>>([])
    const subpages = ref<Array<any>>([])
    const api = ydhl.api
    const refresh = (cb) => {
      // modules.value = []
      ydhl.get('api/module.json', { hide_page: hidePage.value ? 1 : 0, hide_popup: hidePopup.value ? 1 : 0, hide_sub_page: hideSubpage.value ? 1 : 0, uuid: project.value.id, curr_page_uuid: props.defualtPageUuid }, (rst) => {
        if (rst && rst.success) {
          modules.value = rst.data.modules
          popups.value = rst.data.popups
          subpages.value = rst.data.subpages

          openModuleIds.value[rst.data.curr_module_uuid] = true
          openFunctionIds.value[rst.data.curr_function_uuid] = true
        } else {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        }
        if (cb) cb()
      })
    }
    onMounted(() => {
      refresh(null)
    })

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

    const collapseAll = () => {
      openState.value = false
      openProject.value = false
      openPopup.value = false
      openSubpage.value = false
      openModuleIds.value = {}
      openFunctionIds.value = {}
    }
    const expandAll = () => {
      openState.value = true
      openProject.value = true
      openPopup.value = true
      openSubpage.value = true
      if (!modules.value) return
      for (const module of modules.value) {
        openModuleIds.value[module.id] = true
        for (const func of module.functions) {
          openFunctionIds.value[func.id] = true
        }
      }
    }
    const checkPage = (module, func, page: any, pageType) => {
      const path:any = []
      if (module) path.push(module.name)
      if (func) path.push(func.name)
      context.emit('update', (path.length ? path.join('/') + '/' : '') + page.name, page.id, pageType)
    }

    const { t } = useI18n()
    return {
      t,
      project,
      modules,
      api,
      openModuleIds,
      checkedPageUuid,
      openState,
      openFunctionIds,
      openProject,
      openPopup,
      openSubpage,
      popups,
      subpages,
      hidePage,
      hidePopup,
      hideSubpage,
      timestamp: Date.parse((new Date()).toTimeString()),
      toggleFunction,
      checkPage,
      toggleModule,
      collapseAll,
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
  padding-left: 48px;
}
</style>
