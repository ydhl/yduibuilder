<template>
  <h3 class="row">
    <div class="col">
      {{api.name}}
    </div>
    <div class="col d-flex justify-content-end">
      <button type="button" class="btn btn-primary" @click="edit">{{t('common.edit')}}</button>
    </div>
  </h3>
  <div class="row mb-3">
    <div class="col">
      <span :class="'api-method-' + api.method">{{api.method}}</span>&nbsp;{{api.path}}
    </div>
  </div>
  <div class="row mb-3">
    <div class="col">
      <label class="fw-bold">{{t("api.folder")}}</label>
      <div class="form-control-plaintext">{{ api.folder?.name || '' }}</div>
    </div>
    <div class="col">
      <label class="fw-bold">{{t("api.status.name")}}</label>
      <div class="form-control-plaintext">{{ t('api.status.' + api.status) }}</div>
    </div>
    <div class="col">
      <label class="fw-bold">{{t("api.version")}}</label>
      <div class="form-control-plaintext">{{api.major}}.{{api.minor}}.{{api.revision}}</div>
    </div>
    <div class="col">
      <label class="fw-bold">{{t("api.responsible")}}</label>
      <div class="form-control-plaintext">{{api.responsible.name}}</div>
    </div>
    <div class="col">
      <label class="fw-bold">{{t("api.label")}}</label>
      <div class="form-control-plaintext">
        <template v-if="api.label">
          <span v-for="(value,index) in api.label" :key="index" class="bg-light">{{value}}</span>
        </template>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12 position-relative">
      <label class="fw-bold">{{t("api.comment")}}</label>
      <div class="form-control-plaintext">{{api.comment}}</div>
    </div>
  </div>

  <h3 class="mb-2">{{t('api.request.name')}}</h3>
  <div class="card mb-3 border-0">
    <div class="card-header p-0 ps-2 bg-white">
      <ul class="nav nav-tabs  border-bottom-0 position-relative" style="top: 1px">
        <li class="nav-item" v-for="(requestTab, index) in requestTabs" :key="index">
          <a style="line-height: 14px" :class="{'nav-link text-dark': true, 'active fw-bold': requestTabIndex===index}" @click="requestTabIndex = index" href="javascript:void(0)">
            {{t('api.request.'+requestTab.name+'.name')}}
            <span class="badge bg-primary p-0 ps-1 pe-1" v-if="badge[requestTab.name]" v-html="badge[requestTab.name]"></span>
          </a>
        </li>
      </ul>
    </div>
    <div class="card-body  border-1 border rounded-bottom">
      <KeepAlive>
        <Param v-if="requestTabs[requestTabIndex].name === 'cookie'" :param="api.request.cookie"></Param>
      </KeepAlive>
      <KeepAlive>
        <Param v-if="requestTabs[requestTabIndex].name === 'header'" :param="api.request.header"></Param>
      </KeepAlive>
      <KeepAlive>
        <div>
          <template v-if="requestTabs[requestTabIndex].name === 'param'" >
            <div class="text-muted mb-3">{{t('api.dataStruct.queryParam')}}</div>
            <Param :param="api.request.param"></Param>
          </template>
          <template v-if="requestTabs[requestTabIndex].name === 'param' && api.request.path && api.request.path.length>0" >
            <div class="text-muted mt-3">{{t('api.dataStruct.pathParam')}}</div>
            <Param :param="api.request.path"></Param>
          </template>
        </div>
      </KeepAlive>
      <KeepAlive>
        <Body v-if="requestTabs[requestTabIndex].name === 'body'" :api="api"></Body>
      </KeepAlive>
    </div>
  </div>
  <h3 class="mb-2">{{t('api.response.name')}}</h3>
  <div class="card border-0">
    <div class="card-header bg-white p-0 ps-2">
      <ul class="nav nav-tabs border-bottom-0 position-relative" style="top: 1px">
        <li class="nav-item" v-for="(response, index) in responses" :key="index">
          <a style="line-height: 14px" :class="{'nav-link text-dark': true, 'active fw-bold': responseTabIndex===index}" @click="responseTabIndex=index" href="javascript:void(0)">{{response.name}}({{response.code}})</a>
        </li>
      </ul>
    </div>
    <div class="card-body border-1 border rounded-bottom border-top-0">
      <ResponseData v-if="responses[responseTabIndex]" :response="responses[responseTabIndex]"></ResponseData>
    </div>
  </div>
</template>

<script lang="ts">
import { useRouter } from 'vue-router'
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import { useStore } from 'vuex'
import Param from '@/components/request/ParamReadonly.vue'
import Body from '@/components/request/BodyReadonly.vue'
import { API, ResponseCode, ResponseFormat } from '@/store/model'
import ResponseData from '@/components/response/ResponseDataReadonly.vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'APIDetail',
  components: { ResponseData, Body, Param },
  props: {
    tabIndex: Number,
    query: Object
  },
  setup (props: any, context: any) {
    const pageLoading = ref(true)
    const logs = ref([])
    const { t } = useI18n()
    const emptyApi: API = {
      method: 'GET',
      path: '',
      name: '',
      status: 'develop',
      folder: {},
      responsible: {},
      request: { param: [{ uuid: ydhl.uuid(), name: '', type: 'string' }] },
      response: [{ name: t('api.response.success'), code: 200, contentType: 'JSON', uuid: ydhl.uuid() }]
    }
    const api = ref<API>(emptyApi)
    const badge = ref<any>({})
    const newResponse = ref<any>({ code: 200, contentType: 'JSON', uuid: ydhl.uuid() })
    const codes = ResponseCode
    const formats = ResponseFormat
    const members = ref([])
    const store = useStore()
    const router = useRouter()
    const project = computed(() => store.state.design.project)

    const goto = (path, query = {}) => {
      router.push({ path, query })
    }
    const loadApiInfo = (uuid) => {
      ydhl.get('/api/api/info.json?uuid=' + uuid, true).then((rst: any) => {
        members.value = rst.member || []
        api.value = rst.api || emptyApi
      })
    }

    const requestTabs = [
      { name: 'param', page: 'Param' },
      { name: 'body', page: 'Body' },
      { name: 'cookie', page: 'Param' },
      { name: 'header', page: 'Param' }
      // ,
      // { name: 'auth', page: 'Auth' },
      // { name: 'setting', page: 'Setting' }
    ]
    const responses = computed(() => api.value.response)
    const responseTabIndex = ref(0)
    const requestTabIndex = ref(0)

    onMounted(() => {
      loadApiInfo(props.query.uuid)
    })
    const edit = () => {
      store.commit('updateTab', { index: props.tabIndex, tab: { page: 'APIAdd', name: 'common.loading', query: { uuid: api.value.uuid, action: 'edit' } } })
    }
    watch(() => api.value.name, (v) => {
      store.commit('updateTabTitle', { tabIndex: props.tabIndex, name: v || t('common.addAPI') })
    })
    return {
      t,
      project,
      pageLoading,
      requestTabs,
      requestTabIndex,
      responses,
      responseTabIndex,
      members,
      api,
      badge,
      codes,
      formats,
      newResponse,
      logs,
      edit,
      apiurl: ydhl.api,
      goto
    }
  }
}
</script>
