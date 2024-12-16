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
    <template v-if="!isArrayItem">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('api.model.name') }} <span class="text-danger">*</span></label>
        <div class="col-sm-9">
          <input type="text" class="form-control form-control-sm" v-model.trim="myModel.name">
        </div>
      </div>
      <div class="row mb-1">
        <div class="col-sm-9 offset-sm-3 d-flex gap-2 align-items-center">
          <label>
            <input type="checkbox" true-value="1" false-value="0" v-model="myModel.deprecated"> {{ t('api.model.deprecated') }}
          </label>
        </div>
      </div>
      <div class="row mb-2">
        <label class="col-sm-3 col-form-label text-end">
          {{t("api.model.validate")}}
        </label>
        <div class="col-sm-9">
          <AdvanceSelect :options="validateRules"
                         @change="(option) => { myModel.validRule = option.value; myModel.validRegular='' }"
                         :default-text="validateRuleDesc">
            <template #input>
              <input type="text" placeholder="such as /\d+/" class="form-control form-control-sm" v-model.trim="myModel.validRegular"/>
            </template>
          </AdvanceSelect>
          <input type="text" class="form-control mt-1 form-control-sm" placeholder="error message" maxlength="145" v-model.trim="myModel.invalidMsg">
        </div>
      </div>
      <template v-if="isScale">
        <div class="row">
          <label class="col-sm-3 col-form-label text-end" for="enum" @click="changeValueType()">
            <input class="form-check-input" type="checkbox" role="switch" id="enum" :checked="valueType == 'enum'">
            {{t("api.model.isEnumValue")}}
          </label>
          <div class="col-sm-9">
            <table class="table table-sm table-borderless table-hover table-striped" v-if="valueType == 'enum'">
              <thead><tr><th>{{t("api.model.enumValue")}}</th><th>{{t("api.model.comment")}}</th></tr></thead>
              <tbody>
              <tr v-for="(enumItem, index) in enumValues" :key="index">
                <td><input type="text" @change="changeEnum()" class="form-control form-control-sm" v-model.trim="enumItem.name"></td>
                <td><input type="text" @change="changeEnum()" class="form-control form-control-sm" v-model.trim="enumItem.comment"></td>
              </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </template>

    <template v-if="myModel.type =='array'">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('api.model.initLength') }}</label>
        <div class="col-sm-9">
          <input type="number" maxlength="255" minlength="1" class="form-control form-control-sm" v-model.trim="myModel.initLength">
        </div>
      </div>
    </template>
    <div class="row" v-if="myModel.type=='string'">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.mock') }}</label>
      <div class="col-sm-9">
        <select class="form-select-sm form-select" v-model="myModel.mock">
          <option value="">Not Mock</option>
          <option v-for="(name, mock) in mocks" :value="mock" :key="mock">{{name}}</option>
        </select>
      </div>
    </div>
    <div class="row" v-else-if="myModel.type=='any'">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.mock') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" placeholder="mock template string" v-model.trim="myModel.mock">
      </div>
    </div>
    <div class="row" v-else-if="['blob', 'file'].indexOf(myModel.type) === -1">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.mock') }}</label>
      <div class="col-sm-9">
        <select class="form-select-sm form-select" v-model="myModel.mock">
          <option value="">Not Mock</option>
          <option value="1">Mock</option>
        </select>
      </div>
    </div>
    <div class="row" v-if="hasDefaultValue && ['blob', 'file'].indexOf(myModel.type) === -1">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.defaultValue') }}</label>
      <div class="col-sm-9">
        <input type="text" v-if="['object','array','map','any'].indexOf(myModel.type) == -1" class="form-control form-control-sm" v-model.trim="myModel.defaultValue">
        <button type="button" v-else @click="openCodeDialog" class="btn btn-xs btn-light">{{myModel.defaultValue?t('common.view'):t('action.notSet')}}</button>
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
  <CodeEditorDialog v-model="codeDialogVisible" :hide-variable="true"
                    :title="`${myModel.name||''} ${myModel.title||''}`"
                    :schema="modelSchema" :code="code" @update="updateCode"></CodeEditorDialog>
</template>

<script lang="ts" setup>
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import CodeEditorDialog from '@/components/common/CodeEditorDialog.vue'
import AdvanceSelect from '@/components/common/AdvanceSelect.vue'

const emit = defineEmits(['update:modelValue'])
const { modelValue, types, isArrayItem, hasDefaultValue } = defineProps({
  modelValue: Object,
  types: {
    type: Array,
    default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'map', 'null', 'any', 'blob', 'file']
  },
  isArrayItem: Boolean, // 数组结点标识, 数组结点则只能修改type和comment
  hasDefaultValue: {
    default: true,
    type: Boolean
  }
})

const { t } = useI18n()
const myModel = ref(modelValue)
const show = ref(false)
const codeDialogVisible = ref(false)
const valueType = ref('')
const code = ref('')
const mocks = {
  '@string': t('mock.string'),
  '@date': t('mock.date'),
  '@time': t('mock.time'),
  '@datetime': t('mock.datetime'),
  '@color': t('mock.color'),
  '@word': t('mock.word'),
  '@cname': t('mock.cname'),
  '@name': t('mock.name'),
  '@paragraph': t('mock.paragraph'),
  '@sentence': t('mock.sentence'),
  '@cparagraph': t('mock.cparagraph'),
  '@csentence': t('mock.csentence'),
  '@url(http)': t('mock.URL')
}
const isScale = computed(() => ['string', 'integer', 'number', 'any'].indexOf(myModel.value.type) !== -1)
const validateRules = computed(() => {
  const rules: any = [
    {
      header: t('common.general')
    },
    {
      name: t('api.model.valid.notEmpty'),
      value: 'notEmpty',
      desc: t('api.model.valid.notEmptyDesc')
    }
  ]
  if (isScale.value) {
    rules.push(
      {
        header: t('common.custom')
      },
      {
        name: t('common.custom'),
        value: '',
        input: true,
        desc: t('api.model.valid.regular')
      }
    )
  }
  return rules
})

const enumValues = computed(() => {
  const values: any = []
  for (const name in modelValue.enumValue) {
    values.push({ name, comment: modelValue.enumValue[name] })
  }
  if (values) {
    values.push({ name: '', comment: '' })
  }
  return values
})
const modelSchema = computed(() => {
  return ydhl.getModelJSONSchema(modelValue)
})
const validateRuleDesc = computed(() => {
  if (myModel.value.validRegular) return myModel.value.validRegular
  if (myModel.value.validRule) return t('api.model.valid.' + myModel.value.validRule)
  return t('api.model.validate')
})
onMounted(() => {
  if (!ydhl.isEmptyObject(modelValue.enumValue)) {
    valueType.value = 'enum'
  }
})
watch(myModel, (v) => {
  if (!v.name) {
    ydhl.alert(t('common.pleaseCheckRequired'))
    return
  }
  emit('update:modelValue', v)
})
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
const changeEnum = () => {
  const length = enumValues.value.length
  if (!length) return
  if (enumValues.value[length - 1].name !== '') {
    enumValues.value.push({ name: '', comment: '' })
  }
  const values = {}
  for (const value of enumValues.value) {
    if (!value.name) continue
    values[value.name] = value.comment
  }
  myModel.value.enumValue = values
  emit('update:modelValue', JSON.parse(JSON.stringify(myModel.value)))
}
const updateCode = (newCode) => {
  codeDialogVisible.value = false
  myModel.value.defaultValue = newCode
}
const openCodeDialog = () => {
  codeDialogVisible.value = true
  code.value = myModel.value.defaultValue
}
const changeValueType = () => {
  valueType.value = valueType.value === 'enum' ? '' : 'enum'
  if (valueType.value === 'enum') {
    myModel.value.enumValue = {}
  }
}
</script>
