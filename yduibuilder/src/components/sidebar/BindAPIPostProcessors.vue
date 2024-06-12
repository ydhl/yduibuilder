<template>
  <div class="d-flex align-items-center justify-content-end mb-2">
    <button class="btn btn-sm ps-1 pe-1 pt-0 pb-0 btn-primary" type="button" @click="addBindAction()">{{t('action.add')}}</button>
  </div>
  <div v-for="(bindAction,index) in bindActions" :key="index" class="p-1">
    <div class="d-flex align-items-center ps-1 pe-1 bg-light ">
      <div class="flex-grow-1 text-truncate">
        <template v-if="bindAction.mode=='setting'">
          <template v-if="bindAction.expression_desc">
            <div class="text-truncate text-success mb-1">{{bindAction.expression_desc}}</div>
          </template>
          <div v-else class="text-info mb-1">{{ t('action.notSet') }}</div>
        </template>
        <template v-else>
          <div class="text-secondary mb-1 pointer" @click="showCode(bindAction)">{{ t('common.customCode') }}</div>
        </template>
      </div>

      <i  @click="modifyBindAction(bindAction)" class="iconfont icon-edit pointer"></i>
      <ConfirmRemove @remove="removeBindAction(bindAction)"></ConfirmRemove>
    </div>
    <div v-if="!bindAction.actions || bindAction.actions.length==0" class="text-muted">{{t('action.noActionDefined')}}</div>
    <div v-else class="list-group list-group-flush">
      <draggable :list="bindAction.actions" handle=".icon-drag"  @change="(n) => sortEventAction(bindAction, n)">
        <transition-group>
          <div class="list-group-item border-0 list-group-item-action p-1 d-flex align-items-center" v-for="(action, idx) in bindAction.actions" :key="action.uuid">
            <div class="me-1"><i class="iconfont icon-drag text-muted" style="cursor: move"></i></div>
            <EventAction bind-type="bind_action" :bind-uuid="bindAction.uuid" :action="action"
                         :variables="getLocalArgs(bindAction)" @beforeCreatePopupBind="(callback) => beforeCreatePopupBind(idx, callback)"></EventAction>
            <ConfirmRemove @remove="postRemoveAction(bindAction, idx)"></ConfirmRemove>
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
            <button type="button" @click="editBindActionModel.mode = 'setting'" :class="{'btn': true,' btn-outline-secondary': editBindActionModel.mode != 'setting', 'btn-secondary': editBindActionModel.mode == 'setting'}">{{ t('action.settingMode') }}</button>
            <button type="button" @click="switchToCode()" :class="{'btn': true, 'btn-outline-secondary': editBindActionModel.mode != 'code', 'btn-secondary': editBindActionModel.mode == 'code'}">{{ t('action.codeMode') }}</button>
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
            <template v-if="editBindActionModel.mode=='code'">
              <div class="text-muted p-1">{{t('action.codeModeDesc')}}</div>
              <div><span style="color: blue">new </span><span style="color: #51a8a8">Promise</span>((resolve)=>{</div>
              <div id="codeeditor" style="height: 300px"></div>
              <div>})</div>
            </template>
            <template v-if="editBindActionModel.mode=='setting'">
              <div class="d-flex justify-content-start">
                <Expression :variables="editBindActionVariable" :expression="editBindActionExpression"></Expression>
              </div>
            </template>
          </template>
        </div>
      </div>
      <div class="w-50">
        <div class="text-muted">
          {{t('action.conditionDesc')}}
          <AdvanceSelect btn-size="btn-sm" :options="actionTypes" :default-text="t('action.add')" @click="(option)=>addAction(option.value)"></AdvanceSelect>
        </div>
        <draggable :list="editBindActionModel.actions" handle=".card-header"  @change="(n) => sortEventAction(editBindActionModel, n)">
          <transition-group>
            <div class="card mt-2" v-for="(action, index) in editBindActionModel.actions" :key="action.uuid">
              <div class="card-header justify-content-between" style="cursor: move">
                <i class="iconfont icon-drag text-muted"></i>
                {{t('action.' + action.type)}}
                <ConfirmRemove @remove="removeAction(index)"></ConfirmRemove>
              </div>
              <div class="card-body">
                <EventAction :autosave="false" bind-type="bind_action" :bind-uuid="editBindActionModel.uuid" :popup-page-data-inline="true" :action="action"
                             :variables="editBindActionVariable" @beforeCreatePopupBind="(callback) => beforeCreatePopupBind(index, callback)">
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
    const editBindActionModel = ref<any>({ mode: 'setting', code: '', actions: [] })
    const currAction = ref<any>({})
    const actionTypes = computed(() => {
      const types = [
        { name: t('action.output'), value: 'output', desc: t('action.outputDataDesc') },
        { name: t('action.redirect'), value: 'redirect', desc: t('action.redirectDesc') },
        { name: t('action.mutation'), value: 'mutation', desc: t('action.mutationDesc') },
        { name: t('action.popup'), value: 'popup', desc: t('action.popupDesc') },
        { name: t('action.webapi'), value: 'webapi', desc: t('action.webapiDesc') }
      ]
      if (selectedPage.value.pageType === 'component') {
        types.push({ name: t('action.emit'), value: 'emit', desc: t('action.emitDesc') })
      }
      return types
    })
    const bindActions = ref([])
    const currOutputIndex = ref(0)
    const editBindActionExpression = ref()
    let editorInstance
    const createEditor = (id, readOnly, needWrap = false) => {
      editorInstance = monaco.editor.create(document.getElementById(id) as HTMLElement, {
        roundedSelection: true,
        scrollBeyondLastLine: false,
        readOnly,
        language: 'javascript'
      })
      let code = editBindActionModel.value.code || '// write your code, Try entering rst\r\n'
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
          bindActions.value = rst.data.bind_actions
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
    const removeBindAction = (bindAction) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        ydhl.post('api/action/remove.json', { uuid: bindAction.uuid }, [], (rst: any) => {
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
      editBindActionModel.value.mode = 'code'
      nextTick(() => {
        createEditor('codeeditor', false)
      })
    }
    const showCode = (bindAction) => {
      editBindActionModel.value = JSON.parse(JSON.stringify(bindAction))
      editBindActionExpression.value = editBindActionModel.value.expression || {}

      codeDialogVisible.value = true
      nextTick(() => {
        createEditor('previeweditor', true, true)
      })
    }
    const addBindAction = () => {
      editBindActionModel.value = { mode: 'setting', code: '', actions: [] }
      editBindActionExpression.value = {}
      addDialogVisible.value = true
    }
    const addAction = (type) => {
      editBindActionModel.value.actions.push({ type })
    }
    const removeAction = (index) => {
      editBindActionModel.value.actions.splice(index, 1)
    }
    const postRemoveAction = (bindAction, index) => {
      const actionUuid = bindAction.actions[index].uuid

      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: actionUuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }

        bindAction.actions.splice(index, 1)
      })
    }
    const modifyBindAction = (bindAction) => {
      editBindActionModel.value = JSON.parse(JSON.stringify(bindAction))
      editBindActionExpression.value = editBindActionModel.value.expression || {}
      addDialogVisible.value = true
      if (myApi.value.output) {
        currOutputIndex.value = myApi.value.output.findIndex((item) => {
          return item.uuid === bindAction.output_data_id
        })
        if (currOutputIndex.value < 0) currOutputIndex.value = 0
      } else {
        currOutputIndex.value = 0
      }
      if (editBindActionModel.value.mode === 'code') {
        switchToCode()
      }
    }
    const save = (callback:any) => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId: any) => {
        const data: any = JSON.parse(JSON.stringify(editBindActionModel.value))
        data.page_uuid = selectedPage.value.meta.id
        data.type = 'bind_api'
        data.code = editorInstance ? editorInstance.getValue() : ''
        data.expression = editBindActionExpression.value
        data.from_uuid = myApi.value.uuid
        data.output_data_id = myApi.value.output[currOutputIndex.value].uuid
        ydhl.postJson('api/action/save.json', data).then((rst: any) => {
          ydhl.closeLoading(dialogId)
          if (rst.success) {
            editBindActionModel.value = rst.data
            editBindActionExpression.value = editBindActionModel.value.expression || {}
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

    const beforeCreatePopupBind = (index, callback) => {
      save(() => {
        callback(editBindActionModel.value.actions[index])
      })
    }
    const changeOutputIndex = (outputIndex) => {
      currOutputIndex.value = outputIndex
      // 改变输出结构后，action绑定的本地变量需要清空
      for (const action of editBindActionModel.value.actions) {
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
    const getLocalArgs = (bindAction) => {
      const rst = myApi.value.output?.find((item) => item.uuid === bindAction.output_data_id)
      if (rst) {
        const data = JSON.parse(JSON.stringify(rst.body))
        data.name = 'rst'
        data.title = myApi.value.name
        return [data]
      }
      return []
    }
    const editBindActionVariable = computed(() => {
      const rst = JSON.parse(JSON.stringify(myApi.value.output[currOutputIndex.value]?.body))
      rst.name = 'rst'
      rst.title = myApi.value.name
      const v = [rst]
      if (myApi.value.localVariables) {
        v.unshift(...myApi.value.localVariables)
      }
      console.log(v)
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
      bindActions,
      editBindActionModel,
      editBindActionVariable,
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
      editBindActionExpression,
      sortEventAction,
      postRemoveAction,
      getLocalArgs,
      addAction,
      changeOutputIndex,
      beforeCreatePopupBind,
      switchToCode,
      showCode,
      removeBindAction,
      addBindAction,
      modifyBindAction,
      showRedirect,
      showPageData
    }
  }
}
</script>
