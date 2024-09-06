<template>
  <div @click="!readonly?openDialog():''" class="pointer">
    <template v-if="myAction.interval" >
      <div class="fs-7">
        {{t('action.intervalDuration')}}: <span class="text-success fw-bold">{{myInterval.duration}}</span> ms&nbsp;&nbsp;
        {{t('action.intervalDelay')}}: <span class="text-success fw-bold"> {{myInterval.delay}}</span> ms
      </div>
      <div class="fs-7 mt-1">{{t('action.intervalAction')}}:</div>
      <div v-for="(action, actionIndex) in myInterval.action" :key="actionIndex"
           class="p-1 pe-0 ps-3 d-flex align-items-center">
        <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
        <EventAction :action="action" :readonly="true"></EventAction>
      </div>
      <template v-if="myInterval.complete">
        <div class="fs-7 mt-1">{{t('action.completedAction')}}:</div>
        <div v-for="(action, actionIndex) in myInterval.complete" :key="actionIndex"
             class="p-1 pe-0 ps-3 d-flex align-items-center">
          <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
          <EventAction :action="action" :readonly="true"></EventAction>
        </div>
      </template>
    </template>
    <div v-else>{{ t('action.notSet') }}</div>
  </div>

  <!-- API Dialog -->
  <lay-layer v-model="dialogVisible" :title="t('action.interval')" resize layer-classes="layui-layer-content-overflow" :shade="true" :area="['800px', '400px']" :btn="buttons">
    <div class="p-2">
      <div class="d-flex mb-3">
        <div class="row">
          <label class="col-sm-6 col-form-label text-end">{{t("action.intervalDuration")}}</label>
          <div class="col-sm-6 d-flex">
            <div class="input-group">
              <input type="number" class="form-control form-control-sm" value="1000" placeholder="" v-model="editInterval.duration">
              <div class="input-group-text">ms</div>
            </div>
          </div>
        </div>
        <div class="row">
          <label class="col-sm-6 col-form-label text-end">{{t("action.intervalDelay")}}</label>
          <div class="col-sm-6">
            <div class="input-group">
              <input type="number" class="form-control form-control-sm" value="1000" placeholder="" v-model="editInterval.delay">
              <div class="input-group-text">ms</div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-3 col-form-label text-end">{{t("action.intervalAction")}}</label>
        <div class="col-sm-9">
          <button type="button" class="btn btn-sm btn-light mb-1" @click.stop="addAction('action')">{{t('common.add')}}</button>
          <draggable :list="editInterval.action" handle=".icon-drag"  @change="(n) => sortEventAction(n, 'action')">
            <transition-group>
              <div v-for="(action, actionIndex) in editInterval.action" :key="action.uuid"
                   class="list-group-item border-0 list-group-item-action p-1 pe-0 ps-3 d-flex align-items-center">
                <i class="iconfont icon-drag text-muted" style="cursor: move"></i>
                <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
                <EventAction :action="action" :bind-type="bindType"
                             :bind-uuid="bindUuid" :key="actionIndex"
                             :variables="myVariables"></EventAction>
                <ConfirmRemove @remove="deleteAction(actionIndex, action, 'action')"></ConfirmRemove>
              </div>
            </transition-group>
          </draggable>
        </div>
      </div>
      <div class="row mb-3">
        <label class="col-sm-3 col-form-label text-end">{{t("action.completedAction")}}</label>
        <div class="col-sm-9">
          <button type="button" class="btn btn-sm btn-light mb-1" @click.stop="addAction('complete')">{{t('common.add')}}</button>
          <draggable :list="editInterval.complete" handle=".icon-drag"  @change="(n) => sortEventAction(n, 'complete')">
            <transition-group>
              <div v-for="(action, actionIndex) in editInterval.complete" :key="action.uuid"
                   class="list-group-item border-0 list-group-item-action p-1 pe-0 ps-3 d-flex align-items-center">
                <i class="iconfont icon-drag text-muted" style="cursor: move"></i>
                <i :class="'iconfont text-danger me-1 icon-' + action.type"></i>
                <EventAction :action="action" :bind-type="bindType"
                             :bind-uuid="bindUuid" :key="actionIndex"
                             :variables="myVariables"></EventAction>
                <ConfirmRemove @remove="deleteAction(actionIndex, action, 'complete')"></ConfirmRemove>
              </div>
            </transition-group>
          </draggable>
        </div>
      </div>
    </div>
  </lay-layer>

  <!-- bind action Dialog -->
  <lay-layer v-model="addActionDlgVisible" layer-classes="layui-layer-content-overflow"  :title="t('common.action')" :shade="true" :area="['420px', '250px']" :btn="addActionButtons">
    <div class="ps-4 pe-4 pt-2 pb-2">
      <div class="row g-3 mt-1 align-items-center">
        <div class="col-sm-2">
          <label>{{t('common.action')}}</label>
        </div>
        <div class="col-auto">
          <AdvanceSelect btn-size="btn-sm btn-light" :options="actionTypes" :default-text="bindAction.name || t('action.add')" @change="(option)=>bindAction = option"></AdvanceSelect>
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
import { VueDraggableNext } from 'vue-draggable-next'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import Util from '@/components/Util'

export default {
  name: 'IntervalSetting',
  props: {
    modelValue: Object,
    readonly: Boolean,
    variables: Object,
    bindType: String,
    bindUuid: String,
    autosave: {
      default: true,
      type: Boolean
    } // 是否自动提交接口保存
  },
  emits: ['update:modelValue', 'beforeSave'],
  components: {
    AdvanceSelect,
    ConfirmRemove,
    draggable: VueDraggableNext
  },
  setup (props: any, context: any) {
    const dialogVisible = ref(false)
    const addActionDlgVisible = ref(false)
    let addActionType = ''
    const myAction = toRef(props, 'modelValue')
    const myInterval = computed(() => myAction.value.interval || {})
    const myVariables = computed(() => {
      const _ = props.variables || []
      _.push({ type: 'number', name: 'remainTime', path: 'remainTime', title: t('variable.remaintime') + ' ms', uuid: 'remainTime' })
      return _
    })
    const editInterval = ref<any>({})
    const { t } = useI18n()
    const store = useStore()
    const util = Util()
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)
    const bindAction = ref({ value: 'mutation', name: t('action.mutation') })
    const actionTypes = computed(() => {
      if (addActionType !== 'action') {
        return util.getActions('mutation', 'webapi', 'validate', 'redirect', 'popup')
      } else {
        return util.getActions('mutation', 'webapi', 'validate')
      }
    })
    const openDialog = () => {
      editInterval.value = JSON.parse(JSON.stringify(myInterval.value))
      dialogVisible.value = true
    }
    const update = () => {
      myAction.value.interval = JSON.parse(JSON.stringify(editInterval.value))
      context.emit('update:modelValue', myAction)
    }

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
    const buttons = computed(() => {
      const btns = [
        {
          text: t('common.ok'),
          callback: () => {
            dialogVisible.value = false
            if (!props.autosave) {
              update()
              return
            }
            ydhl.loading(t('common.pleaseWait')).then((id) => {
              const data: any = {
                duration: editInterval.value.duration,
                delay: editInterval.value.delay,
                action: [],
                complete: [],
                page_uuid: selectedPageId.value,
                action_uuid: myAction.value.uuid
              }
              for (const action of editInterval.value?.action) {
                data.action.push(action.id)
              }
              for (const action of editInterval.value?.complete) {
                data.complete.push(action.id)
              }
              ydhl.postJson('api/action/interval.json',
                data).then((rst) => {
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

    const sortEventAction = ({ moved }, type: string) => {
      const index = {}
      for (const idx in editInterval.value[type]) {
        index[editInterval.value[type][idx].uuid] = idx
      }
      ydhl.postJson('api/action/sort.json', {
        page_uuid: selectedPageId.value, index
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
    const saveAction = (actionUuid = '') => {
      ydhl.loading(t('common.pleaseWait')).then((dialodId) => {
        ydhl.post('api/action/addaction.json', { page_uuid: selectedPageId.value, type: addActionType, action_uuid: myAction.value.uuid || actionUuid, name: bindAction.value.value }, [], (rst) => {
          ydhl.closeLoading(dialodId)
          if (!rst.success) {
            ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
            return
          }
          if (!editInterval.value[addActionType]) editInterval.value[addActionType] = []
          editInterval.value[addActionType].push(rst.data)
        })
      })
    }
    const deleteAction = (index, action, type) => {
      ydhl.post('api/action/deleteaction.json', { page_uuid: selectedPageId.value, action_uuid: action.uuid }, [], (rst) => {
        if (!rst.success) {
          ydhl.alert(rst.msg || t('common.operationFail'), t('common.ok'))
          return
        }
        editInterval.value[type].splice(index, 1)
      })
    }

    const addAction = (type) => {
      addActionType = type
      addActionDlgVisible.value = true
    }
    return {
      dialogVisible,
      addActionDlgVisible,
      addActionButtons,
      sortEventAction,
      deleteAction,
      addAction,
      myVariables,
      bindAction,
      actionTypes,
      myInterval,
      myAction,
      t,
      openDialog,
      editInterval,
      buttons
    }
  }
}
</script>
