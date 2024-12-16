<template>
  <div class="p-2" style="width: calc(100% - 20px)">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.type') }}</label>
      <div class="col-sm-9">
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><span :class="'param-'+myModel.type">{{myModel.type}}</span></button>
          <ul :class="{'dropdown-menu': true, 'show': show}" aria-labelledby="dropdownMenuLink">
            <li v-for="(type, index) in types" :key="index"><a href="javascript:;" @click="changeType(type)" :class="'dropdown-item param-'+type">{{ type }}</a></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="row" v-if="!isArrayItem">
      <label class="col-sm-3 col-form-label text-end">{{ t('expression.name') }} <span class="text-danger">*</span></label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" v-model.trim="myModel.name">
      </div>
    </div>
    <template v-if="canAddExpression">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('expression.expression') }} <span class="text-danger">*</span></label>
      <div class="col-sm-9">
        <div @click="openCodeDialog" class="pointer bg-light">
          <template v-if="myModel.defaultValue">
            <template v-if="myModel.defaultValue.trim().match(/\n/)">
            <pre><span class="text-muted">{{myModel.name}} = () => {</span>
{{myModel.defaultValue}}
<span class="text-muted">}</span></pre>
            </template>
            <template v-else><pre><span class="text-muted">{{myModel.name}} = () =></span> {{myModel.defaultValue}}</pre></template>
          </template>
          <template v-else>{{t('action.notSet')}}</template>
        </div>
      </div>
    </div>
    </template>
    <div class="row mb-1">
      <div class="col-sm-9 offset-sm-3 d-flex gap-2 align-items-center">
        <label>
          <input type="checkbox" true-value="1" false-value="0" v-model="myModel.deprecated"> {{ t('api.model.deprecated') }}
        </label>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.title') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" maxlength="45" v-model.trim="myModel.title">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.comment') }}</label>
      <div class="col-sm-9">
        <textarea class="form-control form-control-sm" v-model.trim="myModel.comment"></textarea>
      </div>
    </div>
  </div>
  <CodeEditorDialog v-model="codeDialogVisible" :left-operator="leftOperator" language="javascript"
                    :title="`${myModel.name||''} ${myModel.title||''}`"
                    :code="code" @update="updateCode"></CodeEditorDialog>
</template>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import { computed, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import CodeEditorDialog from '@/components/common/CodeEditorDialog.vue'

const codeDialogVisible = ref(false)
const code = ref('')
const emit = defineEmits(['update:modelValue'])
const { modelValue, types, canAddExpression, isArrayItem } = defineProps({
  modelValue: Object,
  canAddExpression: Boolean, // 能不能添加表达式
  isArrayItem: Boolean, // 是不是数组项
  types: {
    type: Array,
    default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'map', 'null', 'any', 'blob', 'file']
  },
  isExpression: Boolean // 是否是表达式
})

const { t } = useI18n()
const myModel = ref(modelValue)
const show = ref(false)
const leftOperator = computed(() => {
  if (!myModel.value.name) return ''
  const multi = myModel.value.defaultValue?.trim().match(/\n/)
  return `${myModel.value.name} = () => ${multi ? '{@}' : '@'}`
})

watch(myModel, (v) => {
  emit('update:modelValue', v)
})
const openCodeDialog = () => {
  codeDialogVisible.value = true
  code.value = myModel.value.defaultValue
}
const updateCode = (newCode) => {
  codeDialogVisible.value = false
  myModel.value.defaultValue = newCode
}
const changeType = (type) => {
  show.value = false
  myModel.value.type = type
  delete myModel.value.validRegular
  delete myModel.value.validRule
  if (type === 'array' && !myModel.value.item) {
    myModel.value.item = { type: 'string', uuid: ydhl.uuid() }
    delete myModel.value.props
  } else if (type === 'object' && !myModel.value.props) {
    myModel.value.props = []
    delete myModel.value.item
  } else if (type === 'blob') {
    myModel.value.props = [
      {
        uuid: myModel.value.uuid + '|size',
        type: 'number',
        name: 'size',
        readonly: true
      },
      {
        uuid: myModel.value.uuid + '|type',
        type: 'string',
        name: 'type',
        readonly: true
      }
    ]
    delete myModel.value.item
  } else if (type === 'file') {
    myModel.value.props = [
      {
        uuid: myModel.value.uuid + '|size',
        type: 'number',
        name: 'size',
        readonly: true
      },
      {
        uuid: myModel.value.uuid + '|type',
        type: 'string',
        name: 'type',
        readonly: true
      },
      {
        uuid: myModel.value.uuid + '|name',
        type: 'string',
        name: 'name',
        readonly: true
      },
      {
        uuid: myModel.value.uuid + '|lastModified',
        type: 'string',
        name: 'lastModified',
        readonly: true
      }
    ]
    delete myModel.value.item
  } else if (type !== 'array' && type !== 'object') {
    delete myModel.value.item
    delete myModel.value.props
  }
}
</script>
