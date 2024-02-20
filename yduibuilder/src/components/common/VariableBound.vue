<template>
  <div class="card mb-3">
    <div class="card-header">{{t('variable.localScope')}}</div>
    <div class="card-body">
      <template v-if="localVariables && localVariables.length > 0">
        <template v-for="(localVariable, index) in localVariables" :key="index">
          <ModelItemConnect @updateCheckedUuid="updateLocal" :hide-sub-data="hideSubData" :checked-uuid="checkedUuid" path=""
                         :is-check="true" :index="0" :model="localVariable" :intent="0"></ModelItemConnect>
        </template>
      </template>
      <template v-else>
        {{t('common.empty')}}
      </template>
    </div>
  </div>
  <div class="card mb-3">
    <div class="card-header">{{t('variable.pageScope')}}</div>
    <div class="card-body">
      <template v-if="pageVariables && pageVariables.length > 0">
        <template v-for="(pageVariable, index) in pageVariables" :key="index">
          <ModelItemConnect @updateCheckedUuid="updatePage" :hide-sub-data="hideSubData" :checked-uuid="checkedUuid" path="page"
                         :is-check="true" :index="0" :model="pageVariable" :intent="0"></ModelItemConnect>
        </template>
      </template>
      <div class="text-muted" v-else>
        {{t('common.empty')}}
      </div>
    </div>
  </div>
  <div class="card">
    <div class="card-header">{{t('variable.globalScope')}}</div>
    <div class="card-body">
      <template v-if="globalVariables && globalVariables.length > 0">
        <template v-for="(globalVariable, index) in globalVariables" :key="index">
          <ModelItemConnect @updateCheckedUuid="updateGlobal" :hide-sub-data="hideSubData" :checked-uuid="checkedUuid" path="global"
                         :is-check="true" :index="0" :model="globalVariable" :intent="0"></ModelItemConnect>
        </template>
      </template>
      <div class="text-muted" v-else>
        {{t('common.empty')}}
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import ModelItemConnect from '@/components/common/ModelItemConnect.vue'
import { onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'VariableBound',
  components: { ModelItemConnect },
  props: {
    localVariables: Array,
    hideSubData: Boolean,
    checkedUuid: String,
    pageUuid: String
  },
  emits: ['updateCheckedUuid'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const pageVariables = ref()
    const globalVariables = ref()
    const updateLocal = (path, data) => {
      context.emit('updateCheckedUuid', { scope: 'local', path, data })
    }
    const updatePage = (path, data) => {
      context.emit('updateCheckedUuid', { scope: 'page', path, data })
    }
    const updateGlobal = (path, data) => {
      context.emit('updateCheckedUuid', { scope: 'global', path, data })
    }
    const loadVariables = () => {
      ydhl.get('api/data.json', { page_uuid: props.pageUuid }, (rst) => {
        pageVariables.value = rst.data.page
        globalVariables.value = rst.data.global
      })
    }
    onMounted(() => {
      loadVariables()
    })
    return {
      t,
      pageVariables,
      globalVariables,
      updateLocal,
      updatePage,
      updateGlobal
    }
  }
}
</script>
