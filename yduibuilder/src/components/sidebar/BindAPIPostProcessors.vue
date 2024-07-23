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
          <div class="text-secondary mb-1 pointer" @click="showCode(bindApiAction)">{{ t('common.customCode') }}</div>
        </template>
      </div>

      <i  @click="modifyBindApiAction(bindApiAction)" class="iconfont icon-edit pointer"></i>
      <ConfirmRemove @remove="removeBindApiAction(bindApiAction)"></ConfirmRemove>
    </div>
    <div v-if="!bindApiAction.actions || bindApiAction.actions.length==0" class="text-muted">{{t('action.noActionDefined')}}</div>
    <div v-else class="list-group list-group-flush">
      <draggable :list="bindApiAction.actions" handle=".icon-drag"  @change="(n) => sortEventAction(bindApiAction, n)">
        <transition-group>
          <div class="list-group-item border-0 list-group-item-action p-1 d-flex align-items-center" v-for="(action, idx) in bindApiAction.actions" :key="idx">
            <div class="me-1"><i class="iconfont icon-drag text-muted" style="cursor: move"></i></div>
            <EventAction bind-type="bind_action" :bind-uuid="bindApiAction.uuid" :action="action"
                         :variables="getLocalArgs(bindApiAction)" @beforeSave="(callback) => beforeSave(idx, callback)"></EventAction>
            <ConfirmRemove @remove="postRemoveAction(bindApiAction, idx)"></ConfirmRemove>
          </div>
        </transition-group>
      </draggable>
    </div>
  </div>
  <lay-layer v-model="addDialogVisible" :title="t('api.action')" :shade="true" :area="['80vw', '80vh']" :btn="buttons">
    <div class="p-2 d-flex">
      <div class="card w-50 me-2">
        <div class="card-header d-flex justify-content-between align-items-center">{{t('action.condition')}}
          <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
            <button type="button" @click="editBindApiActionModel.mode = 'setting'" :class="{'btn': true,' btn-outline-secondary': editBindApiActionModel.mode != 'setting', 'btn-secondary': editBindApiActionModel.mode == 'setting'}">{{ t('action.settingMode') }}</button>
            <button type="button" @click="switchToCode()" :class="{'btn': true, 'btn-outline-secondary': editBindApiActionModel.mode != 'code', 'btn-secondary': editBindApiActionModel.mode == 'code'}">{{ t('action.codeMode') }}</button>
          </div>
        </div>
        <div class="card-body">
          <div v-if="!myApi.output || myApi.output.length===0" class="p-1 mt-2 text-muted">{{t('api.noOutputs')}}</div>
          <template v-else>
            <div class="d-flex align-items-center mb-1">
              <label :class="{'d-flex align-items-center fw-bold me-2': true, 'text-primary':outputIndex==currOutputIndex}" v-for="(item, outputIndex) in myApi.output" :key="outputIndex" @click="changeOutputIndex(outputIndex)">
                <input type="radio" :checked="outputIndex==currOutputIndex">&nbsp;{{myApi.output[outputIndex].name}}
              </label>
            </div>
            <template v-if="editBindApiActionModel.mode=='code'">
              <div class="text-muted p-1">{{t('action.codeModeDesc')}}</div>
              <div><span style="color: blue">new </span><span style="color: #51a8a8">Promise</span>((resolve)=>{</div>
              <div id="codeeditor" style="height: 300px"></div>
              <div>})</div>
            </template>
            <template v-if="editBindApiActionModel.mode=='setting'">
              <div class="d-flex justify-content-start">
                <Expression :variables="editBindApiActionVariable" :expression="editBindApiActionExpression"></Expression>
              </div>
            </template>
          </template>
        </div>
      </div>
      <div class="w-50">
        <div class="text-muted">
          {{t('action.conditionDesc')}}
          <AdvanceSelect btn-size="btn-sm" :options="actionTypes" :default-text="t('action.add')" @change="(option)=>addAction(option.value)"></AdvanceSelect>
        </div>
        <draggable :list="editBindApiActionModel.actions" handle=".card-header"  @change="(n) => sortEventAction(editBindApiActionModel, n)">
          <transition-group>
            <div class="card mt-2" v-for="(action, index) in editBindApiActionModel.actions" :key="index">
              <div class="card-header justify-content-between" style="cursor: move">
                <i class="iconfont icon-drag text-muted"></i>
                {{t('action.' + action.type)}}
                <ConfirmRemove @remove="removeAction(index)"></ConfirmRemove>
              </div>
              <div class="card-body">
                <EventAction :autosave="false" bind-type="bind_action" :bind-uuid="editBindApiActionModel.uuid" :popup-page-data-inline="true" :action="action"
                             :variables="editBindApiActionVariable" @beforeSave="(callback) => beforeSave(index, callback)">
                </EventAction>
              </div>
            </div>
          </transition-group>
        </draggable>
      </div>
    </div>
  </lay-layer>
  <lay-layer v-model="codeDialogVisible" :title="t('common.customCode')" :shade="true" :area="['50vw', '360px']">
    <div class="p-2">
      <div id="previeweditor" style="height: 300px"></div>
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
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import * as monaco from 'monaco-editor'
import DataConnect from '@/components/common/DataConnect.vue'
import EventAction from '@/components/common/EventAction.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import { VueDraggableNext } from 'vue-draggable-next'
import Expression from '@/components/common/Expression.vue'

export default {
  name: 'BindAPIPostProcessors',
  components: { Expression, AdvanceSelect, EventAction, DataConnect, ConfirmRemove, draggable: VueDraggableNext },
  props: {
    api: Object
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const addDialogVisible = ref(false)
    const codeDialogVisible = ref(false)
    const showRedirectVisible = ref(false)
    const showPageDataVisible = ref(false)
    const selectedPageId = computed(() => selectedPage.value?.meta?.id)
    const myApi = ref(JSON.parse(JSON.stringify(props.api)))
    const store = useStore()
    const pageDatas = ref<any>([])
    const tplDatas = ref<any>([])
    const selectedPage = computed(() => store.state.design.page)
    const editBindApiActionModel = ref<any>({ mode: 'setting', code: '', actions: [] })
    const currAction = ref<any>({})
    const actionTypes = computed(() => {
      const types = [
        { name: t('action.output'), value: 'output', desc: t('action.outputDataDesc') },
        { name: t('action.redirect'), value: 'redirect', desc: t('action.redirectDesc') },
        { name: t('action.mutation'), value: 'mutation', desc: t('action.mutationDesc') },
        { name: t('action.popup'), value: 'popup', desc: t('action.popupDesc') },
        { name: t('action.webapi'), value: 'webapi', desc: t('action.webapiDesc') },
        { name: t('action.interval'), value: 'interval', desc: t('action.intervalDesc') }
      ]
      if (selectedPage.value.pageType === 'component') {
        types.push({ name: t('action.emit'), value: 'emit', desc: t('action.emitDesc') })
      }
      return types
    })
    const bindApiActions = ref([])
    const currOutputIndex = ref(0)
    const editBindApiActionExpression = ref()
    let editorInstance
    const createEditor = (id, readOnly, needWrap = false) => {
      editorInstance = monaco.editor.create(document.getElementById(id) as HTMLElement, {
        roundedSelection: true,
        scrollBeyondLastLine: false,
        readOnly,
        language: 'javascript'
      })
      let code = editBindApiActionModel.value.code || '// write your code, Try entering rst\r\n'
      if (needWrap) {
        code = `new Promise((resolve) => {
${code}
})`
      }
      editorInstance.setValue(code)
    }
    watch([addDialogVisible, codeDialogVisible], ([new1, new2]) => {
      if (!new1 && !new2) editorInstance = null
    })

    const loadAction = () => {
      ydhl.get('api/action.json', { uuid: myApi.value.uuid }, (rst: any) => {
        if (rst.success) {
          bindApiActions.value = rst.data.bind_actions
          // 代码提示
          // // monaco.languages.typescript.javascriptDefaults.addExtraLib()
          // monaco.languages.registerCompletionItemProvider('javascript', {
          //   triggerCharacters: ['.'],
          //   provideCompletionItems (model, position, context, token) {
          //     // console.log(model, position, context, token)
          //     return {
          //       suggestions: rst.data.suggestions?.[myApi.value.output[currOutputIndex.value].uuid] || []
          //     }
          //   }
          // })
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
            loadAction()
            return
          }
          ydhl.alert(rst.msg || t('common.operationFail'))
        })
      })
    }
    const switchToCode = () => {
      editBindApiActionModel.value.mode = 'code'
      nextTick(() => {
        createEditor('codeeditor', false)
      })
    }
    const showCode = (bindApiAction) => {
      editBindApiActionModel.value = JSON.parse(JSON.stringify(bindApiAction))
      editBindApiActionExpression.value = editBindApiActionModel.value.expression || {}

      codeDialogVisible.value = true
      nextTick(() => {
        createEditor('previeweditor', true, true)
      })
    }
    const addBindApiAction = () => {
      editBindApiActionModel.value = { mode: 'setting', code: '', actions: [] }
      editBindApiActionExpression.value = {}
      addDialogVisible.value = true
    }
    const addAction = (type) => {
      editBindApiActionModel.value.actions.push({ type })
    }
    const removeAction = (index) => {
      editBindApiActionModel.value.actions.splice(index, 1)
    }
    const postRemoveAction = (bindApiAction, index) => {
      const actionUuid = bindApiAction.actions[index].uuid

      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: actionUuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }

        bindApiAction.actions.splice(index, 1)
      })
    }
    const modifyBindApiAction = (bindApiAction) => {
      editBindApiActionModel.value = JSON.parse(JSON.stringify(bindApiAction))
      editBindApiActionExpression.value = editBindApiActionModel.value.expression || {}
      addDialogVisible.value = true
      if (myApi.value.output) {
        currOutputIndex.value = myApi.value.output.findIndex((item) => {
          return item.uuid === bindApiAction.output_data_id
        })
        if (currOutputIndex.value < 0) currOutputIndex.value = 0
      } else {
        currOutputIndex.value = 0
      }
      if (editBindApiActionModel.value.mode === 'code') {
        switchToCode()
      }
    }
    const save = (callback:any) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        const data: any = JSON.parse(JSON.stringify(editBindApiActionModel.value))
        data.page_uuid = selectedPage.value.meta.id
        data.type = 'bind_api'
        data.code = editorInstance ? editorInstance.getValue() : ''
        data.expression = editBindApiActionExpression.value
        data.from_uuid = myApi.value.uuid
        data.output_data_id = myApi.value.output[currOutputIndex.value].uuid
        ydhl.postJson('api/action/saveapiaction.json', data).then((rst: any) => {
          ydhl.closeLoading(dialogId)
          if (rst.success) {
            editBindApiActionModel.value = rst.data
            editBindApiActionExpression.value = editBindApiActionModel.value.expression || {}
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
            loadAction()
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
      loadAction()
    })

    const beforeSave = (index, callback) => {
      save(() => {
        callback(JSON.parse(JSON.stringify(editBindApiActionModel.value.actions[index])))
      })
    }
    const changeOutputIndex = (outputIndex) => {
      currOutputIndex.value = outputIndex
      // 改变输出结构后，action绑定的本地变量需要清空
      for (const action of editBindApiActionModel.value.actions) {
        action.input = {}
      }
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
    const getLocalArgs = (bindApiAction) => {
      const rst = myApi.value.output?.find((item) => item.uuid === bindApiAction.output_data_id)
      if (rst) {
        const data = JSON.parse(JSON.stringify(rst.body))
        data.name = 'rst'
        data.title = myApi.value.name
        return [data]
      }
      return []
    }
    const editBindApiActionVariable = computed(() => {
      const rst = JSON.parse(JSON.stringify(myApi.value.output[currOutputIndex.value]?.body))
      rst.name = 'rst'
      rst.title = myApi.value.name
      const v = [rst]
      if (myApi.value.localVariables) {
        v.unshift(...myApi.value.localVariables)
      }
      // console.log(v)
      return v
    })

    const sortEventAction = (bindEvent, { moved }) => {
      const index = {}
      for (const idx in bindEvent.actions) {
        index[bindEvent.actions[idx].uuid] = idx
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
      currOutputIndex,
      addDialogVisible,
      codeDialogVisible,
      showRedirectVisible,
      showPageDataVisible,
      buttons,
      pageDatas,
      tplDatas,
      editBindApiActionExpression,
      sortEventAction,
      postRemoveAction,
      getLocalArgs,
      addAction,
      changeOutputIndex,
      beforeSave,
      switchToCode,
      showCode,
      removeBindApiAction,
      addBindApiAction,
      modifyBindApiAction,
      showRedirect,
      showPageData
    }
  }
}
</script>
