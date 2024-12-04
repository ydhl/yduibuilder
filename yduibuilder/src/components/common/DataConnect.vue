<template>
  <div :class="{'model-item ': true, 'bg-light':checkedUuid==myModel.uuid}" @click="isCheck?check():''">
    <div class="model-field flex-shrink-0 text-truncate" @click="toggle" :style="'padding-left: ' + (intent * 16) + 'px'">
      <i v-if="myModel.type=='object' && !hasBound" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-if="myModel.type!='object' || hasBound" style="width: 16px;height: 24px;">&nbsp;</i>
      <div class="pointer hover-text-primary" @click.stop="viewDetail">{{myModel.name || 'ROOT'}}</div>
      <span :class="'ps-1 fs-7 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7">
        {{myModel.title}}
      </span>
    </div>
    <span class="text-muted fs-7" @click.stop="showComment()">
        {{myModel.comment}}
    </span>
    <div v-if="isCheck" class="pe-2" @click.stop="check" >
      <i :class="{'iconfont': true, 'icon-checked text-primary fw-bold': checkedUuid==myModel.uuid, 'icon-unchecked':checkedUuid!=myModel.uuid}"></i>
    </div>
    <div v-else-if="readonly" class="flex-shrink-0">
      <span v-if="hasBound" class="text-muted pe-2">
        <template v-if="connect=='to'">←</template>
        <template v-if="connect=='from'">→</template>
        {{boundExpression.desc}}
      </span>
    </div>
    <div v-else class="d-flex flex-nowrap flex-shrink-0 align-items-center justify-content-end">
      <template v-if="connect=='to'">
        <ExpressionDropdown :variables="variables" @updateExpression="updateExpression" :has-mutation-operator="false"
                            :expression="boundExpression.expression" :left-value="myModel"
                            :left-value-path="path"></ExpressionDropdown>
      </template>
      <template v-else>
        <span class="pointer" @click="openBoundDialog"><span class="text-success" v-if="boundExpression.desc"> → {{boundExpression.desc}} </span><i v-else class="iconfont icon-connect"></i></span>
      </template>
    </div>
  </div>

  <template v-if="myModel.type=='object'  && isOpen">
    <template v-if="!hasBound">
      <DataConnect v-for="(item, index) in myModel.props" :bound-data="boundData" :variables="variables"
                   @checked="checked" :is-check="isCheck"
                   @updateConnectData="updateConnectData" :connect="connect"
                        :path="myPath" :rootUuid="rootUuid" :checked-uuid="checkedUuid"
                   :key="index" :intent="intent+1" :model="item" :index="index"></DataConnect>
    </template>
  </template>
  <DataInfo v-model="detailDlgVisible" :data="myModel"></DataInfo>
  <lay-layer v-model="connectDataDialogVisible" resize :title="t('variable.bound')" :shade="true" :area="['500px', '500px']" :btn="connectDataDialogButtons">
    <div class="p-3">
      <h3 class="text-success text-center mb-3">{{myPath}} →</h3>
      <DataCheckPanel :local-variables="variables" @updateChecked="updateChecked" :checked-uuid="boundExpression?.expression?.data?.id" :page-uuid="currPage.meta.id"></DataCheckPanel>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import { useStore } from 'vuex'
import { DataStruct, Expression } from '@/store/model'
import DataInfo from '@/components/common/DataInfo.vue'
import ExpressionDropdown from '@/components/common/ExpressionDropdown.vue'
import ydhl from '@/lib/ydhl'

// 数据绑定数据，可绑定其他变量或者固定值
export default {
  name: 'DataConnect',
  components: { ExpressionDropdown, DataInfo },
  props: {
    model: Object, // DataStruct
    connect: String, // to 选择的数据（from）绑定给model（to）； from model（from）绑定给选择的数据（to）
    isCheck: Boolean, // 是否是选择模式
    checkedUuid: String, // 选择模式下选中的数据uuid
    boundData: Object, // 绑定的输入 Expression结构 { literal: 字面量， path：变量路径 }
    variables: Array, // 局部变量
    index: Number,
    intent: Number, // 缩进次数
    rootUuid: String, // model所在根节点记录的uuid
    path: String, // 从根到自己到访问路径
    readonly: Boolean // 从根到自己到访问路径
  },
  emits: ['updateConnectData', 'checked'],
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const isOpen = ref(true)
    const detailDlgVisible = ref(false)
    const connectDataDialogVisible = ref(false)
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const myPath = computed(() => (props.path ? props.path + '.' : '') + (myModel.value.name || ''))

    const hasBound = computed(() => {
      return props.boundData?.[myModel.value.uuid]
    })
    const boundExpression = ref<any>(hasBound.value ? JSON.parse(JSON.stringify(hasBound.value)) : { expression: {}, desc: '' })
    watch(hasBound, () => {
      boundExpression.value = hasBound.value ? JSON.parse(JSON.stringify(hasBound.value)) : { expression: {}, desc: '' }
    })
    const viewDetail = () => {
      detailDlgVisible.value = true
    }
    const { t } = useI18n()
    const showComment = () => {
      layer.confirm(props.model.comment || 'no doc', { title: 'YDUIBuilder' })
    }
    const connectDataDialogButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          connectDataDialogVisible.value = false
          updateConnectData(props.rootUuid, myPath.value, myModel.value.uuid, boundExpression.value?.expression, false, boundExpression.value?.desc)
        }
      },
      {
        text: t('common.cleanup'),
        callback: () => {
          connectDataDialogVisible.value = false
          updateConnectData(props.rootUuid, myPath.value, myModel.value.uuid, boundExpression.value?.expression, true, '')
        }
      }
    ])
    const openBoundDialog = () => {
      connectDataDialogVisible.value = true
    }

    const updateChecked = ({ scope, path, data, rootDataId }) => {
      if (data.type !== 'any' && myModel.value && data.type !== myModel.value.type && myModel.value.type !== 'any') {
        ydhl.alert(t('variable.boundTypeMismatch', [myModel.value.type]))
      }
      boundExpression.value = {
        expression: {
          data: {
            scope, id: data.uuid, path, fromUuid: rootDataId, name: data.name, type: data.type
          }
        },
        desc: path
      }
    }

    const toggle = () => {
      if (myModel.value.type === 'object') {
        isOpen.value = !isOpen.value
      }
    }

    /**
     * From 是界面上展示的数据， ToData是选择关联的数据，但实际上谁（from）关联给谁（to），由最顶层的组件处理，比如API.vue
     * @param fromRootUuid
     * @param fromPath
     * @param fromUuid
     * @param toData
     * @param remove
     */
    const updateConnectData = (fromRootUuid, fromPath, fromUuid, toData: Expression, remove = false, desc: string) => {
      context.emit('updateConnectData', fromRootUuid, fromPath, fromUuid, toData, remove, desc)
    }
    const updateExpression = (expression, desc) => {
      updateConnectData(props.rootUuid, myPath.value, myModel.value.uuid, expression, !expression?.type, desc)
    }
    /**
     * 选择的数据
     * @param path 访问路径
     * @param data 数据结构体
     * @param rootDataId 数据所在的根数据uuid
     */
    const checked = (path, data: DataStruct, rootDataId) => {
      context.emit('checked', path, data, rootDataId)
    }
    const check = () => {
      if (!props.isCheck) return
      checked(myPath.value, myModel.value, props.rootUuid)
    }

    return {
      t,
      myModel,
      detailDlgVisible,
      connectDataDialogVisible,
      hasBound,
      boundExpression,
      currPage,
      isOpen,
      myPath,
      connectDataDialogButtons,
      viewDetail,
      check,
      updateChecked,
      updateExpression,
      toggle,
      showComment,
      checked,
      updateConnectData,
      openBoundDialog
    }
  }
}
</script>
