<template>
  <div class="row mb-3">
    <div class="col-6">
      <label>{{t("api.url")}} <span class="text-danger">*</span></label>
      <div class="input-group">
        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span :class="'text-uppercase api-method-'+api.method">{{ api.method }}</span></button>
        <ul class="dropdown-menu">
          <li v-for="(method, index) in methods" :key="index" @click="api.method=method">
            <a :class="'dropdown-item text-uppercase api-method-'+method" href="javascript:void(0)">{{ method }}</a>
          </li>
        </ul>
        <div class="input-group-text">/</div>
        <input type="text" class="form-control" v-model="api.path" aria-label="Text input with dropdown button">
      </div>
    </div>
    <div class="col-2">
      <label>{{t("api.name")}} <span class="text-danger">*</span></label>
      <input type="text" class="form-control" v-model="api.name">
    </div>
    <div class="col-2">
      <label>{{t("api.folder")}}</label>
      <lay-tree-select style="width: auto !important;" v-model="api.folder.uuid" allow-clear :data="folderSelector"></lay-tree-select>
    </div>
    <div class="col-2">
      <label>{{t("api.status.name")}}</label>
      <div class="dropdown">
        <button class="btn btn-outline-secondary d-flex w-100 align-items-center justify-content-between dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <div><span :class="'api-status api-status-'+api.status"></span>{{ t('api.status.' + api.status) }}</div>
        </button>
        <ul class="dropdown-menu">
          <li @click="api.status='develop'"><a class="dropdown-item" href="javascript:void(0)"><span class="api-status api-status-develop" ></span>{{ t('api.status.develop') }}</a></li>
          <li @click="api.status='test'"><a class="dropdown-item" href="javascript:void(0)"><span class="api-status api-status-test" ></span>{{ t('api.status.test') }}</a></li>
          <li @click="api.status='deprecated'"><a class="dropdown-item" href="javascript:void(0)"><span class="api-status api-status-deprecated" ></span>{{ t('api.status.deprecated') }}</a></li>
          <li @click="api.status='released'"><a class="dropdown-item" href="javascript:void(0)"><span class="api-status api-status-released" ></span>{{ t('api.status.released') }}</a></li>
        </ul>
      </div>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-4">
      <label>{{t("api.version")}}</label>
      <div class=" input-group">
        <label class="input-group-text">{{t("api.major")}}</label>
        <input class="form-control" type="number" v-model="api.major"/>
        <label class="input-group-text">{{t("api.minor")}}</label>
        <input class="form-control" type="number" v-model="api.minor"/>
        <label class="input-group-text">{{t("api.revision")}}</label>
        <input class="form-control" type="number" v-model="api.revision"/>
      </div>
    </div>
    <div class="col-5">
      <label>{{t("api.responsible")}}</label>
      <select class="form-control" v-model="api.responsible.uuid">
        <option value="">{{t('common.none')}}</option>
        <option v-for="(member, index) in members" :value="member.uuid" :key="index">{{member.name}}</option>
      </select>
    </div>
    <div class="col-3">
      <label>{{t("api.label")}}</label>
      <LabelSelect v-model="api.label" :url="apiurl+'api/label.json'"></LabelSelect>
    </div>
  </div>

  <div class="row mb-3">
    <div class="col-12 position-relative">
      <label>{{t("api.comment")}}</label>
      <button style="position: absolute; right: 20px;top:30px" @click="openMarkdown" type="button" class="btn btn-outline-primary btn-sm"><i class="iconfont icon-code"></i></button>
      <textarea class="form-control" :placeholder="t('api.supportMarkdown')" v-model="api.comment"></textarea>
    </div>
  </div>

  <h3 class="mb-2">{{t('api.request.name')}}</h3>
  <div class="card mb-3 border-0">
    <div class="card-header p-0 ps-2 bg-white">
      <ul class="nav nav-tabs  border-bottom-0 position-relative" style="top: 1px">
        <li class="nav-item" v-for="(requestTab, index) in requestTabs" :key="index">
          <a  style="line-height: 14px" :class="{'nav-link text-dark': true, 'active fw-bold': requestTabIndex===index}" @click="requestTabIndex = index" href="javascript:void(0)">
            {{t('api.request.'+requestTab.name+'.name')}}
            <span class="badge bg-primary p-0 ps-1 pe-1" v-if="badge[requestTab.name]" v-html="badge[requestTab.name]"></span>
          </a>
        </li>
      </ul>
    </div>
    <div class="card-body  border-1 border rounded-bottom">
      <KeepAlive>
        <Param v-if="requestTabs[requestTabIndex].name === 'cookie'" :param="api.request.cookie" @update="updateRequest"></Param>
      </KeepAlive>
      <KeepAlive>
        <Param v-if="requestTabs[requestTabIndex].name === 'header'" :param="api.request.header" @update="updateRequest"></Param>
      </KeepAlive>
      <KeepAlive>
        <div>
          <template v-if="requestTabs[requestTabIndex].name === 'param'" >
            <div class="text-muted mb-3">{{t('api.dataStruct.queryParam')}}</div>
            <Param :param="api.request.param" :query="queryString" @update="updateRequest"></Param>
          </template>
          <template v-if="requestTabs[requestTabIndex].name === 'param' && api.request.path && api.request.path.length>0" >
            <div class="text-muted mt-3">{{t('api.dataStruct.pathParam')}}</div>
            <Param :name-is-read-only="true" :hide-array="true" :hide-import="true" :param="api.request.path" @update="updateRequest"></Param>
          </template>
        </div>
      </KeepAlive>
      <KeepAlive>
        <Body v-if="requestTabs[requestTabIndex].name === 'body'" :api="api" @update="updateRequest"></Body>
      </KeepAlive>
<!--      <KeepAlive>-->
<!--        <Auth v-if="requestTabs[requestTabIndex].name === 'auth'" :api="api" @update="updateRequest"></Auth>-->
<!--      </KeepAlive>-->
    </div>
  </div>
  <h3 class="mb-2">{{t('api.response.name')}}</h3>
  <div class="card border-0">
    <div class="card-header bg-white p-0 ps-2">
      <ul class="nav nav-tabs border-bottom-0 position-relative" style="top: 1px">
        <li class="nav-item" v-for="(response, index) in responses" :key="index">
          <a style="line-height: 14px" :class="{'nav-link text-dark': true, 'active fw-bold': responseTabIndex===index}" @click="responseTabIndex=index" href="javascript:void(0)">{{response.name}}({{response.code}})</a>
        </li>
        <li class="nav-item" >
          <a style="line-height: 14px" class="nav-link text-dark" @click="addResponseVisible=true" href="javascript:void(0)"><i class="iconfont icon-plus"></i> {{t('common.add')}}</a>
        </li>
      </ul>
    </div>
    <div class="card-body border-1 border rounded-bottom border-top-0">
      <ResponseData v-if="responses[responseTabIndex]" :response="responses[responseTabIndex]" @update="updateResponse" :canDelete="responses.length > 1"></ResponseData>
    </div>
  </div>
  <div class="fixed bg-light border-top fixed-bottom d-flex align-items-center p-2" style="margin-left: 80px">
    <button type="button" class="btn btn-primary ms-3" @click="commitDialogVisible=true">{{t('common.save')}}</button>
  </div>
  <lay-layer v-model="commitDialogVisible" :shade="true" :title="t('api.commitMessage')">
    <div class="card shadow-none border-0" style="width: 80vw">
      <div class="card-body">
        <textarea v-model="commitMsg" class="form-control" :placeholder="t('api.commitMessageTip')"></textarea>
      </div>
      <div class="card-footer d-flex justify-content-end align-items-center">
        <button type="button" class="btn btn-primary" @click="saveAPI">{{ t('common.save') }}</button>
      </div>
    </div>
  </lay-layer>
  <lay-layer v-model="markdownDialogVisible" :shade="true">
    <div class="card shadow-none border-0" style="width: 80vw">
      <div class="card-body">
        <v-md-editor height="600px" v-model="api.comment" left-toolbar="undo redo clear | h bold italic strikethrough quote | ul ol table hr | link image code" :disabled-menus="[]" @upload-image="handleUploadImage"></v-md-editor>
      </div>
      <div class="card-footer d-flex justify-content-end align-items-center">
        <button type="button" class="btn btn-primary" @click="closeMarkdown">{{ t('common.ok') }}</button>
      </div>
    </div>
  </lay-layer>
  <lay-layer v-model="addResponseVisible" :shade="true">
      <div class="card shadow-none border-0" style="width: 500px">
        <div class="card-body">
          <div class="row mb-3">
            <div class="col">
              <label>{{t("api.response.contentName")}}</label>
              <input type="text" class="form-control form-control-sm" v-model.trim="newResponse.name">
            </div>
            <div class="col">
              <div class="text-nowrap">{{t('api.response.httpCode')}}:</div>
              <select class="form-select form-select-sm" v-model.number="newResponse.code">
                <option v-for="(desc, code, index) in codes" :value="code" :key="index">{{code}} - {{desc}}</option>
              </select>
            </div>
            <div class="col">
              <div class="text-nowrap">{{t('api.response.contentFormat')}}:</div>
              <select class="form-select form-select-sm"  v-model="newResponse.contentType">
                <option v-for="(format, contentType, index) in formats" :value="contentType" :key="index">{{contentType}}</option>
              </select>
            </div>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end align-items-center">
          <button type="button" class="btn btn-secondary me-3" @click="addResponseVisible=false">{{ t('common.close') }}</button>
          <button type="button" class="btn btn-primary"  @click="addResponse">{{ t('common.ok') }}</button>
        </div>
      </div>
    </lay-layer>
</template>

<script lang="ts">
import { useRouter } from 'vue-router'
import { layer } from '@layui/layui-vue'
import { useI18n } from 'vue-i18n'
import { computed, onMounted, onActivated, ref, watch, onDeactivated, toRaw } from 'vue'
import { useStore } from 'vuex'
import LabelSelect from '@/components/common/LabelSelect.vue'
import Param from '@/components/request/Param.vue'
import Body from '@/components/request/Body.vue'
// import Auth from '@/components/request/Auth.vue'
import { API, ResponseCode, ResponseFormat } from '@/store/model'
import ResponseData from '@/components/response/ResponseData.vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'APIAdd',
  components: { ResponseData, Body, Param, LabelSelect },
  props: {
    tabIndex: Number,
    query: Object
  },
  setup (props: any, context: any) {
    const pageLoading = ref(true)
    const addResponseVisible = ref(false)
    const markdownDialogVisible = ref(false)
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
    const queryString = ref<any>('')
    const folderSelector = ref([])
    const commitDialogVisible = ref(false)
    const commitMsg = ref('')
    const newResponse = ref<any>({ code: 200, contentType: 'JSON', uuid: ydhl.uuid() })
    const codes = ResponseCode
    const formats = ResponseFormat
    const members = ref([])
    const store = useStore()
    const router = useRouter()
    const project = computed(() => store.state.design.project)
    watch(() => api.value.name, (v) => {
      store.commit('updateTabTitle', { tabIndex: props.tabIndex, name: v || t('common.addAPI') })
    })
    watch(() => api.value.path, (v) => {
      if (v.match(/\?$/)) { // 输入$
        layer.msg(t('api.request.param.addQueryStringInParam'))
        setTimeout(() => {
          const rawApi = toRaw(api.value)
          rawApi.path = v.replace(/\?.*/, '')
        }, 800)
        return
      }
      if (v.match(/\?.+$/)) { // 粘贴
        queryString.value = v
      }
      if (v.match(/{.+}/)) {
        handlePathParam(v)
      }
      const rawApi = toRaw(api.value)
      rawApi.path = v.replace(/\?.+/, '').replace(/^.+\.[^/]+\//, '')
    })
    const handlePathParam = (path: string) => {
      if (!api.value.request.path) {
        api.value.request.path = []
      }
      const match: any = path.match(/{([^{}]+)}/g)
      if (!match) {
        api.value.request.path = []
        return
      }
      const args: any = []
      for (const item of match) {
        args.push(item.replace(/{|}/g, ''))
      }
      // 删除已经不存在的
      for (let index = 0; index < api.value.request.path.length; index++) {
        if (args.indexOf(api.value.request.path[index].name) === -1) {
          api.value.request.path.splice(index, 1)
        }
      }

      // 添加新的
      for (const arg of args) {
        const index = api.value.request.path.findIndex(item => item.name === arg)
        if (index === -1) {
          api.value.request.path.push({ name: arg, uuid: ydhl.uuid(), type: 'string' })
        }
      }
    }
    const goto = (path, query = {}) => {
      router.push({ path, query })
    }
    const loadApiInfo = (uuid) => {
      ydhl.get('/api/api/info.json?uuid=' + uuid, true).then((rst: any) => {
        folderSelector.value = rst.folder || []
        members.value = rst.member || []
        api.value = rst.api || emptyApi
        if (props.query.action === 'copy') {
          api.value.uuid = ''
          api.value.name = '(复制)' + api.value.name
        }
      })
    }
    const loadBasicInfo = () => {
      ydhl.get('/api/api/basic.json?uuid=' + project.value.id).then((rst: any) => {
        folderSelector.value = rst.folder || []
        members.value = rst.member || []
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
    onActivated(() => {
      console.log('apiadd activated')
    })
    onDeactivated(() => {
      console.log('apiadd deactive')
    })
    onMounted(() => {
      console.log('apiadd mounted')
      if (['edit', 'copy'].indexOf(props.query?.action) === -1 || !props.query.uuid) {
        loadBasicInfo()
        store.commit('updateTabTitle', { tabIndex: props.tabIndex, name: t('common.addAPI') })
      } else {
        loadApiInfo(props.query.uuid)
      }
    })
    const openMarkdown = () => {
      markdownDialogVisible.value = true
    }
    const addResponse = () => {
      if (!newResponse.value.name) {
        layer.msg(t('api.response.nameIsRequired'))
        return
      }
      if (newResponse.value.contentType === 'JSON') {
        newResponse.value.body = { uuid: ydhl.uuid(), type: 'object', isRoot: true }
      }
      addResponseVisible.value = false
      responses.value.push(JSON.parse(JSON.stringify(newResponse.value)))
    }
    const closeMarkdown = () => {
      markdownDialogVisible.value = false
    }
    const requestComponent = computed(() => {
      return requestTabs?.[requestTabIndex.value].page || null
    })
    const updateResponse = (info: any) => {
      if (!api.value.response) api.value.response = []
      api.value.response[responseTabIndex.value] = info
    }
    const saveAPI = () => {
      commitDialogVisible.value = false
      ydhl.post('/api/api/save.json', { api: api.value, commit: commitMsg.value, project_uuid: project.value.id }, [], true, true, true).then((rst: any) => {
        ydhl.confirm(t('api.saveSuccessContinueAdd'), t('common.cancel'), t('common.continueAdd')).then((id) => {
          layer.close(id)
          store.commit('updateTab', { index: props.tabIndex, tab: { page: 'APIDetail', name: 'common.loading', query: { uuid: rst } } })
        }).catch(() => {
          api.value = emptyApi
        })
      }).catch((rst) => {
        ydhl.alert(rst.msg || 'Save Failed', t('common.ok'))
      })
    }
    const updateRequest = (info: any = [], bodyType = '') => {
      const name = requestTabs?.[requestTabIndex.value].name
      const request: any = api.value.request
      switch (name) {
        case 'param':
        case 'cookie':
        case 'header':
          badge.value[name] = info.filter(item => item.name !== '').length || 0
          api.value.request[name] = info
          break
        case 'body':
          badge.value[name] = info ? '&nbsp;' : ''
          if (!request.body) request.body = { none: '' }
          request.body![bodyType] = info
          break
        case 'auth':
        case 'setting':
          badge.value[name] = info ? '&nbsp;' : ''
          api.value.request[name] = info
          break
      }
    }

    return {
      t,
      queryString,
      markdownDialogVisible,
      project,
      pageLoading,
      folderSelector,
      requestTabs,
      requestTabIndex,
      addResponseVisible,
      responses,
      responseTabIndex,
      members,
      requestComponent,
      api,
      badge,
      codes,
      formats,
      newResponse,
      apiurl: ydhl.api,
      methods: ['get', 'post', 'head', 'options', 'delete', 'put'],
      closeMarkdown,
      loadBasicInfo,
      updateRequest,
      saveAPI,
      updateResponse,
      commitDialogVisible,
      commitMsg,
      openMarkdown,
      addResponse,
      goto
    }
  }
}
</script>
