<template>
  <div class="d-flex align-items-center justify-content-end mb-2">
    <button class="btn btn-sm ps-1 pe-1 pt-0 pb-0 btn-primary" type="button" @click="addBindApiAction()">{{t('action.add')}}</button>
  </div>
  <div v-for="(bindApiAction,index) in bindApiActions" :key="index" class="p-1">
    <div class="d-flex align-items-center ps-1 pe-1 bg-light ">
      <div class="flex-grow-1 text-truncate">
        <template v-if="bindApiAction.mode=='setting'">
          <template v-if="bindApiAction.expression_desc">
            <div class="text-truncate text-success mb-1">{{bindApiAction.expression_desc}}</div>
          </template>
          <div v-else class="text-info mb-1">{{ t('action.notSet') }}</div>
        </template>
        <template v-else>
          <template v-if="bindApiAction.code">
            <div class="text-truncate text-success mb-1">{{bindApiAction.code}}</div>
          </template>
          <div v-else class="text-info mb-1">{{ t('action.notSet') }}</div>
        </template>
      </div>

      <i  @click="modifyBindApiAction(bindApiAction)" class="iconfont icon-edit pointer"></i>
      <ConfirmRemove @remove="removeBindApiAction(bindApiAction)"></ConfirmRemove>
    </div>
    <div v-if="(!bindApiAction.trueActions || bindApiAction.trueActions.length==0) && (!bindApiAction.falseActions || bindApiAction.falseActions.length==0)" class="text-muted">{{t('action.noActionDefined')}}</div>
    <div class="text-muted"> When Resolve:</div>
    <div v-if="bindApiAction.trueActions && bindApiAction.trueActions.length>0" class="list-group list-group-flush">
      <draggable :list="bindApiAction.trueActions" handle=".icon-drag"  @change="(n) => sortEventAction(bindApiAction, n, 'true')">
        <transition-group>
          <div class="list-group-item border-0 list-group-item-action p-1 d-flex align-items-center" v-for="(action, idx) in bindApiAction.trueActions" :key="idx">
            <div class="me-1"><i class="iconfont icon-drag text-muted" style="cursor: move"></i></div>
            <EventAction bind-type="bind_api_action" :bind-uuid="bindApiAction.uuid" :action="action"
                         :variables="getActionLocalArgs(bindApiAction)" @beforeSave="(callback) => beforeSave(idx, callback, 'true')"></EventAction>
            <ConfirmRemove @remove="postRemoveAction(bindApiAction, idx, 'true')"></ConfirmRemove>
          </div>
        </transition-group>
      </draggable>
    </div>
    <div class="text-muted"> When Reject:</div>
    <div v-if="bindApiAction.falseActions && bindApiAction.falseActions.length>0" class="list-group list-group-flush">
      <draggable :list="bindApiAction.falseActions" handle=".icon-drag"  @change="(n) => sortEventAction(bindApiAction, n, 'false')">
        <transition-group>
          <div class="list-group-item border-0 list-group-item-action p-1 d-flex align-items-center" v-for="(action, idx) in bindApiAction.falseActions" :key="idx">
            <div class="me-1"><i class="iconfont icon-drag text-muted" style="cursor: move"></i></div>
            <EventAction bind-type="bind_api_action" :bind-uuid="bindApiAction.uuid" :action="action"
                         :variables="getActionLocalArgs(bindApiAction)" @beforeSave="(callback) => beforeSave(idx, callback, 'false')"></EventAction>
            <ConfirmRemove @remove="postRemoveAction(bindApiAction, idx, 'false')"></ConfirmRemove>
          </div>
        </transition-group>
      </draggable>
    </div>
  </div>
  <lay-layer v-model="addDialogVisible" :title="t('api.postProcessors')" :shade="true" :area="['800px', '60vh']" :btn="buttons">
    <div class="p-2">
      <div>
        <div v-if="!myApi.output || myApi.output.length===0" class="p-1 mt-2 text-muted">{{t('api.noOutputs')}}</div>
        <template v-else>
          <div class="d-flex align-items-center mb-1">
            <div class="ps-2 pe-2 text-muted">{{t('api.whenResponseIs')}}</div>
            <label :class="{'d-flex align-items-center fw-bold me-2': true, 'text-primary':outputIndex==currOutputIndex}" v-for="(item, outputIndex) in myApi.output" :key="outputIndex" @click="changeOutputIndex(outputIndex)">
              <input type="radio" :checked="outputIndex==currOutputIndex">&nbsp;{{myApi.output[outputIndex].name}}
            </label>
            <div class="text-muted">{{t('api.meetConditions')}}</div>
          </div>
          <CodeEditor editStyle="height:200px" ref="codeEditor" :code="editBindApiActionModel.code"
                      surround-code="new Promise((resolve, reject) => {@})"
                      :variables="editBindApiActionVariable" language="javascript"></CodeEditor>
        </template>
      </div>
      <div class="d-flex gap-2">
        <div class="w-50">
          <div class="text-muted">
            Resolve: {{t('action.conditionTrueDesc')}}
            <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="t('action.add')" @change="(option)=>addAction(option.value, 'true')"></AdvanceSelect>
          </div>
          <draggable :list="editBindApiActionModel.trueActions" handle=".card-header"  @change="(n) => sortEventAction(editBindApiActionModel, n, 'true')">
            <transition-group>
              <div class="card mt-2" v-for="(action, index) in editBindApiActionModel.trueActions" :key="index">
                <div class="card-header justify-content-between" style="cursor: move">
                  <i class="iconfont icon-drag text-muted"></i>
                  {{t('action.' + action.type)}}
                  <ConfirmRemove @remove="removeAction(index, 'true')"></ConfirmRemove>
                </div>
                <div class="card-body">
                  <EventAction :autosave="false" bind-type="bind_api_action" :bind-uuid="editBindApiActionModel.uuid" :popup-page-data-inline="true" :action="action"
                               :variables="editBindApiActionVariable" @beforeSave="(callback) => beforeSave(index, callback, 'true')">
                  </EventAction>
                </div>
              </div>
            </transition-group>
          </draggable>
        </div>
        <div class="w-50">
          <div class="text-muted">
            Reject: {{t('action.conditionFalseDesc')}}
            <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="t('action.add')" @change="(option)=>addAction(option.value, 'false')"></AdvanceSelect>
          </div>
          <draggable :list="editBindApiActionModel.falseActions" handle=".card-header"  @change="(n) => sortEventAction(editBindApiActionModel, n, 'false')">
            <transition-group>
              <div class="card mt-2" v-for="(action, index) in editBindApiActionModel.falseActions" :key="index">
                <div class="card-header justify-content-between" style="cursor: move">
                  <i class="iconfont icon-drag text-muted"></i>
                  {{t('action.' + action.type)}}
                  <ConfirmRemove @remove="removeAction(index, 'false')"></ConfirmRemove>
                </div>
                <div class="card-body">
                  <EventAction :autosave="false" bind-type="bind_api_action" :bind-uuid="editBindApiActionModel.uuid" :popup-page-data-inline="true" :action="action"
                               :variables="editBindApiActionVariable" @beforeSave="(callback) => beforeSave(index, callback, 'false')">
                  </EventAction>
                </div>
              </div>
            </transition-group>
          </draggable>
        </div>
      </div>
    </div>
  </lay-layer>
  <lay-layer v-model="showRedirectVisible" title="Redirect" :shade="true" :area="['50vw', '360px']">
    <div class="p-2">
      <div class="alert-light alert">{{currAction.redirect}}</div>
      <DataConnect v-for="(item, index) in tplDatas"
                   connect="to" :bound-data="currAction?.input" :readonly = 'true'
                   :key="index" :intent="0" :model="item" :index="0">
      </DataConnect>
    </div>
  </lay-layer>
  <lay-layer v-model="showPageDataVisible" :title="t('variable.bound')" :shade="true" :area="['50vw', '360px']">
    <div class="p-2">
      <DataConnect v-for="(item, index) in pageDatas"
                   connect="to" :bound-data="currAction?.input" :readonly = 'true'
                   :key="index" :intent="0" :model="item" :index="0">
      </DataConnect>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import DataConnect from '@/components/common/DataConnect.vue'
import EventAction from '@/components/common/EventAction.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import { VueDraggableNext } from 'vue-draggable-next'
import CodeEditor from '@/components/common/CodeEditor.vue'
import Util from '@/components/Util'

export default {
  name: 'BindAPIPostProcessors',
  components: { CodeEditor, AdvanceSelect, EventAction, DataConnect, ConfirmRemove, draggable: VueDraggableNext },
  props: {
    api: Object
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const addDialogVisible = ref(false)
    const showRedirectVisible = ref(false)
    const showPageDataVisible = ref(false)
    const selectedPageId = computed(() => selectedPage.value?.meta?.id)
    const myApi = ref(JSON.parse(JSON.stringify(props.api)))
    const store = useStore()
    const pageDatas = ref<any>([])
    const tplDatas = ref<any>([])
    const codeEditor = ref()
    const selectedPage = computed(() => store.state.design.page)
    const editBindApiActionModel = ref<any>({ mode: 'setting', code: '', actions: [] })
    const currAction = ref<any>({})
    const util = Util()
    const actionTypes = computed(() => {
      const actions: any = ['output', 'redirect', 'mutation', 'popup', 'webapi', 'interval', 'validate']
      if (selectedPage.value.pageType === 'component') {
        actions.push('emit')
      }
      return util.getActions(...actions)
    })
    const bindApiActions = ref([])
    const currOutputIndex = ref(0)

    const loadBindApiActions = () => {
      ydhl.get('api/action.json', { uuid: myApi.value.uuid }, (rst: any) => {
        if (rst.success) {
          bindApiActions.value = rst.data.bind_actions
          return
        }
        ydhl.alert(rst.msg || t('common.operationFail'))
      })
    }
    const removeBindApiAction = (bindApiAction) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        ydhl.post('api/action/removeapiaction.json', { uuid: bindApiAction.uuid }, [], (rst: any) => {
          ydhl.closeLoading(dialogId)
          if (rst.success) {
            loadBindApiActions()
            return
          }
          ydhl.alert(rst.msg || t('common.operationFail'))
        })
      })
    }

    const addBindApiAction = () => {
      editBindApiActionModel.value = { mode: 'code', code: '', expression: '', actions: [] }
      addDialogVisible.value = true
    }
    const addAction = (type, trueFalse) => {
      if (!editBindApiActionModel.value[`${trueFalse}Actions`]) editBindApiActionModel.value[`${trueFalse}Actions`] = []
      editBindApiActionModel.value[`${trueFalse}Actions`].push({ type })
    }
    const removeAction = (index, trueFalse) => {
      editBindApiActionModel.value[`${trueFalse}Actions`].splice(index, 1)
    }
    const postRemoveAction = (bindApiAction, index, trueFalse) => {
      const actionUuid = bindApiAction[`${trueFalse}Actions`][index].uuid

      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: actionUuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }

        bindApiAction[`${trueFalse}Actions`].splice(index, 1)
      })
    }
    const modifyBindApiAction = (bindApiAction) => {
      editBindApiActionModel.value = JSON.parse(JSON.stringify(bindApiAction))
      addDialogVisible.value = true
      if (myApi.value.output) {
        currOutputIndex.value = myApi.value.output.findIndex((item) => {
          return item.uuid === bindApiAction.output_data_id
        })
        if (currOutputIndex.value < 0) currOutputIndex.value = 0
      } else {
        currOutputIndex.value = 0
      }
    }
    const save = (callback:any) => {
      const editor = codeEditor.value?.getEditorInstance()
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        const data: any = JSON.parse(JSON.stringify(editBindApiActionModel.value))
        data.page_uuid = selectedPage.value.meta.id
        data.type = 'bind_api'
        data.code = editor ? editor.getValue().trim() : ''
        data.expression = ''
        data.from_uuid = myApi.value.uuid
        data.output_data_id = myApi.value.output[currOutputIndex.value].uuid
        ydhl.postJson('api/action/saveapiaction.json', data).then((rst: any) => {
          ydhl.closeLoading(dialogId)
          if (rst.success) {
            editBindApiActionModel.value = rst.data
            if (callback) callback()
            return
          }
          ydhl.alert(rst.msg || t('common.operationFail'))
        }).catch((reason) => {
          ydhl.closeLoading(dialogId)
          ydhl.alert(reason || t('common.operationFail'))
        })
      })
    }
    const buttons = ref([
      {
        text: t('common.save'),
        callback: () => {
          save(() => {
            addDialogVisible.value = false
            loadBindApiActions()
          })
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          addDialogVisible.value = false
        }
      }
    ])
    onMounted(() => {
      loadBindApiActions()
    })

    const beforeSave = (index, callback, type) => {
      save(() => {
        callback(JSON.parse(JSON.stringify(editBindApiActionModel.value[`${type}Actions`][index])))
      })
    }
    const showRedirect = (action) => {
      currAction.value = JSON.parse(JSON.stringify(action))
      showRedirectVisible.value = true
      tplDatas.value = []
      const match = currAction.value?.redirect?.match(/\{[^}]+\}/g)
      if (!match) return
      for (const item of match) {
        const name = item.replace(/{|}/g, '')
        tplDatas.value.push({
          uuid: name,
          name,
          type: 'string'
        })
      }
    }
    const showPageData = (action) => {
      currAction.value = JSON.parse(JSON.stringify(action))
      showPageDataVisible.value = true
      loadPageData(action?.popupPageId)
    }

    const loadPageData = (popupPageId) => {
      // 重定向时只重定向到页面
      if (popupPageId) {
        ydhl.get('api/bind/data.json?data_from=path,query&page_uuid=' + popupPageId, [], (rst: any) => {
          pageDatas.value = rst.data.query || []
          if (rst.data.path) pageDatas.value.push(...rst.data.path)
        }, 'json')
      }
    }
    const getOutputArgs = (rst: any) => {
      const v: any = []
      if (rst) {
        const data = JSON.parse(JSON.stringify(rst.body))
        data.name = 'rst'
        data.title = myApi.value.name
        v.push(data)
      }
      if (myApi.value.localVariables) {
        v.unshift(...myApi.value.localVariables)
      }
      return v
    }
    const getActionLocalArgs = (bindApiAction) => {
      const rst = myApi.value.output?.find((item) => item.uuid === bindApiAction.output_data_id)
      return getOutputArgs(rst)
    }
    // 当前编辑的api 中选择的响应的数据的局部变量
    const editBindApiActionVariable = computed(() => {
      return getOutputArgs(myApi.value.output[currOutputIndex.value])
    })
    const changeOutputIndex = (outputIndex) => {
      currOutputIndex.value = outputIndex
      // 改变输出结构后，action绑定的本地变量需要清空
      for (const action of editBindApiActionModel.value.trueActions) {
        action.input = {}
      }
      for (const action of editBindApiActionModel.value.falseActions) {
        action.input = {}
      }
    }

    const sortEventAction = (bindEvent, { moved }, trueFalse) => {
      const index = {}
      for (const idx in bindEvent[`${trueFalse}Actions`]) {
        index[bindEvent[`${trueFalse}Actions`][idx].uuid] = idx
      }
      ydhl.postJson('api/action/sort.json', {
        page_uuid: selectedPageId.value, index
      })
    }
    return {
      t,
      actionTypes,
      selectedPage,
      myApi,
      bindApiActions,
      editBindApiActionModel,
      editBindApiActionVariable,
      currAction,
      removeAction,
      changeOutputIndex,
      currOutputIndex,
      addDialogVisible,
      showRedirectVisible,
      showPageDataVisible,
      buttons,
      pageDatas,
      tplDatas,
      codeEditor,
      sortEventAction,
      postRemoveAction,
      getActionLocalArgs,
      addAction,
      beforeSave,
      removeBindApiAction,
      addBindApiAction,
      modifyBindApiAction,
      showRedirect,
      showPageData
    }
  }
}
</script>
