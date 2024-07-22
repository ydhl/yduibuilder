<template>
  <div class="d-flex align-items-center justify-content-end flex-grow-1">
    <template v-if="myExpression?.type == 'literal'">
      ←
      <div class="text-success text-truncate" v-if="readonly">{{myExpression.literal}}</div>
      <template v-else>
        <template v-if="leftValue?.enumValue">
          <AdvanceSelect :options="formatEnumValue" :default-text="myExpression.literal" @change="(option)=>changeEnumValue(option.value)"></AdvanceSelect>
        </template>
        <template v-else>
          <button type="button" @click="openCodeEditor" class="btn btn-xs text-success me-1 btn-light text-truncate">{{myExpression?.literal || t('action.notSet')}}</button>
        </template>
      </template>
    </template>
    <div v-else-if="myExpression?.type == 'connect'" :title="myExpression.data?.path ? myExpression.data?.path : ''" @click="!readonly ? connectDataDialogVisible=true : ''" class="pointer text-danger text-truncate">
      <template v-if="myExpression.data?.name">
        ← {{myExpression.data.name}}
      </template>
      <template v-else>
        <i class="iconfont icon-connect hover-primary"></i>
      </template>
    </div>
    <template v-else-if="myExpression?.type">
      ←
      <span @click="!readonly ? openExpressionVisible = true : ''" class="text-primary me-1" :title="myExpressionDesc">{{myExpressionDesc || t('action.notSet')}}</span>
    </template>
    <div v-if="!readonly" class="flex-shrink-0 d-flex align-items-center bg-light text-muted">&nbsp;{
      <AdvanceSelect :options="mutationTypes" :default-text="myExpression?.type ? t('expression.'+myExpression?.type)  : ''" @change="(option) => changeMutationType(option.value)"></AdvanceSelect>
      }&nbsp;
    </div>
  </div>
  <lay-layer v-model="connectDataDialogVisible" :title="t('variable.bound')" resize :shade="true" :area="['500px', '500px']"
             :btn="connectDataDialogButtons">
    <div class="p-3">
      <h3 class="text-success text-center mb-3">{{leftValuePath?leftValuePath+'.':''}}{{leftValue?.name}} ←</h3>
      <DataCheckPanel :local-variables="variables" @updateChecked="updateChecked"
                 :checked-uuid="myExpression.data?.id" :page-uuid="selectedPageId"/>
    </div>
  </lay-layer>
  <CodeEditor v-model="codeDlgVisible" :code="code" @update="updateCode"></CodeEditor>
  <ExpressionEditor v-model="openExpressionVisible" :variables="variables" @update="updateExpression" :expression="myExpression"></ExpressionEditor>
</template>

<script lang="ts">
import AdvanceSelect from './AdvanceSelect.vue'
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import CodeEditor from '@/components/common/CodeEditor.vue'
import ExpressionEditor from '@/components/common/ExpressionEditor.vue'
import { Expression } from '@/store/model'

// 设置表达式的dropdown菜单
export default {
  name: 'ExpressionDropdown',
  props: {
    variables: Object, // 本地变量
    readonly: Boolean,
    expression: Object,
    leftValue: Object, // 左值
    leftValuePath: String // 左值访问路径
  },
  emits: ['updateExpression'],
  components: { ExpressionEditor, CodeEditor, AdvanceSelect },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myExpression = ref<Expression>(props.expression) // 通过引用的方式通知上层
    const connectDataDialogVisible = ref(false)
    const openExpressionVisible = ref(false)
    const codeDlgVisible = ref(false)
    const mutationTypes = computed(() => {
      const menu = [
        { name: t('variable.literal'), value: 'literal', desc: t('variable.literalTip') },
        { name: t('expression.connect'), value: 'connect', desc: t('expression.connectTip') },
        { name: t('expression.expression'), value: 'expression', desc: t('expression.expressionTip') }
      ]
      if (myExpression.value?.type) {
        menu.push({ name: '', value: '', desc: '' })
        menu.push({ name: t('common.remove'), value: 'remove', desc: t('expression.removeMutation') })
      }
      return menu
    })
    const connectDataDialogButtons = ref([
      {
        text: t('common.ok'),
        callback: () => {
          connectDataDialogVisible.value = false
        }
      }
    ])

    const store = useStore()
    const code = ref('')
    const myExpressionDesc = computed(() => {
      return ydhl.getExpressionDesc(myExpression.value)
    })
    const formatEnumValue = computed(() => {
      if (!props.leftValue || !props.leftValue.enumValue) return []
      const rst: any = []
      for (const name in props.leftValue.enumValue) {
        rst.push({ name, value: name, desc: props.leftValue.enumValue[name] })
      }
      return rst
    })
    const selectedPageId = computed(() => store.state.design.page?.meta?.id)

    const openCodeEditor = () => {
      code.value = myExpression.value.literal as string
      codeDlgVisible.value = true
    }
    const changeEnumValue = (value) => {
      if (!myExpression.value.data) myExpression.value.data = {}
      myExpression.value.literal = value
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }
    const updateChecked = ({ scope, path, data, rootDataId }) => {
      if (props.leftValue && data.type !== props.leftValue?.type && props.leftValue?.type !== 'any') {
        ydhl.alert(t('variable.boundTypeMismatch', [props.leftValue.type]))
      }
      myExpression.value.data = {
        fromUuid: rootDataId,
        scope: scope,
        id: data.uuid,
        type: data.type,
        path: path,
        name: data.name
      }
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }
    const updateCode = (code) => {
      codeDlgVisible.value = false
      if (!myExpression.value.data) myExpression.value.data = {}
      myExpression.value.literal = code
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }

    const clearExpression = (expression) => {
      delete expression.type
      delete expression.data
      delete expression.rightData
      delete expression.operator
      delete expression.desc
      delete expression.literal
      delete expression.expression
      delete expression.subexpression
      delete expression.rightExpression
      delete expression.trueExpression
      delete expression.falseExpression
    }
    const changeMutationType = (type) => {
      if (type === 'remove') {
        delete myExpression.value.type
        context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
        return
      }
      clearExpression(myExpression.value)
      myExpression.value.type = type
      if (type === 'literal') {
        if (!props.leftValue?.enumValue) openCodeEditor() // 不是枚举的字面量则弹出编辑器
      } else if (type === 'connect') {
        connectDataDialogVisible.value = true
      } else if (type === 'expression') {
        openExpressionVisible.value = true
      }
    }
    const updateExpression = (expression) => {
      myExpression.value = expression
      openExpressionVisible.value = false
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }

    return {
      t,
      mutationTypes,
      connectDataDialogVisible,
      openExpressionVisible,
      codeDlgVisible,
      myExpression,
      connectDataDialogButtons,
      selectedPageId,
      formatEnumValue,
      code,
      myExpressionDesc,
      updateExpression,
      updateCode,
      updateChecked,
      openCodeEditor,
      changeEnumValue,
      changeMutationType
    }
  }
}
</script>
