<template>
  <div @click="!readonly ? openDialog() : ''" class="pointer">
    <template v-if="myValidate.datas?.length > 0" >
        <div v-for="(validateData, index) in myValidate.datas" :key="index" class="d-flex align-items-center w-100 text-truncate">
          <span>{{validateData.fullName}}</span>
          <span class="text-muted fs-7">&nbsp;{{validateData.title}}</span>
          <span class="text-danger">&nbsp;{{validateData.validRegular || (validateData.validRule ? t(`api.model.valid.${validateData.validRule}`) : '')}}</span>
        </div>
        <template v-if="myValidate?.trueActions?.length > 0">
          <div class="fs-7 mt-1">{{t('api.model.valid.whenPass')}}:</div>
          <div v-for="(action, actionIndex) in myValidate.trueActions" :key="actionIndex"
               class="p-1 pe-0 ps-3 d-flex align-items-center">
            <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
            <EventAction :action="action" :readonly="true"></EventAction>
          </div>
        </template>

        <template v-if="myValidate?.falseActions?.length > 0">
          <div class="fs-7 mt-1">{{t('api.model.valid.whenFail')}}:</div>
          <div v-for="(action, actionIndex) in myValidate.falseActions" :key="actionIndex"
               class="p-1 pe-0 ps-3 d-flex align-items-center">
            <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
            <EventAction :action="action" :readonly="true"></EventAction>
          </div>
        </template>
    </template>
    <div v-else>{{ t('action.notSet') }}</div>
  </div>

  <!-- API Dialog -->
  <lay-layer v-model="dialogVisible" layer-classes="layui-layer-content-overflow"
             :title="t('action.validate')"  resize :shade="true" :area="['800px', '60vh']" :btn="buttons">
    <div class="p-2 d-flex gap-2">
      <div class="w-50">
        <template v-if="queryDatas && queryDatas.length > 0">
          <template v-for="(queryData, index) in queryDatas" :key="index">
            <DataPicker @checked="updateValidate" :checked-uuids="checkedUuids" path="page" :root-uuid="queryData.uuid"
                         :index="0" :model="queryData" :intent="0"></DataPicker>
          </template>
        </template>
        <template v-if="pageDatas && pageDatas.length > 0">
          <template v-for="(pageData, index) in pageDatas" :key="index">
            <DataPicker @checked="updateValidate" :checked-uuids="checkedUuids" path="page" :root-uuid="pageData.uuid"
                         :index="0" :model="pageData" :intent="0"></DataPicker>
          </template>
        </template>
      </div>
      <div class="w-50">
        <div class="row mb-3">
          <label class="col-form-label">{{t("api.model.valid.whenPass")}}</label>
          <div class="">
            <button type="button" class="btn btn-sm btn-light mb-1" @click.stop="addAction('trueActions')">{{t('common.add')}}</button>
            <draggable :list="editValidate.trueActions" handle=".icon-drag"  @change="(n) => sortAction(n, 'trueActions')">
              <transition-group>
                <div v-for="(action, actionIndex) in editValidate.trueActions" :key="action.uuid"
                     class="list-group-item border-0 list-group-item-action p-1 pe-0 ps-3 d-flex align-items-center">
                  <i class="iconfont icon-drag text-muted" style="cursor: move"></i>
                  <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
                  <EventAction :action="action" bind-type="bind_event"
                               :bind-uuid="editValidate.uuid" :key="actionIndex"></EventAction>
                  <ConfirmRemove @remove="deleteAction(actionIndex, action, 'trueActions')"></ConfirmRemove>
                </div>
              </transition-group>
            </draggable>
          </div>
        </div>
        <div class="row">
        <label class="col-form-label">{{t("api.model.valid.whenFail")}}</label>
        <div>
          <button type="button" class="btn btn-sm btn-light mb-1" @click.stop="addAction('falseActions')">{{t('common.add')}}</button>
          <draggable :list="editValidate.falseActions" handle=".icon-drag"  @change="(n) => sortAction(n, 'falseActions')">
            <transition-group>
              <div v-for="(action, actionIndex) in editValidate.falseActions" :key="action.uuid"
                   class="list-group-item border-0 list-group-item-action p-1 pe-0 ps-3 d-flex align-items-center">
                <i class="iconfont icon-drag text-muted" style="cursor: move"></i>
                <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
                <EventAction :action="action" bind-type="bind_event"
                             :bind-uuid="editValidate.uuid" :key="actionIndex"></EventAction>
                <ConfirmRemove @remove="deleteAction(actionIndex, action, 'falseActions')"></ConfirmRemove>
              </div>
            </transition-group>
          </draggable>
        </div>
      </div>
      </div>
    </div>
  </lay-layer>

  <!-- bind action Dialog -->
  <lay-layer v-model="addActionDlgVisible" layer-classes="layui-layer-content-overflow"
             :title="t('common.action')" :shade="true" :area="['420px', '250px']" :btn="addActionButtons">
    <div class="ps-4 pe-4 pt-2 pb-2">
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">
          <label>{{t('common.action')}}</label>
        </div>
        <div class="col-auto">
          <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes"
                         :default-text="bindAction.name || t('action.add')" @change="(option)=>bindAction = option"></AdvanceSelect>
        </div>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { ref, toRef, computed, nextTick } from 'vue'
import ydhl from '@/lib/ydhl'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import DataPicker from '@/components/common/DataPicker.vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import { VueDraggableNext } from 'vue-draggable-next'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import Util from '@/components/Util'

export default {
  name: 'ValidateSetting',
  components: {
    AdvanceSelect,
    ConfirmRemove,
    DataPicker,
    draggable: VueDraggableNext
  },
  props: {
    readonly: Boolean,
    modelValue: Object,
    variables: Object,
    eventName: String,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const dialogVisible = ref(false)
    const pageDatas = ref<any>([])
    const queryDatas = ref<any>([])
    const myAction = toRef(props, 'modelValue')
    const addActionDlgVisible = ref(false)
    let addActionType = ''
    const myValidate = ref<any>(myAction.value.validate || {})
    const { t } = useI18n()
    const util = Util()
    const store = useStore()
    const editValidate = ref<any>({})
    const bindAction = ref({ value: 'popup', name: t('action.popup') })
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)
    const checkedUuids = computed(() => {
      const uuids: any = []
      for (const data of myValidate.value?.datas) {
        uuids.push(data.uuid)
      }
      return uuids
    })
    const actionTypes = computed(() => {
      return util.getActions('redirect', 'popup', 'webapi', 'emit', 'mutation', 'interval', 'break')
    })
    const loadData = () => {
      ydhl.loading(t('common.pleaseWait')).then((dialogId) => {
        ydhl.get('api/bind/data.json?page_uuid=' + selectedPageId.value, [], (rst) => {
          ydhl.closeLoading(dialogId)
          if (!rst?.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          pageDatas.value = rst.data.page
          queryDatas.value = rst.data.query
          dialogVisible.value = true
        }, 'json')
      })
    }

    const openDialog = () => {
      loadData()
      editValidate.value = JSON.parse(JSON.stringify(myValidate.value))
    }
    const update = () => {
      myAction.value.validate = JSON.parse(JSON.stringify(editValidate.value))
      myValidate.value = JSON.parse(JSON.stringify(editValidate.value))
      // console.log(myValidate.value)
      context.emit('update:modelValue', myAction)
    }
    const buttons = computed(() => {
      const btns = [
        {
          text: t('common.add'),
          callback: () => {
            dialogVisible.value = false
            if (!props.autosave) {
              update()
              return
            }
            const validate = JSON.parse(JSON.stringify(editValidate.value))
            delete validate.trueActions
            delete validate.falseActions
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              ydhl.postJson('api/action/validate.json',
                {
                  page_uuid: selectedPageId.value,
                  action_uuid: myAction.value.uuid,
                  validate
                }).then((rst) => {
                ydhl.closeLoading(id)
                update()
              })
            })
          }
        },
        {
          text: t('common.cancel'),
          callback: () => {
            dialogVisible.value = false
          }
        }
      ]
      return btns
    })

    const addActionButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          addSubAction()
        }
      },
      {
        text: t('common.cancel'),
        callback: () => {
          addActionDlgVisible.value = false
        }
      }
    ])
    const updateValidate = (path, data, rootDataId, removed) => {
      if (removed) {
        const index = editValidate.value?.datas?.findIndex(item => item.uuid === data.uuid)
        if (index !== -1) {
          editValidate.value.datas.splice(index, 1)
        }
      } else {
        if (editValidate.value.datas.findIndex(item => item.uuid === data.uuid) === -1) {
          data.from_uuid = rootDataId
          data.fullName = path
          editValidate.value.datas.push(data)
        }
      }
    }

    const sortAction = ({ moved }, type: string) => {
      const index = {}
      for (const idx in editValidate.value[type]) {
        index[editValidate.value[type][idx].uuid] = idx
      }
      ydhl.postJson('api/action/sort.json', {
        page_uuid: selectedPageId.value, index
      })
    }

    const saveAction = (actionUuid = '') => {
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.post('api/action/addaction.json', { page_uuid: selectedPageId.value, type: addActionType, action_uuid: myAction.value.uuid || actionUuid, name: bindAction.value.value }, [], (rst) => {
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          if (!editValidate.value[addActionType]) editValidate.value[addActionType] = []
          editValidate.value[addActionType].push(rst.data)
        })
      })
    }
    const deleteAction = (index, action, type) => {
      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: action.uuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        editValidate.value[type].splice(index, 1)
      })
    }

    const addSubAction = () => {
      if (!bindAction.value?.value) {
        ydhl.alert(t('event.error.bindActionEmpty'), t('common.ok'))
        return
      }

      addActionDlgVisible.value = false
      if (!myAction.value.uuid) { // 对应的action不存在，这时先通知上层组件先把action在后端保存起来; 主要时bindApiPostprocessor在对话框整体保存数据的情况
        context.emit('beforeSave', (newAction) => {
          nextTick(() => {
            saveAction()
          })
        })
      } else {
        saveAction()
      }
    }
    const addAction = (type) => {
      addActionType = type
      addActionDlgVisible.value = true
    }
    return {
      dialogVisible,
      myAction,
      t,
      pageDatas,
      queryDatas,
      checkedUuids,
      bindAction,
      editValidate,
      openDialog,
      updateValidate,
      sortAction,
      deleteAction,
      addAction,
      addActionButtons,
      actionTypes,
      myValidate,
      selectedPageId,
      buttons,
      addActionDlgVisible
    }
  }
}
</script>
