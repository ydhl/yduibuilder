<template>
  <div class="p-2" style="width: 500px">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.type') }}</label>
      <div class="col-sm-9">
        <div class="dropdown">
          <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><span :class="'param-'+myModel.type">{{myModel.type}}</span></button>
          <ul :class="{'dropdown-menu': true, 'show': show}" aria-labelledby="dropdownMenuLink">
            <li v-for="(type, index) in types" :key="index"><a href="#" @click="changeType(type)" :class="'dropdown-item param-'+type">{{ type }}</a></li>
          </ul>
        </div>
      </div>
    </div>
    <template v-if="!isArrayItem">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('api.model.name') }} <span class="text-danger">*</span></label>
        <div class="col-sm-9">
          <input type="text" class="form-control form-control-sm" v-model="myModel.name">
        </div>
      </div>
      <template v-if="isScale">
        <div class="row">
          <div class="col-sm-9 offset-sm-3">
            <div class="d-flex align-items-center">
              <div class="form-check form-switch me-5" @click="changeValueType()">
                <input class="form-check-input" type="checkbox" role="switch" id="enum" :checked="valueType == 'enum'">
                <label class="form-check-label" for="enum"><label>{{t("api.model.isEnumValue")}}</label></label>
              </div>
            </div>
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
          <input type="number" maxlength="255" minlength="1" class="form-control form-control-sm" v-model="myModel.initLength">
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
        <input type="text" class="form-control form-control-sm" placeholder="mock template string" v-model="myModel.mock">
      </div>
    </div>
    <div class="row" v-else>
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.mock') }}</label>
      <div class="col-sm-9">
        <select class="form-select-sm form-select" v-model="myModel.mock">
          <option value="">Not Mock</option>
          <option value="1">Mock</option>
        </select>
      </div>
    </div>
    <div class="row" v-if="hasDefaultValue">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.defaultValue') }}</label>
      <div class="col-sm-9">
        <input type="text" v-if="['object','array','map','any'].indexOf(myModel.type) == -1" class="form-control form-control-sm" v-model="myModel.defaultValue">
        <button type="button" v-if="['object','array','map','any'].indexOf(myModel.type) !== -1" @click="openCodeDialog" class="btn btn-xs btn-light">{{myModel.defaultValue?t('common.view'):t('action.notSet')}}</button>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.title') }}</label>
      <div class="col-sm-9">
        <input type="text" class="form-control form-control-sm" maxlength="45" v-model="myModel.title">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.comment') }}</label>
      <div class="col-sm-9">
        <textarea class="form-control form-control-sm" v-model="myModel.comment"></textarea>
      </div>
    </div>
  </div>
  <CodeEditor v-model="codeDialogVisible" :code="code" @update="updateCode"></CodeEditor>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'
import CodeEditor from '@/components/common/CodeEditor.vue'

export default {
  name: 'AddData',
  components: { CodeEditor },
  props: {
    modelValue: Object,
    types: {
      type: Array,
      default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'map', 'null', 'any']
    },
    isArrayItem: Boolean, // 数组结点标识, 数组结点则只能修改type和comment
    hasDefaultValue: {
      default: true,
      type: Boolean
    }
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myModel = ref(props.modelValue)
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

    const enumValues = computed(() => {
      const values: any = []
      for (const name in props.modelValue.enumValue) {
        values.push({ name, comment: props.modelValue.enumValue[name] })
      }
      if (values) {
        values.push({ name: '', comment: '' })
      }
      return values
    })
    onMounted(() => {
      if (!ydhl.isEmptyObject(props.modelValue.enumValue)) {
        valueType.value = 'enum'
      }
    })
    watch(myModel, (v) => {
      context.emit('update:modelValue', v)
    })
    const changeType = (type) => {
      show.value = false
      myModel.value.type = type
      if (type === 'array' && !myModel.value.item) {
        myModel.value.item = { type: 'string', uuid: ydhl.uuid() }
      } else if (type === 'object' && !myModel.value.props) {
        myModel.value.props = []
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
      context.emit('update:modelValue', JSON.parse(JSON.stringify(myModel.value)))
    }
    const updateCode = (newCode) => {
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
    return {
      t,
      myModel,
      valueType,
      isScale,
      show,
      code,
      updateCode,
      codeDialogVisible,
      changeEnum,
      changeValueType,
      enumValues,
      mocks,
      changeType,
      openCodeDialog
    }
  }
}
</script>
