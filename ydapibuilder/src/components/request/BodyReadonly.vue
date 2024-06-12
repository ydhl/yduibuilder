<template>
  <span class="bg-primary text-white badge">{{myApi.requestBodyType}}</span>
  <keep-alive>
    <div v-if="myApi.requestBodyType === 'none'" class="d-flex text-muted justify-content-center align-items-center" style="height: 200px">
      {{t('api.request.body.none')}}
    </div>
  </keep-alive>
  <keep-alive>
    <Param :param="myApi.request.body?.['form-data']" v-if="myApi.requestBodyType==='form-data'"></Param>
  </keep-alive>
  <keep-alive>
    <Param :param="myApi.request.body?.['x-www-form-urlencoded']" v-if="myApi.requestBodyType==='x-www-form-urlencoded'"></Param>
  </keep-alive>
  <keep-alive>
    <DataJSON :json-data="myApi.request?.body?.json" v-if="myApi.requestBodyType==='json'"></DataJSON>
  </keep-alive>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onActivated } from 'vue'
import Param from '@/components/request/ParamReadonly.vue'
import DataJSON from '@/components/request/DataJSONReadonly.vue'

export default {
  name: 'BodyReadonly',
  emits: ['update'],
  props: {
    api: Object
  },
  components: {
    Param,
    DataJSON
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myApi = computed(() => props.api)

    onActivated(() => {
      if (!myApi.value.request.body) {
        myApi.value.request.body = {}
        myApi.value.requestBodyType = 'none'
      }
    })
    return {
      t,
      myApi
    }
  }
}
</script>
