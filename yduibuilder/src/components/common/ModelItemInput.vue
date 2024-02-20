<template>
  <div class="model-item pt-1 pb-1">
    <div class="model-field text-truncate ps-1" :style="'width:0px;padding-left: ' + (intent * 16) + 'px'">
      {{myModel.name}}
      <span :class="'ps-1 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7" @click.stop="showComment()">
        {{myModel.title}}
      </span>
    </div>
    <!--title-->
    <div class="model-title">
      <template v-if="myModel.type=='array' || myModel.type=='object'">
        <button class="btn btn-sm btn-light pt-0 pb-0" type="button" @click="openBoundDialog">{{ boundInfo || t('action.notSet')}}</button>
      </template>
      <template v-else>
        <div class="dropup">
          <button type="button" class="btn btn-light btn-sm dropdown-toggle pt-0 pb-0" data-bs-auto-close="outside"
                  data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
           <span :class="{'text-danger':isLiteral}">{{boundInfo || t('action.notSet')}}</span>
          </button>
          <ul class="dropdown-menu  dropdown-menu-lg-end">
            <li><a class="dropdown-item" href="javascript:void(0)" @click="openBoundDialog">{{t('variable.bound')}}</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><h6 class="dropdown-header">{{ t('variable.literal') }}</h6></li>
            <li class="p-2"><input type="text" class="form-control form-control-sm" v-model="literalValue"/></li>
          </ul>
        </div>
      </template>
    </div>
  </div>

  <lay-layer v-model="variableDialogVisible" :title="t('variable.bound')" :shade="true" :area="['500px', '500px']" :btn="variableDlgButtons">
    <div class="p-3">
      <VariableBound :local-variables="variables" :hide-sub-data="true" @updateCheckedUuid="updateCheckedUuid" :checked-uuid="boundVariableUuid?.data_uuid" :page-uuid="currPage.meta.id"></VariableBound>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { computed, ref, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import VariableBound from '@/components/common/VariableBound.vue'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
export default {
  name: 'ModelItemInput',
  components: { VariableBound },
  props: {
    model: Object,
    boundInput: Object,
    variables: Array,
    index: Number,
    intent: Number, // 缩进次数
    path: String // 从根到自己到访问路径
  },
  emits: ['updateBoundInput'],
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const literalValue = ref(props.boundInput?.[myModel.value.uuid]?.literal || '')
    const variableDialogVisible = ref(false)
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const boundInfo = computed(() => {
      if (props.boundInput?.[myModel.value.uuid]?.literal) return props.boundInput[myModel.value.uuid].literal
      return props.boundInput?.[myModel.value.uuid] ? props.boundInput?.[myModel.value.uuid]?.path : null
    })
    const isLiteral = computed(() => {
      if (props.boundInput?.[myModel.value.uuid]?.literal) return true
      return false
    })
    const boundVariableUuid = ref(props.boundInput?.[myModel.value.uuid])
    watch(literalValue, (v) => {
      updateBoundInput(myModel.value.uuid, { literal: v })
    })
    const updateBoundInput = (uuid, v) => {
      context.emit('updateBoundInput', uuid, v)
    }
    const { t } = useI18n()
    const isOpen = ref(true)
    const showComment = () => {
      layer.confirm(props.model.comment || 'no doc', { title: 'YDUIBuilder' })
    }
    const updateCheckedUuid = ({ scope, path, data }) => {
      boundVariableUuid.value = { scope, data_uuid: data.uuid, path }
      if (data.type !== myModel.value.type) {
        updateBoundInput(myModel.value.uuid, {})
        ydhl.alert(t('variable.boundTypeMismatch', [myModel.value.type]))
        return
      }
      updateBoundInput(myModel.value.uuid, boundVariableUuid.value)
    }

    const openBoundDialog = () => {
      variableDialogVisible.value = true
    }
    const variableDlgButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          variableDialogVisible.value = false
        }
      }
    ])

    return {
      t,
      isOpen,
      myModel,
      literalValue,
      isLiteral,
      boundInfo,
      variableDialogVisible,
      variableDlgButtons,
      boundVariableUuid,
      currPage,
      updateCheckedUuid,
      showComment,
      openBoundDialog,
      updateBoundInput
    }
  }
}
</script>
