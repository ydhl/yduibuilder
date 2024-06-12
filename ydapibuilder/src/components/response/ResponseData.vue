<template>
  <div class="row mb-3">
    <div class="col-3 d-flex align-items-center">
      <div>{{t('api.response.contentName')}}:</div>
      <input type="text" class="form-control-sm form-control ms-2" v-model="myResponse.name">
    </div>
    <div class="col-2 d-flex align-items-center">
      <div class="text-nowrap">{{t('api.response.httpCode')}}:</div>
      <select class="form-select form-select-sm ms-2" v-model.number="myResponse.code">
        <option v-for="(desc, code, index) in codes" :value="code" :key="index">{{code}} - {{desc}}</option>
      </select>
    </div>
    <div class="col-3 d-flex align-items-center">
      <div class="text-nowrap">{{t('api.response.contentFormat')}}:</div>
      <select class="form-select form-select-sm ms-2" v-model="myResponse.contentType">
        <option v-for="(format, contentType, index) in formats" :value="contentType" :key="index">{{contentType}}</option>
      </select>
    </div>
    <div class="col d-flex align-items-center">Content-type: {{formats[myResponse.contentType||'JSON']}}</div>
    <div class="col d-flex justify-content-end" v-if="canDelete">
      <button type="button" class="btn rounded-2 btn-outline-danger btn-xs">{{t('common.delete')}}</button>
    </div>
  </div>
  <keep-alive>
    <DataJSON :json-data="myResponse.body" @update="updateBody" v-if="myResponse.contentType==='JSON'"></DataJSON>
  </keep-alive>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import DataJSON from '@/components/request/DataJSON.vue'
import { APIResponse, ResponseCode, ResponseFormat } from '@/store/model'

export default {
  name: 'ResponseData',
  components: { DataJSON },
  props: {
    response: Object,
    canDelete: Boolean
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const contentType = ref('JSON')
    const myResponse = computed<APIResponse>(() => props.response)
    const codes = ResponseCode
    const formats = ResponseFormat

    const updateBody = (info) => {
      myResponse.value.body = info
    }
    return {
      t,
      formats,
      contentType,
      myResponse,
      codes,
      updateBody
    }
  }
}
</script>
