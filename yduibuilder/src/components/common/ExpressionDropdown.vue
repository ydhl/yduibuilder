<template>
  <div class="d-flex align-items-center justify-content-end w-100">
    <template v-if="hasMutationOperator">
      <AdvanceSelect :options="mutationOperators" btn-size="btn-xs" :default-text="myDefaultMutationOperator || t('expression.operator')" @change="(option) => updateMutationOperator(option.value)">
        <template #input>
          <CustomModifier :prefix="leftValue.name" :default-modifier="defaultMutationOperator" :data-name="myExpressionDesc||''" @update="(modifier) => updateMutationOperator(modifier)"></CustomModifier>
        </template>
      </AdvanceSelect>
    </template>
    <template v-if="myExpression?.type == 'literal'">
      <template v-if="!hideArrow">←</template>
      <div v-if="readonly" class="text-success w-75 text-truncate">{{myExpression.literal}}</div>
      <template v-else>
        <template v-if="leftValue?.enumValue">
          <AdvanceSelect :options="formatEnumValue" :default-text="myExpression.literal" @change="(option)=>changeEnumValue(option.value)"></AdvanceSelect>
        </template>
        <template v-else>
          <button type="button" @click="openCodeEditor('literal')" class="btn btn-xs text-success me-1 fs-7 w-75 text-truncate">{{myExpression?.literal || t('action.notSet')}}</button>
        </template>
      </template>
    </template>
    <template v-else-if="myExpression?.type == 'code'">
      <template v-if="!hideArrow">←</template>
      <div class="text-success w-75 text-truncate fs-7" v-if="readonly">{{myExpression.code}}</div>
      <template v-else>
        <button type="button" @click="openCodeEditor('code')" class="btn btn-xs text-success me-1 fs-7 w-75 text-truncate">{{myExpression?.code || t('action.notSet')}}</button>
      </template>
    </template>
    <div v-else-if="myExpression?.type == 'connect'" :title="myExpression.data?.path ? myExpression.data?.path : ''" @click="!readonly ? connectDataDialogVisible=true : ''" class="pointer text-danger w-50 text-truncate">
      <template v-if="myExpression.data?.name">
        <template v-if="!hideArrow">←</template> {{myExpression.data.name}}
      </template>
      <span v-else class="fs-7">
        <i class="iconfont icon-connect hover-primary"></i>{{t('action.notSet')}}
      </span>
    </div>
    <template v-else-if="myExpression?.type">
      <template v-if="!hideArrow">←</template>
      <span @click="!readonly ? openCodeEditor('code') : ''" class="text-primary fs-7 me-1" :title="myExpressionDesc">{{myExpressionDesc || t('action.notSet')}}</span>
    </template>
    <span class="fs-7" v-if="myDefaultMutationOperatorEndWithBracket">)</span>
    <div v-if="!readonly && (!hasMutationOperator || (hasMutationOperator && defaultMutationOperator))" class="flex-shrink-0 d-flex align-items-center text-muted">&nbsp;{
      <AdvanceSelect :options="mutationTypes"  btn-size="btn-xs" :default-text="myExpression?.type ? t('expression.'+myExpression?.type)  : t('variable.rightValue')" @change="(option) => changeMutationType(option.value)"></AdvanceSelect>
      }&nbsp;
    </div>
  </div>
  <lay-layer v-model="connectDataDialogVisible" :title="t('variable.bound')" resize :shade="true" :area="['500px', '500px']"
             :btn="connectDataDialogButtons">
    <div class="p-3">
      <h3 class="text-success text-center mb-3">{{leftValuePath?leftValuePath+'.':''}}{{leftValue?.name}} <template v-if="!hideArrow">←</template></h3>
      <DataCheckPanel :local-variables="variables" @updateChecked="updateChecked"
                 :checked-uuid="myExpression.data?.id" :page-uuid="selectedPageId"/>
    </div>
  </lay-layer>
  <CodeEditor v-model="codeDlgVisible" :left-operator="defaultMutationOperator" :language="codeType === 'literal' ? 'json' : 'javascript'"
              :left-value-path="leftValuePath" :left-data="leftValue" :variables="variables"
              :schema="leftValueSchema" :code="code" @update="updateCode"></CodeEditor>
</template>

<script lang="ts">
import AdvanceSelect from './AdvanceSelect.vue'
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
import CodeEditor from '@/components/common/CodeEditor.vue'
import { Expression } from '@/store/model'
import CustomModifier from '@/components/common/CustomModifier.vue'

// 设置表达式的dropdown菜单
export default {
  name: 'ExpressionDropdown',
  props: {
    variables: Object, // 本地变量
    readonly: Boolean,
    expression: Object,
    leftValue: Object, // 左值
    leftValuePath: String, // 左值访问路径
    hideArrow: Boolean,
    hasMutationOperator: {
      default: true,
      type: Boolean
    }, // 能否自定义赋值运算
    defaultMutationOperator: String
  },
  emits: ['updateExpression', 'updateMutationOperator'],
  components: { CustomModifier, CodeEditor, AdvanceSelect },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myExpression = ref<Expression>(props.expression) // 通过引用的方式通知上层
    const connectDataDialogVisible = ref(false)
    const codeDlgVisible = ref(false)
    const codeType = ref('expression')
    const mutationTypes = computed(() => {
      const menu = [
        { name: t('variable.rightValue') + ':', disabled: true },
        { name: t('variable.literal'), value: 'literal', desc: t('variable.literalTip') },
        { name: t('expression.connect'), value: 'connect', desc: t('expression.connectTip') },
        // { name: t('expression.expression'), value: 'expression', desc: t('expression.expressionTip') }
        { name: t('expression.code'), value: 'code', desc: t('expression.expressionTip') } // 用代码写表达式
      ]
      if (myExpression.value?.type) {
        menu.push({ name: '', value: '', desc: '' })
        menu.push({ name: t('common.remove'), value: 'remove', desc: t('expression.removeMutation') })
      }
      return menu
    })
    const mutationOperators = computed<string>(() => {
      const _: any = [
        { header: t('expression.operator') },
        { name: '=@', value: '=@', desc: t('variable.assignOperator') }
      ]
      if (props.leftValue?.type === 'array') {
        _.push({ name: '.push(@)', value: '.push(@)', desc: t('variable.pushDesc') })
        _.push({ name: '.unshift(@)', value: '.unshift(@)', desc: t('variable.unshiftDesc') })
      }
      _.push({ header: t('common.custom') })
      _.push({ name: t('common.custom'), value: '', desc: t('expression.customOperator'), input: true })
      return _
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
    const myDefaultMutationOperator = computed(() => {
      return props.defaultMutationOperator?.replace(/@/, '').replace(/\([^)]*\)/, '(')
    })
    const myDefaultMutationOperatorEndWithBracket = computed(() => {
      return props.defaultMutationOperator?.trim().endsWith(')')
    })
    const leftValueSchema = computed(() => {
      // 如果操作是数组，并且操作的是数组项目，那么只用数组项的定义
      if (props.leftValue === 'array' || props.defaultMutationOperator !== '=') {
        return ydhl.getModelJSONSchema(props.leftValue?.item)
      }
      return ydhl.getModelJSONSchema(props.leftValue)
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

    const openCodeEditor = (type: string) => {
      codeType.value = type
      if (type === 'literal') {
        code.value = myExpression.value.literal as string
      } else {
        code.value = myExpression.value.code as string
      }

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
      if (codeType.value === 'literal') {
        if (!myExpression.value.data) myExpression.value.data = {}
        myExpression.value.literal = code
      } else {
        myExpression.value.code = code
      }
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
        if (!props.leftValue?.enumValue) openCodeEditor('literal') // 不是枚举的字面量则弹出编辑器
      } else if (type === 'connect') {
        connectDataDialogVisible.value = true
      } else if (type === 'code') {
        // 修改为直接编写代码
        openCodeEditor('code')
      }
    }
    const updateExpression = (expression) => {
      myExpression.value = expression
      context.emit('updateExpression', myExpression.value, myExpressionDesc.value)
    }
    const updateMutationOperator = (operator) => {
      context.emit('updateMutationOperator', operator)
    }

    return {
      t,
      mutationTypes,
      connectDataDialogVisible,
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
      changeMutationType,
      updateMutationOperator,
      myDefaultMutationOperator,
      myDefaultMutationOperatorEndWithBracket,
      codeType,
      mutationOperators,
      leftValueSchema
    }
  }
}
</script>
