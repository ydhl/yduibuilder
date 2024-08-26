<template>
  <div class="style-panel pt-2">
    <div class="d-flex align-items-center justify-content-between mb-2">
      <div class="fs-6 text-muted"><i class="iconfont icon-api"></i>{{t('common.api')}}</div>
      <button type="button" class="btn btn-xs btn-light" @click="loadApis">{{t('common.refresh')}}</button>
    </div>
    <div v-if="loading" class="vh-100 d-flex align-items-center justify-content-center">
      {{t('page.loading')}}
    </div>
    <template v-else>
      <div v-if="!apis || apis.length===0" class="p-5 text-center">
        <i class="iconfont icon-wuneirong text-muted fs-1 m-5"></i>
        <div>{{t('common.empty')}}</div>
      </div>
      <template v-for="(api, apiIndex) in apis" :key="apiIndex">
        <div class="style-header pointer d-block">
          <div class="d-flex text-truncate text-dark">
            <i class="iconfont icon-tree-close"></i>
            <div class="flex-grow-1 pointer-event-none d-flex align-items-center">
              <WebAPIBasicInfo :web-api="api"></WebAPIBasicInfo>
            </div>
          </div>
          <div class="fs-7 text-muted ms-3">{{api.trigger}}</div>
          <div class="fs-7 text-muted ms-3">Request: {{api.requestBodyType}}, Response: {{api.responseType}}</div>
        </div>
        <div class="style-body d-none" style="padding: 0">
          <div v-if="api.hasNewVersion" class="p-2 bg-light text-danger mb-2">
            <div class="fw-bold">{{t('api.hasNewVersion', [api.newVersion])}}</div>
            <div class="fs-7">{{api.commit_msg}}</div>
            <button type="button" class="btn btn-primary btn-xs" @click="upgradeAPI(api)">{{t('api.update')}}</button>
          </div>
          <div class="d-flex">
            <div class="vertical-tab">
              <a :class="{'vertical-tab-item': true, 'active': apiTab[api.uuid]=='input'}" @click="apiTab[api.uuid]='input'" href="javascript:void(0)"><i class="iconfont icon-data-input"></i>&nbsp;{{t('api.input')}}</a>
              <a :class="{'vertical-tab-item': true, 'active': apiTab[api.uuid]=='output'}" @click="apiTab[api.uuid]='output'" href="javascript:void(0)"><i class="iconfont icon-data-output"></i>&nbsp;{{t('api.output')}}</a>
              <a :class="{'vertical-tab-item': true, 'active': apiTab[api.uuid]=='postProcessors'}" @click="apiTab[api.uuid]='postProcessors'" href="javascript:void(0)"><i class="iconfont icon-event"></i>&nbsp;{{t('api.postProcessors')}}</a>
            </div>
            <div class="flex-grow-1 p-2" style="width: 90%">
              <template v-if="apiTab[api.uuid]=='input'">
                <div class="d-flex align-items-center mb-1">
                  <div class="btn-group btn-group-sm">
                    <button type="button" :class="{'btn ps-1 pe-1 pt-0 pb-0':true, 'btn-success': name==currInputName[apiIndex], 'btn-outline-success': name!=currInputName[apiIndex]}"
                            v-for="(input, name, inputIndex) in api.input" @click="currInputName[apiIndex]=name" :key="inputIndex">{{name}}</button>
                  </div>
                </div>
                <div v-if="!api.input[currInputName[apiIndex]]" class="p-1 text-muted">{{t('api.noInputs')}}</div>
                <template  v-for="(input, inputIndex) in api.input[currInputName[apiIndex]]" :key="currInputName[apiIndex] + '_' + inputIndex">
                  <DataConnect :root-uuid="api.uuid" :variables="api.localVariables" @updateConnectData="(leftRootUuid, leftDataPath, leftDataUuid, rightData, remove, expDesc) => updateInputBoundData(apiIndex, leftRootUuid, leftDataPath, leftDataUuid, rightData, remove, expDesc)" :model="input" connect="to"
                               :index="inputIndex" :boundData="api.inBoundData" :intent="0" path=""></DataConnect>
                </template>
              </template>
              <template v-if="apiTab[api.uuid]=='output'">
                <div v-if="!api.output || api.output.length===0" class="p-1 text-muted">{{t('api.noOutputs')}}</div>
                <template v-else>
                  <div class="d-flex align-items-center mb-1">
                    <div class="btn-group btn-group-sm">
                      <button type="button" :class="{'btn ps-1 pe-1 pt-0 pb-0':true, 'btn-success': outputIndex==currOutputIndex[apiIndex], 'btn-outline-success': outputIndex!=currOutputIndex[apiIndex]}"
                              v-for="(item, outputIndex) in api.output" @click="currOutputIndex[apiIndex]=outputIndex" :key="outputIndex">{{item.name}}</button>
                    </div>
                  </div>
                  <DataConnect :root-uuid="api.uuid" @updateConnectData="(rightRootUuid, rightDataPath, rightDataUuid, leftData, remove, expDesc) => updateOutputBoundData(apiIndex, rightRootUuid, rightDataPath, rightDataUuid, leftData, remove, expDesc)" :variables="api.localVariables"
                               :model="getOutputData(api, apiIndex)" :index="0" connect="from"
                               :boundData="api.outBoundData" :intent="0" path=""></DataConnect>
                </template>
              </template>
              <template v-if="apiTab[api.uuid]=='postProcessors'">
                <BindAPIPostProcessors :api="api"></BindAPIPostProcessors>
              </template>
            </div>
          </div>
        </div>
      </template>
    </template>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import BindAPIPostProcessors from '@/components/sidebar/BindAPIPostProcessors.vue'
import DataConnect from '@/components/common/DataConnect.vue'
import { Expression } from '@/store/model'
import WebAPIBasicInfo from '@/components/common/WebAPIBasicInfo.vue'

export default {
  name: 'API',
  components: { WebAPIBasicInfo, DataConnect, BindAPIPostProcessors },
  setup (pros: any, context: any) {
    const { t } = useI18n()
    const apis = ref<any>([])
    const loading = ref(true)
    const apiTab = ref({})
    const currInputName = ref({})
    const currOutputIndex = ref({})
    const store = useStore()
    const currPageId = computed(() => store.state.design.page?.meta?.id)
    const getOutputData = (api: any, apiIndex) => {
      const rst = JSON.parse(JSON.stringify(api.output[currOutputIndex.value[apiIndex]].body))
      if (rst) {
        rst.name = 'rst'
      }
      return rst || {}
    }

    watch(currPageId, (n, v) => {
      if (n !== v) loadApis()
    })
    const loadApis = (showLoading = false, activeApiIndex = -1, activeInput = null, activeOutput = null) => {
      if (showLoading) loading.value = true
      ydhl.get('api/bind/api.json?page_uuid=' + currPageId.value, [], (rst) => {
        if (showLoading) loading.value = false
        apis.value = rst.data
        for (const index in rst.data) {
          if (!apiTab.value[rst.data[index].uuid]) apiTab.value[rst.data[index].uuid] = 'input'
          if (activeApiIndex > -1 && activeInput) {
            currInputName.value[activeApiIndex] = activeInput
          } else {
            if (!ydhl.isEmptyObject(rst.data[index].input)) {
              currInputName.value[index] = Object.keys(rst.data[index].input)[0]
            }
          }
          if (activeApiIndex > -1 && activeOutput) {
            currOutputIndex.value[activeApiIndex] = activeOutput
          } else {
            if (rst.data[index].output) {
              currOutputIndex.value[index] = 0
            }
          }
        }
      }, 'json')
    }
    onMounted(() => {
      loadApis(true)
    })
    const upgradeAPI = (api: any) => {
      ydhl.loading(t('common.pleaseWait')).then((id) => {
        ydhl.post('api/bind/upgradeapi.json', { page_uuid: currPageId.value, api_uuid: api.uuid }, [], (rst) => {
          ydhl.closeLoading(id)
          if (!rst?.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          }
          loadApis()
        }, 'json')
      })
    }

    const removeBound = (fromUuid, toUuid, apiIndex = -1, apiInputName = null, apiOutputName = null) => {
      ydhl.postJson('api/bind/deleteconnect.json', {
        to_page_uuid: currPageId.value,
        from_page_uuid: currPageId.value,
        to_uuid: toUuid,
        from_uuid: fromUuid
      }).then((rst: any) => {
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        loadApis(false, apiIndex, apiInputName, apiOutputName)
      })
    }

    // 输入数据绑定，是从选择的rightData(右值)表达式，绑定给left数据（左值）, From是界面上显示要绑定的数据
    const updateInputBoundData = (apiIndex, leftRootUuid, leftDataPath, leftDataUuid, rightData: Expression, remove, expDesc: string) => {
      if (remove) {
        removeBound(rightData?.data?.fromUuid, leftRootUuid, apiIndex, currInputName.value[apiIndex])
        return
      }
      if (!rightData || ydhl.isEmptyObject(rightData)) return
      ydhl.postJson('api/bind/connect.json', {
        to_page_uuid: currPageId.value,
        from_page_uuid: currPageId.value,
        to_uuid: leftRootUuid,
        to_data_id: leftDataUuid,
        to_path: leftDataPath,
        from_type: 'bind_data',
        to_type: 'bind_api',
        input: rightData
      }).then((rst: any) => {
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        loadApis(false, apiIndex, currInputName.value[apiIndex])
      })
    }
    // 输出数据绑定，是从api的输出数据（右值）绑定给选择的left（左值）, right是界面上显示要绑定的数据
    const updateOutputBoundData = (apiIndex, rightRootUuid, rightDataPath, rightDataUuid, leftData: Expression, remove, expDesc) => {
      if (remove) {
        removeBound(rightRootUuid, leftData.data?.fromUuid, apiIndex, null, currOutputIndex.value[apiIndex])
        return
      }
      if (!leftData || ydhl.isEmptyObject(leftData)) return
      ydhl.postJson('api/bind/connect.json', {
        to_page_uuid: currPageId.value,
        from_page_uuid: currPageId.value,
        to_uuid: leftData.data?.fromUuid,
        to_data_id: leftData.data?.id,
        to_path: leftData.data?.path,
        from_type: 'bind_api',
        to_type: 'bind_data',
        input: {
          type: 'connect',
          data: {
            scope: 'page',
            fromUuid: rightRootUuid,
            path: rightDataPath,
            id: rightDataUuid
          }
        }
      }).then((rst: any) => {
        if (!rst?.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        loadApis(false, apiIndex, null, currOutputIndex.value[apiIndex])
      })
    }
    return {
      t,
      loading,
      apis,
      currInputName,
      currOutputIndex,
      apiTab,
      loadApis,
      updateInputBoundData,
      updateOutputBoundData,
      upgradeAPI,
      getOutputData
    }
  }
}
</script>
