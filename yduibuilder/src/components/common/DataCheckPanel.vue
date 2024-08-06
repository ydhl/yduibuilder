<template>
  <ul class="nav nav-tabs">
    <li class="nav-item">
      <a :class="{'nav-link pt-1 pb-1':true, 'active':scope=='local'}" href="javascript:void(0)" @click="scope='local'">{{t('variable.localScope')}}</a>
    </li>
    <li class="nav-item">
      <a :class="{'nav-link pt-1 pb-1':true, 'active':scope=='page'}" href="javascript:void(0)" @click="scope='page'">{{t('variable.pageScope')}}</a>
    </li>
    <li class="nav-item">
      <a :class="{'nav-link pt-1 pb-1':true, 'active':scope=='global'}" href="javascript:void(0)" @click="scope='global'">{{t('variable.globalScope')}}</a>
    </li>
  </ul>
  <div v-if="scope=='local'" class="mt-2">
    <template v-if="localVariables && localVariables.length > 0">
      <template v-for="(localVariable, index) in localVariables" :key="index">
        <DataConnect @checked="updateLocal" :checked-uuid="checkedUuid" path="" :root-uuid="localVariable.uuid"
                       :is-check="true" :index="0" :model="localVariable" :intent="0"></DataConnect>
      </template>
    </template>
    <div class="text-muted" v-else>
      {{t('common.empty')}}
    </div>
  </div>

  <div v-if="scope=='page'" class="mt-2">
    <template v-if="pageVariables && pageVariables.length > 0">
      <template v-for="(pageVariable, index) in pageVariables" :key="index">
        <DataConnect @checked="updatePage" :checked-uuid="checkedUuid" path="page" :root-uuid="pageVariable.uuid"
                          :is-check="true" :index="0" :model="pageVariable" :intent="0"></DataConnect>
      </template>
    </template>
    <div class="text-muted" v-else>
      {{t('common.empty')}}
    </div>
  </div>

  <div v-if="scope=='global'" class="mt-2">
    <template v-if="globalVariables && globalVariables.length > 0">
      <template v-for="(globalVariable, index) in globalVariables" :key="index">
        <DataConnect @checked="updateGlobal" :checked-uuid="checkedUuid" path="global" :root-uuid="globalVariable.uuid"
                          :is-check="true" :index="0" :model="globalVariable" :intent="0"></DataConnect>
      </template>
    </template>
    <div class="text-muted" v-else>
      {{t('common.empty')}}
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { DataStruct } from '@/store/model'
// 列出变量，选择绑定
export default {
  name: 'DataCheckPanel',
  props: {
    localVariables: Array,
    checkedUuid: String,
    pageUuid: String
  },
  emits: ['updateChecked'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const pageVariables = ref()
    const scope = ref('page')
    const globalVariables = ref()
    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const updateLocal = (path, data: DataStruct, rootDataId) => {
      context.emit('updateChecked', { scope: 'local', path, data, rootDataId })
    }
    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const updatePage = (path, data: DataStruct, rootDataId) => {
      context.emit('updateChecked', { scope: 'page', path, data, rootDataId })
    }
    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const updateGlobal = (path, data: DataStruct, rootDataId) => {
      context.emit('updateChecked', { scope: 'global', path, data, rootDataId })
    }
    const loadVariables = () => {
      ydhl.get('api/data.json', { page_uuid: props.pageUuid }, (rst) => {
        pageVariables.value = rst.data?.page || []
        globalVariables.value = rst.data?.global || []
      })
    }
    onMounted(() => {
      loadVariables()
    })
    return {
      t,
      scope,
      pageVariables,
      globalVariables,
      updateLocal,
      updatePage,
      updateGlobal
    }
  }
}
</script>
