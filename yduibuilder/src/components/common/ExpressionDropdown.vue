<template>
  <div class="d-flex align-items-center justify-content-end flex-grow-1">
    <template v-if="myExpression.type == 'literal'">
      ←
      <template v-if="leftValue?.enumValue">
        <AdvanceSelect :options="formatEnumValue" :default-text="myExpression.literal" @click="(option)=>changeEnumValue(option.literal)"></AdvanceSelect>
      </template>
      <template v-else>
        <button type="button" @click="openCodeEditor" class="btn btn-xs text-success me-1 btn-light text-truncate">{{myExpression?.literal || t('action.notSet')}}</button>
      </template>
    </template>
    <div v-else-if="myExpression?.type == 'connect'" :title="myExpression.data?.path ? myExpression.data?.path : ''" @click="connectDataDialogVisible=true" class="pointer text-danger text-truncate">
      <template v-if="myExpression.data?.name">
        ← {{myExpression.data.name}}
      </template>
      <template v-else>
        <i class="iconfont icon-connect hover-primary"></i>
      </template>
    </div>
    <template v-else-if="myExpression?.type">
      ←
      <span @click="openExpressionVisible = true" style="width: 100px" class="text-primary me-1 text-truncate" :title="myExpressionDesc">{{myExpressionDesc || t('action.notSet')}}</span>
    </template>
    <div class="flex-shrink-0">
      <AdvanceSelect :options="mutationTypes" :default-text="myExpression?.type ? t('expression.'+myExpression?.type)  : ''" @click="(option) => changeMutationType(option.value)"></AdvanceSelect>
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
        // { name: 'invert', value: 'invert', desc: 'Reverse the current value' },
        // { name: '?:', value: '?:', desc: 'Ternary conditional operator: first ? second : third' }
      ]
      if (myExpression.value.type) {
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
      return getExpression(myExpression.value)
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

    const getExpression = (expression: any) => {
      if (expression.type === 'expression_group') {
        const rst: any = []
        const hasSubexpression = expression.subexpression.length > 0
        if (hasSubexpression) rst.push('(')
        for (const sub of expression.subexpression) {
          rst.push(getExpression(sub))
        }
        if (hasSubexpression) rst.push(')')
        return rst.join(' ')
      } else if (expression.type === 'expression') {
        return getConditionExpression(expression)
      } else if (expression.type === 'ternary') {
        const rst: any = []
        if (expression.expression) {
          rst.push(getExpression(expression.expression))
        } else {
          rst.push(getConditionExpression(expression))
        }
        rst.push('?')
        rst.push(getExpression(expression.trueExpression))
        rst.push(':')
        rst.push(getExpression(expression.falseExpression))
        return rst.join(' ')
      } else if (expression.type === 'literal') {
        return expression.literal
      } else if (expression.type === 'connect') {
        return expression.data?.path
      } else if (expression.type === 'operator') {
        return expression.operator
      }
      return ''
    }
    const getConditionExpression = (expression: any) => {
      const modifier = { not: '!' }
      const rst: any = []
      if (expression.data) {
        if (expression.data.modifier)rst.push(modifier[expression.data.modifier])
        rst.push(expression.data.path || expression.data.literal)
      } else if (expression.expression) {
        rst.push(getExpression(expression.expression))
      }
      if (expression.rightData) {
        rst.push(expression.operator)
        if (expression.rightData.modifier)rst.push(modifier[expression.rightData.modifier])
        rst.push(expression.rightData.path || expression.rightData.literal)
      } else if (expression.rightExpression) {
        rst.push(getExpression(expression.rightExpression))
      }
      return rst.join(' ')
    }
    const openCodeEditor = () => {
      code.value = myExpression.value.literal as string
      codeDlgVisible.value = true
    }
    const changeEnumValue = (value) => {
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
      myExpression.value.literal = code
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }

    const clearExpression = (expression) => {
      delete expression.type
      delete expression.data
      delete expression.rightData
      delete expression.operator
      delete expression.literal
      delete expression.desc
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
        openCodeEditor()
      } else if (type === 'connect') {
        connectDataDialogVisible.value = true
      } else if (type === 'expression') {
        openExpressionVisible.value = true
      }
    }
    const updateExpression = (expression) => {
      myExpression.value = expression
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
