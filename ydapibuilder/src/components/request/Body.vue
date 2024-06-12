<template>
  <ul class="nav nav-pills mb-3">
    <li class="nav-item" v-for="(menu, index) in menus" :key="index">
      <a :class="{'nav-link p-1 me-2 ': true, 'active text-light': activeMenuIndex == index, 'text-dark': activeMenuIndex !== index}"
         @click="changeTab(index)" href="javascript:void(0)">{{ menu.name }}</a>
    </li>
  </ul>
  <keep-alive>
    <div v-if="menus[activeMenuIndex].name === 'none'" class="d-flex text-muted justify-content-center align-items-center" style="height: 200px">
      {{t('api.request.body.none')}}
    </div>
  </keep-alive>
  <keep-alive>
    <Param :has-file-type="true" :param="myApi.request.body?.['form-data']" @update="update" v-if="menus[activeMenuIndex].name==='form-data'"></Param>
  </keep-alive>
  <keep-alive>
    <Param :param="myApi.request.body?.['x-www-form-urlencoded']" @update="update" v-if="menus[activeMenuIndex].name==='x-www-form-urlencoded'"></Param>
  </keep-alive>
  <keep-alive>
    <DataJSON :json-data="myApi.request?.body?.json" @update="update" v-if="menus[activeMenuIndex].name==='json'"></DataJSON>
  </keep-alive>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onActivated, onMounted, ref, watch } from 'vue'
import Param from '@/components/request/Param.vue'
import DataJSON from '@/components/request/DataJSON.vue'

export default {
  name: 'Body',
  emits: ['update'],
  props: {
    api: Object
  },
  components: {
    Param,
    DataJSON
    // ,
    // DataXML,
    // DataRAW,
    // DataBinary,
    // DataGraphQL,
    // DataMSGPack
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const menus = [
      { name: 'none' },
      { name: 'form-data', page: 'Param' },
      { name: 'x-www-form-urlencoded', page: 'Param' },
      { name: 'json', page: 'DataJSON' }
      // ,
      // { name: 'xml', page: 'DataXML' },
      // { name: 'raw', page: 'DataRAW' },
      // { name: 'binary', page: 'DataBinary' },
      // { name: 'GraphQl', page: 'DataGraphQL' },
      // { name: 'msgpack', page: 'DataMSGPack' }
    ]
    const myApi = computed(() => props.api)
    const activeMenuIndex = ref(0)
    watch(() => props.api.requestBodyType, (v) => {
      activeMenuIndex.value = menus.findIndex((item) => item.name === v)
      if (activeMenuIndex.value <= -1) activeMenuIndex.value = 0
    }, { immediate: true })
    const changeTab = (index: number) => {
      activeMenuIndex.value = index
      myApi.value.requestBodyType = menus[activeMenuIndex.value].name
      const type = menus[activeMenuIndex.value].name
      if (type === 'none') {
        update('')
      }
    }
    const update = (info) => {
      const type = myApi.value.requestBodyType || 'none'
      if (!myApi.value.request.body) {
        myApi.value.request.body = {}
      }
      myApi.value.request.body[type] = info
      context.emit('update', info, type)
    }
    onMounted(() => {
      console.log('Body.vue onMounted')
    })
    onActivated(() => {
      console.log('Body.vue onActivated')
      if (!myApi.value.request.body) {
        myApi.value.request.body = {}
        myApi.value.requestBodyType = 'none'
      }
    })
    return {
      t,
      update,
      changeTab,
      myApi,
      activeMenuIndex,
      menus
    }
  }
}
</script>
