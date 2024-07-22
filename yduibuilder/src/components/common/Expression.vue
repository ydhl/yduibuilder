<template>
  <div :class="['gap-1 expression', 'expression-' + deep, hovered ? 'expression-hover' : '']" @mouseover.stop.prevent="hovered = true" @mouseout.stop.prevent="hovered = false">
    <div class="d-flex align-items-center" v-if="myExpression.subexpression">
      <DataModifier v-if="myExpression.modifier" :modifier="myExpression.modifier"></DataModifier>
      <div class="expression-group gap-1">
        <template v-for="(subexpression, index) in myExpression.subexpression" :key="index">
          <Expression :index="index" :variables="variables" :deep="deep+1" :expression="subexpression" @remove="removeSubExpression"></Expression>
        </template>
      </div>
    </div>
    <div v-else-if="myExpression.type=='ternary'" class="d-flex align-items-center">
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.expression"></Expression>
      <span>&nbsp;?&nbsp;</span>
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.trueExpression"></Expression>
      <span>&nbsp;:&nbsp;</span>
      <Expression :deep="deep+1" :variables="variables" :expression="myExpression.falseExpression"></Expression>
    </div>
    <template v-else-if="myExpression.type=='operator'">
      <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :flex-mode="true" :default-text="myExpression.operator" :options="binaryOperators" @change="(option) => myExpression.operator = option.value"></AdvanceSelect>
    </template>
    <template v-else-if="myExpression.type=='literal'">
      <span class="expression-literal pointer" @click="openCodeEditor">
        {{myExpression.literal || t('action.notSet')}}
      </span>
    </template>
    <template v-else-if="myExpression.type=='connect'">
      <span class="expression-data pointer" @click="changeData('left', myExpression.data?.id)">{{ myExpression.data?.path }}</span>
    </template>
    <template v-else>
      <Expression :deep="deep+1" :variables="variables" v-if="myExpression.expression" :expression="myExpression.expression"></Expression>
      <template v-else-if="myExpression.data?.id">
        <span class="expression-data pointer d-flex align-items-center">
          <div @click="changeData('left', myExpression.data?.id)" >
            <DataModifier :data-name="myExpression.data?.path" :modifier="myExpression.data?.modifier"></DataModifier>
          </div>
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="dataModifiers" v-if="dataModifiers.length>0" @change="(option) => changeModifier('left', option.value)">
            <template #input>
              <CustomModifier :data-name="myExpression.data?.path" @update="(modifier) => changeModifier('left', modifier)"></CustomModifier>
            </template>
          </AdvanceSelect>
        </span>
      </template>
      <template v-else-if="myExpression.data?.literal != undefined">
        <span class="expression-literal pointer d-flex align-items-center" @click="openCodeEditor('left')" >
        {{myExpression.data?.literal || t('action.notSet')}}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="literalModifiers" @change="(option) => changeModifier('left', option.value)"></AdvanceSelect>
        </span>
      </template>
      <div class="dropdown d-flex" v-if="myExpression.operator">
        <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :flex-mode="true" :default-text="myExpression.operator" :options="binaryOperators" @change="(option) => myExpression.operator = option.value"></AdvanceSelect>
      </div>
      <Expression :deep="deep+1" :variables="variables" v-if="myExpression.rightExpression" :expression="myExpression.rightExpression"></Expression>
      <template v-else-if="myExpression.rightData?.id">
        <span class="expression-data pointer d-flex align-items-center">
          <div @click="changeData('right', myExpression.rightData?.id)">
            <DataModifier :data-name="myExpression.rightData?.path" :modifier="myExpression.rightData?.modifier"></DataModifier>
          </div>
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="rightDataModifiers"
                         v-if="rightDataModifiers.length>0" @change="(option) => changeModifier('right', option.value)">
            <template #input>
              <CustomModifier :data-name="myExpression.rightData?.path" @update="(modifier) => changeModifier('right', modifier)"></CustomModifier>
            </template>
          </AdvanceSelect>
        </span>
      </template>
      <template v-else-if="myExpression.rightData?.literal != undefined">
        <span class="expression-literal pointer d-flex align-items-center" @click="openCodeEditor('right')">
          {{myExpression.rightData?.literal || t('action.notSet')}}
          <AdvanceSelect btn-size="bg-transparent border-0 btn-xs" :options="literalModifiers" @change="(option) => changeModifier('right', option.value)"></AdvanceSelect>
        </span>
      </template>
    </template>
    <div class="dropdown d-flex" v-if="hasSetting">
      <button type="button" class="btn btn-light bg-transparent border-0 btn-xs" data-bs-toggle="dropdown"><i class="iconfont icon-more hover-primary fs-7"></i></button>
      <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
        <li v-if="myExpression.type != 'ternary'"><a href="javascript:void(0)" @click="changeToThird" class="dropdown-item">{{t('expression.changeToTernary')}}</a></li>
        <li v-if="canAddExpression"><a href="javascript:void(0)" @click="addSubExpression" class="dropdown-item">{{t('expression.addSubExpression')}}</a></li>
        <li v-if="canAddData"><a href="javascript:void(0)" @click="addData" class="dropdown-item">{{myExpression.data ? t('expression.addAnotherData') : t('expression.addData')}}</a></li>
        <li v-if="canAddData"><a href="javascript:void(0)" @click="addLiteral" class="dropdown-item">{{t('expression.addLiteral')}}</a></li>
        <li><hr class="dropdown-divider"></li>
        <template v-if="isExpressionGroup">
          <li><a href="javascript:void(0)" @click="changeModifier('group', '~~@')" class="dropdown-item">~~@</a></li>
          <li><a href="javascript:void(0)" @click="changeModifier('group', '!@')" class="dropdown-item">!@</a></li>

          <li><hr class="dropdown-divider"></li>
          <li>
            <div class="dropdown-item">
              <CustomModifier :data-name="myExpressionDesc" @update="(modifier) => changeModifier('group', modifier)"></CustomModifier>
            </div>
          </li>

          <li><hr class="dropdown-divider"></li>
        </template>
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
  <CodeEditor v-model="codeDlgVisible" :title="t('expression.literal')" language="javascript" :code="code" @update="updateCode"></CodeEditor>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, ref } from 'vue'
import DataCheckPanel from '@/components/common/DataCheckPanel.vue'
import { useStore } from 'vuex'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'
import { Expression as ExpressionModel } from '@/store/model'
import CodeEditor from '@/components/common/CodeEditor.vue'
import DataModifier from '@/components/common/DataModifier.vue'
import CustomModifier from '@/components/common/CustomModifier.vue'
import ydhl from '@/lib/ydhl'

// 表达式展示
export default {
  name: 'Expression',
  components: { CustomModifier, DataModifier, CodeEditor, AdvanceSelect, DataCheckPanel },
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
    const leftOrRightData = ref('left')
    const binaryOperators = computed(() => {
      const _: any = []
      _.push({ name: t('expression.operator'), disabled: true })
      _.push({ name: '&&', value: '&&', desc: t('expression.&&') })
      _.push({ name: '||', value: '||', desc: t('expression.||') })
      _.push({ name: '' })
      _.push({ name: '==', value: '==', desc: t('expression.==') })
      _.push({ name: '===', value: '==', desc: t('expression.===') })
      _.push({ name: '!=', value: '!=', desc: t('expression.!=') })
      _.push({ name: '!==', value: '!=', desc: t('expression.!==') })
      _.push({ name: '<', value: '<', desc: t('expression.<') })
      _.push({ name: '<=', value: '<=', desc: t('expression.<=') })
      _.push({ name: '>', value: '>', desc: t('expression.>') })
      _.push({ name: '>=', value: '>=', desc: t('expression.>=') })
      _.push({ name: '' })
      _.push({ name: '+', value: '+', desc: t('expression.+') })
      _.push({ name: '-', value: '-', desc: t('expression.-') })
      _.push({ name: '*', value: '*', desc: t('expression.*') })
      _.push({ name: '/', value: '/', desc: t('expression./') })
      _.push({ name: '%', value: '%', desc: t('expression.%') })
      _.push({ name: '**', value: '**', desc: t('expression.**') })
      _.push({ name: '' })
      _.push({ name: '&', value: '&', desc: t('expression.&') })
      _.push({ name: '|', value: '|', desc: t('expression.|') })
      _.push({ name: '^', value: '^', desc: t('expression.^') })
      _.push({ name: '<<', value: '<<', desc: t('expression.<<') })
      _.push({ name: '>>', value: '>>', desc: t('expression.>>') })
      return _
    })
    const code = ref('')
    const checkUuid = ref('')
    const getModifiers = (type, modifiers) => {
      if (['integer', 'number'].indexOf(type) !== -1) {
        modifiers.push({ name: '++@', value: '++@', desc: t('expression.++@') })
        modifiers.push({ name: '@++', value: '@++', desc: t('expression.@++') })
        modifiers.push({ name: '--@', value: '--@', desc: t('expression.--@') })
        modifiers.push({ name: '@--', value: '@--', desc: t('expression.@--') })
        modifiers.push({ name: '~@', value: '~@', desc: t('expression.~@') })
        modifiers.push({ name: '~~@', value: '~~@', desc: t('expression.~~@') })
      } else if (['string', 'array'].indexOf(type) !== -1) {
        modifiers.push({ name: 'Length', value: '@.length', desc: t('expression.strArrLength') })
        if (type === 'string') {
          modifiers.push({ name: 'toLowerCase', value: '@.toLowerCase()' })
          modifiers.push({ name: 'toUpperCase', value: '@.toUpperCase()' })
        }
      }
      modifiers.push({ name: '!@', value: '!@', desc: t('expression.!@') })
      modifiers.push({ name: '' })
      modifiers.push({ name: t('common.custom'), value: '', desc: t('expression.customModifier'), input: true })
    }
    const modifiers = (data: any) => {
      const _: any = []
      _.push({ name: data.path, disabled: true })
      _.push({ name: t('expression.changeToLiteral'), value: 'literal', desc: '' })
      _.push({ name: t('expression.changeToExpression'), value: 'expression_group', desc: '' })
      _.push({ name: '' })
      getModifiers(data.type, _)
      if (data?.modifier) {
        _.push({ name: '' })
        _.push({ name: t('common.remove'), value: 'remove', desc: '' })
      }
      return _
    }
    const dataModifiers = computed(() => {
      return modifiers(myExpression.value.data)
    })
    const rightDataModifiers = computed(() => {
      return modifiers(myExpression.value.rightData)
    })
    const literalModifiers = computed(() => {
      const _: any = []
      _.push({ name: t('expression.literal'), disabled: true })
      _.push({ name: '' })
      _.push({ name: t('expression.changeToData'), value: 'data', desc: '' })
      _.push({ name: t('expression.changeToExpression'), value: 'expression_group', desc: '' })
      return _
    })

    const isExpressionGroup = computed(() => {
      return myExpression.value.type === 'expression_group'
    })
    const hasSetting = computed(() => {
      return !myExpression.value.type || ['operator'].indexOf(myExpression.value.type) === -1
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
    const myExpressionDesc = computed(() => {
      return ydhl.getExpressionDesc(myExpression.value)
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
      leftOrRightData.value = type
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
        myExpression.value.type = 'expression'
      }
      const key = !myExpression.value.data ? 'data' : 'rightData'
      myExpression.value[key] = { literal: '' }
      if (key === 'rightData' && !myExpression.value.operator) myExpression.value.operator = '=='
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
      // 当前表达式没有右值数据，则创建右值表达式
      // 否则当前表达式不是分组的话，就变成分组, 同时增加子表达式，是分组的话就添加子表达式
      if (!myExpression.value.rightData && !myExpression.value.rightExpression) {
        myExpression.value.rightExpression = {}
        if (!myExpression.value.operator) {
          myExpression.value.operator = '=='
        }
        return
      }
      if (myExpression.value.type !== 'expression_group') {
        const subexpression = [JSON.parse(JSON.stringify(myExpression.value))]
        clearExpression(myExpression.value)
        myExpression.value.subexpression = subexpression
        myExpression.value.type = 'expression_group'
      }
      if (!myExpression.value.subexpression) myExpression.value.subexpression = []
      if (myExpression.value.subexpression?.length > 0) {
        myExpression.value.subexpression?.push({ type: 'operator', operator: '&&' })
      }
      myExpression.value.subexpression?.push({})
    }
    const changeModifier = (type, modifier) => {
      if (type === 'group') { // 分组的修饰符
        myExpression.value.modifier = modifier
        return
      }
      const key = type === 'left' ? 'data' : 'rightData'
      leftOrRightData.value = type
      if (!myExpression.value[key]) myExpression.value[key] = {}

      if (modifier === 'data') { // 转变为数据
        checkUuid.value = myExpression.value?.[key]?.id || ''
        chooseDataVisible.value = true
        myExpression.value[key] = { literal: undefined }
      } else if (modifier === 'remove') { // 移除修饰符
        myExpression.value[key]!.modifier = ''
      } else if (modifier === 'literal') { // 转变为字面量
        myExpression.value[key] = { literal: '' }
      } else if (modifier === 'expression_group') { // 把所选的操作数或者字面量转变分组表达式
        if (key === 'data') {
          const data = myExpression.value.data
          const expression = myExpression.value.expression
          delete myExpression.value.data
          myExpression.value.expression = { type: 'expression_group', subexpression: [{ type: 'expression', data, expression }] }
        } else {
          const rightData = myExpression.value.rightData
          const rightExpression = myExpression.value.rightExpression
          delete myExpression.value.rightData
          delete myExpression.value.rightExpression
          myExpression.value.rightExpression = { type: 'expression_group', subexpression: [{ type: 'expression', rightData, rightExpression }] }
        }
        // const oldExpression = JSON.parse(JSON.stringify(myExpression.value))
        // clearExpression(myExpression.value)
        // myExpression.value.subexpression = []
        // myExpression.value.type = 'expression_group'
        //
        // const leftExp: ExpressionModel = {}
        // if (key === 'data') {
        //   leftExp.type = 'expression_group'
        //   leftExp.subexpression = [{ type: 'expression', data: oldExpression.data }]
        // } else {
        //   leftExp.type = 'expression'
        //   leftExp.data = oldExpression.data
        // }
        // myExpression.value.subexpression.push(leftExp)
        // if (oldExpression.operator) {
        //   myExpression.value.subexpression.push({ type: 'operator', data: oldExpression.operator })
        // }
        // if (oldExpression.rightData || oldExpression.rightExpression) {
        //   const rightExp: ExpressionModel = oldExpression.rightData ? { type: 'expression', data: oldExpression.rightData } : oldExpression.rightExpression
        //   if (key === 'rightData') {
        //     myExpression.value.subexpression = [{ type: 'expression_group', subexpression: [rightExp] }]
        //   } else {
        //     myExpression.value.subexpression.push(rightExp)
        //   }
        // }
        console.log(JSON.stringify(myExpression.value))
      } else {
        myExpression.value[key]!.modifier = modifier
      }
    }
    const chooseData = ({ scope, path, data, rootDataId }) => {
      checkUuid.value = data.uuid
      const key = leftOrRightData.value === 'left' ? 'data' : 'rightData'
      // { scope, data_uuid: data.uuid, path, rootDataId }
      if (leftOrRightData.value === 'right' && !myExpression.value.operator) myExpression.value.operator = '=='
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
        leftOrRightData.value = ''
        return
      }
      leftOrRightData.value = type
      const key = leftOrRightData.value === 'left' ? 'data' : 'rightData'
      code.value = myExpression.value?.[key]?.literal as string
      codeDlgVisible.value = true
    }
    const checkCode = (code) => {
      // if (code.match(/;|\r|\n/)) {
      //   ydhl.alert(t('expression.literalInvalid'))
      //   return false
      // }
      return true
    }
    const updateCode = (code) => {
      // 检查代码，不能有分号和换行
      if (!checkCode(code)) return
      codeDlgVisible.value = false
      if (!leftOrRightData.value) {
        myExpression.value.literal = code
        return
      }
      const key = leftOrRightData.value === 'right' ? 'rightData' : 'data'
      myExpression.value[key] = { literal: code }
    }
    return {
      chooseButtons,
      myExpression,
      t,
      leftOrRightData,
      chooseDataVisible,
      selectedPageId,
      checkUuid,
      dataModifiers,
      literalModifiers,
      hasSetting,
      rightDataModifiers,
      codeDlgVisible,
      code,
      canAddData,
      canRemove,
      canAddExpression,
      hovered,
      binaryOperators,
      isExpressionGroup,
      myExpressionDesc,
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
