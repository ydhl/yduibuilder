<template>
  <div class="model-item pt-1 pb-1" @click="toggle">
    <div class="model-field text-truncate w-50" :style="'min-width: 200px;padding-left: ' + (intent * 16) + 'px'">
      <i v-if="myModel.type=='object' && (myModel.props && myModel.props.length>0 && !defaultMutation?.[myModel.uuid]?.expression)" :class="{'iconfont':true, 'icon-tree-close': !isOpen, 'icon-tree-open': isOpen}"></i>
      <i v-else style="width: 16px;height: 24px;">&nbsp;</i>
      <div class="pointer hover-text-primary" @click.stop="viewDetail">{{myModel.name}}</div>
      <span :class="'ps-1 fs-7 param-' + myModel.type">
        {{myModel.type}}
      </span>
      <span class="text-truncate ps-2 pointer text-muted fs-7" @click.stop="showComment()">
        {{myModel.title}}
      </span>
    </div>
    <!--title-->
    <div class="model-title d-flex flex-nowrap justify-content-end flex-grow-1 w-50">
      <ExpressionDropdown :variables="variables" :hide-arrow="true" :expression="expression" :has-mutation-operator="true"
                          :default-mutation-operator="mutationOperator" @updateMutationOperator="updateMutationOperator"
                          @updateExpression="updateExpression" :leftValue="myModel" :leftValuePath="path"></ExpressionDropdown>
    </div>
  </div>
  <template v-if="myModel.type=='object' && isOpen && !defaultMutation?.[myModel.uuid]?.expression">
    <template v-for="(item, index) in myModel.props" :key="index" >
      <DataMutation :default-value="defaultValue" :variables="variables" :from-uuid="fromUuid" :path="(path ? path+'.' : '')+(myModel.name||'')"
                   :intent="intent+1" :model="item" :index="index"></DataMutation>
    </template>
  </template>
  <DataInfo v-model="detailDlgVisible" :data="myModel"></DataInfo>

</template>

<script lang="ts">
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { layer } from '@layui/layer-vue'
import DataInfo from '@/components/common/DataInfo.vue'
import { Expression, Mutation } from '@/store/model'
import ExpressionDropdown from '@/components/common/ExpressionDropdown.vue'
// 给数据赋值
export default {
  name: 'DataMutation',
  components: { ExpressionDropdown, DataInfo },
  props: {
    model: Object,
    defaultValue: Object,
    variables: Object,
    // 来源于那个表
    fromUuid: String,
    index: Number,
    intent: Number, // 缩进次数
    path: String // 从根到自己到访问路径
  },
  setup (props: any, context: any) {
    const myModel = computed<any>(() => props.model)
    const defaultMutation = ref<Mutation>(props.defaultValue)
    const codeDlgVisible = ref(false)
    const detailDlgVisible = ref(false)
    const connectDataDialogVisible = ref(false)
    const { t } = useI18n()
    const expression = computed<Expression>(() => {
      return defaultMutation.value?.[myModel.value.uuid]?.expression || {}
    })
    const mutationOperator = computed<string>(() => {
      return defaultMutation.value?.[myModel.value.uuid]?.mutation_operator || ''
    })
    const code = ref('')
    const isOpen = ref(true)

    const showComment = () => {
      layer.confirm(props.model.comment || 'no doc', { title: 'YDUIBuilder' })
    }

    const viewDetail = () => {
      detailDlgVisible.value = true
    }
    const toggle = () => {
      if (myModel.value.type === 'object') {
        isOpen.value = !isOpen.value
      }
    }

    const updateExpression = (expression, expDesc) => {
      defaultMutation.value[myModel.value.uuid].expression = expression
      defaultMutation.value[myModel.value.uuid].expression_code = expDesc
    }
    const updateMutationOperator = (operator) => {
      defaultMutation.value[myModel.value.uuid].mutation_operator = operator
    }

    return {
      t,
      detailDlgVisible,
      connectDataDialogVisible,
      codeDlgVisible,
      isOpen,
      myModel,
      code,
      expression,
      defaultMutation,
      mutationOperator,
      viewDetail,
      toggle,
      showComment,
      updateExpression,
      updateMutationOperator
    }
  }
}
</script>
