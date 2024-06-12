<template>
  <div class="row mb-3">
    <div class="col-3 d-flex align-items-center">
      <div class="fw-bold pe-2">{{t('api.response.contentName')}}:</div>
      <div class="form-control-plaintext">{{myResponse.name}}</div>
    </div>
    <div class="col-2 d-flex align-items-center">
      <div class="fw-bold text-nowrap pe-2">{{t('api.response.httpCode')}}: </div>
      <div class="form-control-plaintext">{{myResponse.code}}</div>
    </div>
    <div class="col-3 d-flex align-items-center">
      <div class="fw-bold text-nowrap pe-2">{{t('api.response.contentFormat')}}: </div>
      <div class="form-control-plaintext">{{myResponse.contentType}}</div>
    </div>
    <div class="col d-flex align-items-center"><div class="fw-bold text-nowrap pe-2">Content-type: </div>{{formats[myResponse.contentType||'JSON']}}</div>
  </div>
  <keep-alive>
    <DataJSON :json-data="myResponse.body" v-if="myResponse.contentType==='JSON'"></DataJSON>
  </keep-alive>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import DataJSON from '@/components/request/DataJSONReadonly.vue'
import { APIResponse, ResponseCode, ResponseFormat } from '@/store/model'

export default {
  name: 'ResponseDataReadonly',
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

    return {
      t,
      formats,
      contentType,
      myResponse,
      codes
    }
  }
}
</script>
