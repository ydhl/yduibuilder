<template>
  <table class="table align-middle table-borderless table-striped table-hover table-sm mb-0 mt-1">
    <thead>
    <tr>
      <th>{{t('api.request.param.queryName')}}</th>
      <th>{{t('api.dataStruct.title')}}</th>
      <th>{{t('api.request.param.required')}}</th>
      <th>{{t('api.request.param.type')}}</th>
      <th>{{t('api.request.param.sample')}}</th>
      <th>
        <lay-tooltip :content="t('api.request.param.fixedDesc')">{{t('api.request.param.fixed')}}?</lay-tooltip>
      </th>
      <th>{{t('api.request.param.desc')}}</th>
    </tr>
    </thead>
    <tbody>
    <template v-for="(param,index) in params" :key="index">
    <tr v-if="param.name">
      <td>{{param.name}}</td>
      <td>{{param.title}}</td>
      <td>{{param.required ? 'Y' : 'N'}}</td>
      <td>
        <span :class="'param-'+param.type">{{param.type}}</span>
      </td>
      <td>
        <template v-if="param.type!='array' && param.type!='file'">{{param.sample}}</template>
        <table v-if="param.type=='array'">
          <tr v-for="(sample, sampleindex) in param.sample" :key="sampleindex">
            <td class="pe-3">{{sampleindex}}</td>
            <td>{{param.sample[sampleindex]}}</td>
          </tr>
        </table>
      </td>
      <td>{{param.action}}</td>
      <td>{{param.comment}}</td>
    </tr>
    </template>
    </tbody>
  </table>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import { DataStructParam } from '@/store/model'

export default {
  name: 'ParamReadonly',
  emits: ['update'],
  props: {
    param: Object
  },
  components: {},
  setup (props: any, context: any) {
    const importPopupVisible = ref(false)
    const { t } = useI18n()
    const params = computed<Array<DataStructParam>>(() => props.param || [])

    return {
      t,
      params,
      importPopupVisible
    }
  }
}
</script>
