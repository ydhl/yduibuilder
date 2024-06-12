<template>
  <div :class="['gap-1 expression', 'expression-' + deep, hovered ? 'expression-hover' : '']" @mouseover.stop.prevent="hovered = true" @mouseout.stop.prevent="hovered = false">
    <div v-if="myExpression.subexpression" class="expression-group gap-1">
      <template v-for="(subexpression, index) in myExpression.subexpression" :key="index">
        <Expression :index="index" :variables="variables" :deep="deep+1" :expression="subexpression" @remove="removeSubExpression"></Expression>
      </template>
    </div>
    <div v-else-if="myExpression.type=='ternary'" class="d-flex align-items-center">
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.expression"></Expression>
      <span>&nbsp;?&nbsp;</span>
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.trueExpression"></Expression>
      <span>&nbsp;:&nbsp;</span>
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.falseExpression"></Expression>
    </div>
    <template v-else-if="myExpression.type=='operator'">
      <div class="dropdown">
        <button class="btn btn-xs bg-transparent rounded-1" type="button" data-bs-toggle="dropdown"><span class="expression-op">{{myExpression.operator}}</span></button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
          <li v-for="(type, index) in ['and','or','xor']" :key="index"><a href="javascript:void(0)" @click="myExpression.operator = type" class="dropdown-item">{{ type }}</a></li>
        </ul>
      </div>
    </template>
    <template v-else-if="myExpression.type=='literal'">
      <span class="expression-literal" @click="openCodeEditor">{{ myExpression.literal || t('action.notSet') }}</span>
    </template>
    <template v-else-if="myExpression.type=='connect'">
      <span class="expression-data pointer" @click="changeData('left', myExpression.data?.id)">{{ myExpression.data?.path }}</span>
    </template>
    <template v-else>
      <template v-if="myExpression.data?.id">
        <span :class="['expression-data pointer d-flex align-items-center', myExpression.data?.modifier ? 'expression-modifier-'+myExpression.data.modifier : '']"  @click="changeData('left', myExpression.data?.id)">
          {{ myExpression.data?.path }}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="modifierTypes" v-if="modifierTypes.length>0" @click="(option) => changeModifier('left', option.value)"></AdvanceSelect>
        </span>
      </template>
      <template v-else-if="myExpression.data?.literal != undefined">
        <span class="expression-literal pointer d-flex align-items-center" @click="openCodeEditor('left')" >
          {{ myExpression.data?.literal || t('action.notSet')}}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="dataTypes" @click="(option) => changeModifier('left', option.value)"></AdvanceSelect>
        </span>
      </template>
      <div class="dropdown d-flex" v-if="myExpression.operator">
        <button class="btn btn-xs bg-transparent rounded-1" type="button" data-bs-toggle="dropdown"><span class="expression-op">{{myExpression.operator}}</span></button>
        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
          <li v-for="(type, index) in ['==','!=','<','<=','>','>=']" :key="index"><a href="javascript:void(0)" @click="myExpression.operator = type" class="dropdown-item">{{ type }}</a></li>
        </ul>
      </div>
      <Expression :deep="deep+1" :variables="variables" v-if="myExpression.rightExpression" :expression="myExpression.rightExpression"></Expression>
      <template v-if="myExpression.rightData?.id">
        <span :class="['expression-data pointer d-flex align-items-center', myExpression.rightData?.modifier ? 'expression-modifier-'+myExpression.rightData.modifier : '']"  @click="changeData('right', myExpression.rightData?.id)">
          {{ myExpression.rightData?.path }}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="rightModifierTypes" v-if="rightModifierTypes.length>0" @click="(option) => changeModifier('right', option.value)"></AdvanceSelect>
        </span>
      </template>
      <template v-else-if="myExpression.rightData?.literal != undefined">
        <span class="expression-literal pointer d-flex align-items-center" @click="openCodeEditor('right')">
          {{ myExpression.rightData?.literal || t('action.notSet')}}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="dataTypes" @click="(option) => changeModifier('right', option.value)"></AdvanceSelect>
        </span>
      </template>
    </template>
    <div class="dropdown d-flex" v-if="hasSetting">
      <i class="iconfont icon-more hover-primary fs-7" data-bs-toggle="dropdown"></i>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <li v-if="myExpression.type != 'ternary'"><a href="javascript:void(0)" @click="changeToThird" class="dropdown-item">{{t('expression.changeToTernary')}}</a></li>
        <li v-if="canAddExpression"><a href="javascript:void(0)" @click="addSubExpression" class="dropdown-item">{{t('expression.addSubExpression')}}</a></li>
        <li v-if="canAddData"><a href="javascript:void(0)" @click="addData" class="dropdown-item">{{t('expression.addData')}}</a></li>
        <li v-if="canAddData"><a href="javascript:void(0)" @click="addLiteral" class="dropdown-item">{{t('expression.addLiteral')}}</a></li>
        <li><a href="javascript:void(0)" @click="clear" class="dropdown-item">{{t('expression.clear')}}</a></li>
        <li v-if="canRemove"><a href="javascript:void(0)" @click="remove" class="dropdown-item">{{t('common.delete')}}</a></li>
      </ul>
    </div>
  </div>
  <lay-layer v-model="chooseDataVisible" :title="t('expression.data')" :shade="true" :area="['800px', '400px']" :btn="chooseButtons">
    <div class="p-3">
      <DataCheckPanel :local-variables="variables" @updateChecked="chooseData" :checked-uuid="checkUuid" :page-uuid="selectedPageId"></DataCheckPanel>
    </div>
  </lay-layer>
  <CodeEditor v-model="codeDlgVisible" :code="code" @update="updateCode"></CodeEditor>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import DataCheckPanel from '@/components/common/DataCheckPanel.vue'
import { useStore } from 'vuex'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import { Expression as ExpressionModel } from '@/store/model'
import CodeEditor from '@/components/common/CodeEditor.vue'

// 表达式展示
export default {
  name: 'Expression',
  components: { CodeEditor, AdvanceSelect, DataCheckPanel },
  props: {
    deep: {
      type: Number,
      default: 1
    },
    index: Number, // 表示在expression group中的索引
    expression: Object,
    variables: Object
  },
  emits: ['remove'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const chooseDataVisible = ref(false)
    const codeDlgVisible = ref(false)
    const hovered = ref(false)
    const chooseDataType = ref('left')
    const code = ref('')
    const checkUuid = ref('')
    const modifierTypes = computed(() => {
      const _: any = []
      _.push({ name: t('expression.changeToLiteral'), value: 'literal', desc: '' })
      if (myExpression.value.data?.type === 'boolean') {
        _.push({ name: t('expression.not'), value: 'not', desc: '' })
      }
      if (myExpression.value.data?.modifier) {
        _.push({ name: '' })
        _.push({ name: t('common.remove'), value: 'remove', desc: '' })
      }
      return _
    })
    const rightModifierTypes = computed(() => {
      const _: any = []
      _.push({ name: t('expression.changeToLiteral'), value: 'literal', desc: '' })
      if (myExpression.value.rightData?.type === 'boolean') {
        _.push({ name: t('expression.not'), value: 'not', desc: '' })
      }
      if (myExpression.value.rightData?.modifier) {
        _.push({ name: '' })
        _.push({ name: t('common.remove'), value: 'remove', desc: '' })
      }
      return _
    })
    const hasSetting = computed(() => {
      return !myExpression.value.type || ['operator'].indexOf(myExpression.value.type) === -1
    })
    const dataTypes = computed(() => {
      const _: any = []
      _.push({ name: 'Change to data', value: 'data', desc: '' })
      return _
    })

    const selectedPageId = computed(() => store.state.design.page?.meta?.id)
    const myExpression = ref<ExpressionModel>(props.expression) // 通过引用的方式通知上层
    const chooseButtons = computed(() => [{
      text: t('common.ok'),
      callback: () => {
        chooseDataVisible.value = false
      }
    }])
    const canAddData = computed(() => {
      if (myExpression.value.type && myExpression.value.type !== 'expression') return false
      return !myExpression.value.data || !myExpression.value.rightData
    })
    const canRemove = computed(() => {
      return props.index !== undefined
    })
    const canAddExpression = computed(() => {
      return !myExpression.value.type || ['expression', 'expression_group'].indexOf(myExpression.value.type) !== -1
    })

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
    const clear = () => {
      clearExpression(myExpression.value)
    }
    const remove = () => {
      context.emit('remove', props.index)
    }
    const removeSubExpression = (index) => {
      if (!myExpression.value.subexpression) return
      if (index === myExpression.value.subexpression?.length - 1) {
        myExpression.value.subexpression.splice(index - 1)
      } else {
        myExpression.value.subexpression.splice(index, 2)
      }
    }
    const changeData = (type, dataId) => {
      chooseDataType.value = type
      checkUuid.value = dataId
      chooseDataVisible.value = true
    }
    const addData = () => {
      myExpression.value.type = 'expression'
      if (!myExpression.value.data) {
        changeData('left', '')
      } else {
        changeData('right', '')
      }
    }
    const addLiteral = () => {
      if (!myExpression.value.type) {
        myExpression.value.type = 'literal'
      } else {
        const key = !myExpression.value.data ? 'data' : 'rightData'
        myExpression.value[key] = { literal: '' }
        if (key === 'rightData' && !myExpression.value.operator) myExpression.value.operator = '=='
      }
    }
    const changeToThird = () => {
      const expression = JSON.parse(JSON.stringify(myExpression.value))
      clearExpression(myExpression.value)
      myExpression.value.type = 'ternary'
      myExpression.value.expression = expression
      myExpression.value.trueExpression = {}
      myExpression.value.falseExpression = {}
    }
    const addSubExpression = () => {
      // 当前表达式不是分组的话，就变成分组，是分组的话就添加子表达式
      if (myExpression.value.type !== 'expression_group') {
        const subexpression = [JSON.parse(JSON.stringify(myExpression.value))]
        clearExpression(myExpression.value)
        myExpression.value.subexpression = subexpression
        myExpression.value.type = 'expression_group'
      } else {
        if (!myExpression.value.subexpression) myExpression.value.subexpression = []
        if (myExpression.value.subexpression?.length > 0) {
          myExpression.value.subexpression?.push({ type: 'operator', operator: 'and' })
        }
        myExpression.value.subexpression?.push({})
      }
    }
    const changeModifier = (type, modifier) => {
      const key = type === 'left' ? 'data' : 'rightData'
      chooseDataType.value = type
      if (!myExpression.value[key]) myExpression.value[key] = {}

      if (modifier === 'data') {
        checkUuid.value = myExpression.value?.[key]?.id || ''
        chooseDataVisible.value = true
        myExpression.value[key] = { literal: undefined }
      } else if (modifier === 'remove') {
        myExpression.value[key]!.modifier = ''
      } else if (modifier === 'literal') {
        myExpression.value[key] = { literal: '' }
      } else {
        myExpression.value[key]!.modifier = modifier
      }
    }
    const chooseData = ({ scope, path, data, rootDataId }) => {
      checkUuid.value = data.uuid
      const key = chooseDataType.value === 'left' ? 'data' : 'rightData'
      // { scope, data_uuid: data.uuid, path, rootDataId }
      if (chooseDataType.value === 'right' && !myExpression.value.operator) myExpression.value.operator = '=='
      myExpression.value[key] = {
        fromUuid: rootDataId,
        scope: scope,
        id: data.uuid,
        type: data.type,
        path: path,
        name: data.name
      }
    }
    const openCodeEditor = (type: any = null) => {
      if (!type) {
        code.value = myExpression.value.literal as string
        return
      }
      chooseDataType.value = type
      const key = chooseDataType.value === 'left' ? 'data' : 'rightData'
      code.value = myExpression.value?.[key]?.literal as string
      codeDlgVisible.value = true
    }
    const updateCode = (code) => {
      if (chooseDataType.value) {
        myExpression.value.literal = code
      }
      const key = chooseDataType.value === 'left' ? 'data' : 'rightData'
      myExpression.value[key] = { literal: code }
    }
    return {
      chooseButtons,
      myExpression,
      t,
      chooseDataType,
      chooseDataVisible,
      selectedPageId,
      checkUuid,
      modifierTypes,
      dataTypes,
      hasSetting,
      rightModifierTypes,
      codeDlgVisible,
      code,
      canAddData,
      canRemove,
      canAddExpression,
      hovered,
      clear,
      removeSubExpression,
      remove,
      updateCode,
      changeData,
      openCodeEditor,
      changeModifier,
      changeToThird,
      addSubExpression,
      addData,
      addLiteral,
      chooseData
    }
  }
}
</script>
