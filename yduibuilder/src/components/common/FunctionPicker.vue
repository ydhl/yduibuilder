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

    <template v-if="(openState || openProject)">
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
          <div v-if="openModuleIds[module.id]" :data-uuid="func.id"
               class="list-group-item-action d-flex align-items-center tab-2">
            <div class="flex-grow-1" @click="checkFunc(module, func)">
              <input style="margin-left:1px;margin-right: 6px" :type="single?'radio':'checkbox'" :checked="func.id==checkedFuncUuid"><small>{{ func.name }}</small>
            </div>
          </div>
        </template>
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
  name: 'FunctionPicker',
  props: {
    defualtFuncUuid: String,
    defualtModuleUuid: String,
    single: Boolean,
    data: Array
  },
  emits: ['update'],
  setup (props: any, context: any) {
    const store = useStore()
    const checkedFuncUuid = ref(props.defualtFuncUuid)

    const openState = ref(false)
    const openProject = ref(true)
    const openModuleIds = ref({})
    const openFunctionIds = ref({})

    const project = computed(() => store.state.design.project)
    const modules = ref<Array<any>>([])
    const api = ydhl.api

    onMounted(() => {
      if (props.data) {
        modules.value = props.data
        openModuleIds.value[props.defualtModuleUuid] = true
        openFunctionIds.value[props.defualtFuncUuid] = true
        return
      }
      ydhl.get('api/module.json', { hide_page: 1, hide_popup: 1, hide_sub_page: 1, uuid: project.value.id, curr_page_uuid: props.defualtPageUuid }, (rst) => {
        if (rst && rst.success) {
          modules.value = rst.data.modules

          openModuleIds.value[rst.data.curr_module_uuid] = true
          openFunctionIds.value[rst.data.curr_function_uuid] = true
        } else {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
        }
      })
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
      openModuleIds.value = {}
      openFunctionIds.value = {}
    }
    const expandAll = () => {
      openState.value = true
      openProject.value = true
      if (!modules.value) return
      for (const module of modules.value) {
        openModuleIds.value[module.id] = true
        for (const func of module.functions) {
          openFunctionIds.value[func.id] = true
        }
      }
    }
    const checkFunc = (module, func) => {
      checkedFuncUuid.value = func.id
      context.emit('update', module, func)
    }

    const { t } = useI18n()
    return {
      t,
      project,
      modules,
      api,
      openModuleIds,
      checkedFuncUuid,
      openState,
      openFunctionIds,
      openProject,
      timestamp: Date.parse((new Date()).toTimeString()),
      toggleFunction,
      checkFunc,
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
  padding-left: 48px;
}
</style>
