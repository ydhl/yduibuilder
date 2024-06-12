<template>
  <button v-if="!hideImport" class="btn btn-outline-secondary btn-sm" @click="importPopupVisible = true" type="button">{{t('api.request.param.import')}}</button>
  <table class="table align-middle table-borderless table-striped table-hover table-sm mb-0 mt-1  hide-last-table">
    <thead>
    <tr>
      <th>{{t('api.request.param.queryName')}}</th>
      <th>{{t('api.dataStruct.title')}}</th>
      <th>{{t('api.request.param.required')}}</th>
      <th>{{t('api.request.param.type')}}</th>
      <th>{{t('api.request.param.sample')}}</th>
      <th>
        <lay-tooltip :content="t('api.request.param.fixedDesc')">{{t('api.request.param.fixed')}}?</lay-tooltip>
      </th>
      <th>{{t('api.request.param.desc')}}</th>
      <th></th>
    </tr>
    </thead>
    <tbody class="hide-param-last">
    <tr v-for="(param,index) in params" :key="index">
      <td>
        <input v-if="!nameIsReadOnly" @keyup="addParams" type="text" v-model.trim="param.name" :placeholder="t('api.request.param.add')" class="form-control form-control-sm"/>
        <div class="form-control form-control-sm bg-light" v-else>{{param.name}}</div>
      </td>
      <td><input type="text" v-model.trim="param.title" class="form-control form-control-sm"/></td>
      <td>
        <lay-switch v-model="param.required"></lay-switch>
      </td>
      <td>
        <div class="dropdown">
          <button class="btn btn-white btn-sm d-flex w-100 align-items-center justify-content-between dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <div :class="'param-'+param.type">{{param.type}}</div>
          </button>
          <ul class="dropdown-menu">
            <li v-for="(type, typeIndex) in types" :key="typeIndex">
              <a :class="'dropdown-item param-'+type" href="javascript:void(0)" @click="changeParamType(index,type)">{{ type }}</a>
            </li>
          </ul>
        </div>
      </td>
      <td>
        <input type="text" v-if="param.type!='array' && param.type!='file'" v-model="param.sample" class="form-control form-control-sm"/>
        <table v-if="param.type=='array'">
          <tr v-for="(sample, sampleindex) in param.sample" :key="sampleindex">
            <td class="pe-3">{{sampleindex}}</td>
            <td><input type="text" @keyup="addSample(index)" v-model="param.sample[sampleindex]" class="form-control form-control-sm"/></td>
            <td>
              <button type="button" class="btn btn-xs btn-outline-secondary hide-array-last" @click="removeSample(index, sampleindex)">
                <i class="iconfont icon-delete"></i>
              </button>
            </td>
          </tr>
        </table>
      </td>
      <td>
        <lay-switch v-model="param.action" onswitch-value="ReadOnly" unswitch-value="ReadWrite"></lay-switch>
      </td>
      <td><input type="text" v-model="param.comment" class="form-control form-control-sm"/></td>
      <td><button type="button" class="btn btn-sm btn-light hide-param-last" @click="removeParam(index)"><i class="iconfont icon-remove"></i></button></td>
    </tr>
    </tbody>
  </table>
  <teleport to="body">
    <lay-layer v-model="importPopupVisible" :shade="true" :title="t('api.request.param.import')">
      <div class="card shadow-none border-0" style="width: 50vw">
        <div class="card-body">
          <textarea class="form-control" v-model="queryString" rows="10" placeholder="such as foo/bar?a=b&c=d or a=b&c=d"></textarea>
        </div>
        <div class="card-footer">
          <button type="button" class="btn btn-secondary me-3" @click="importPopupVisible=false">{{ t('common.close') }}</button>
          <button type="button" class="btn btn-primary" @click="importQueryString">{{t('api.request.param.import')}}</button>
        </div>
      </div>
    </lay-layer>
  </teleport>
</template>

<script lang="ts">
import { layer } from '@layui/layui-vue'
import { useI18n } from 'vue-i18n'
import { computed, onMounted, ref, watch } from 'vue'
import { DataStructArray, DataStructParam, DataType } from '@/store/model'
import ydhl from '@/lib/ydhl'

export default {
  name: 'Param',
  emits: ['update'],
  props: {
    param: Object,
    hideImport: Boolean,
    nameIsReadOnly: Boolean,
    hasFileType: Boolean,
    hideArray: Boolean,
    query: String
  },
  components: {},
  setup (props: any, context: any) {
    const importPopupVisible = ref(false)
    const queryString = ref('')
    const { t } = useI18n()
    const types = computed(() => {
      const _ = ['string', 'integer', 'number']
      if (props.hasFileType) {
        _.push('file')
      }
      if (!props.hideArray) {
        _.push('array')
      }
      return _
    })
    const params = computed<Array<DataStructParam>>({
      get () {
        return props.param
      },
      set (v) {
        emitUpdate(v)
      }
    })
    const emitUpdate = (v) => {
      context.emit('update', v)
    }
    const addParams = () => {
      const lastName = params.value[params.value.length - 1].name?.trim()
      if (!lastName) return
      params.value.push({ uuid: ydhl.uuid(), name: '', type: 'string' })
      emitUpdate(params.value)
    }
    const removeParam = (index: number) => {
      params.value.splice(index, 1)
      emitUpdate(params.value)
    }
    const changeParamType = (index: number, type: DataType) => {
      // console.log(index)
      params.value[index].type = type
      if (type === 'array') {
        params.value[index].sample = ['']
        const param = params.value[index] as DataStructArray
        param.item = { uuid: ydhl.uuid(), type: 'string' }
      } else {
        const param = params.value[index] as DataStructArray
        delete param.item
      }
    }
    const removeSample = (index: number, sampleindex) => {
      const sample: any = params.value[index].sample
      if (!sample) return
      sample.splice(sampleindex, 1)
    }
    const addSample = (index: number) => {
      const param = params.value[index]
      if (!param.sample) return
      const sampleLength = param.sample?.length - 1
      let lastName = ''
      const sample: any = param.sample[sampleLength]
      if (param.type === 'array') {
        lastName = sample.trim()
      } else {
        lastName = sample.name.trim()
      }

      if (!lastName) return
      if (param.type === 'array') {
        (param.sample as any).push('')
      } else {
        (param.sample as any).push({ name: '' })
      }
      // emitUpdate()
    }
    const importQueryString = () => {
      importPopupVisible.value = false
      if (!queryString.value) return
      const args: any = ydhl.parseQueryString(queryString.value)

      for (const arg of args) {
        const item = params.value.find((item) => item.name === arg.name)
        if (!item) {
          params.value.push({ uuid: ydhl.uuid(), name: arg.name, sample: arg.value, type: arg.type })
        } else {
          item.sample = arg.value
          item.type = arg.type
        }
      }
      // emitUpdate()
    }
    watch(() => props.query, (value, oldValue) => {
      if (oldValue !== value && value.match(/\?.+/)) {
        layer.msg(t('api.request.param.autoParseQueryTip'))
        queryString.value = value
        importQueryString()
      }
    })
    onMounted(() => {
      if (!params.value || params.value.length === 0) params.value = [{ uuid: ydhl.uuid(), name: '', type: 'string' }]
    })
    return {
      t,
      params,
      importPopupVisible,
      queryString,
      types,
      importQueryString,
      addParams,
      addSample,
      removeSample,
      changeParamType,
      removeParam
    }
  }
}
</script>
<style scoped>
tr:last-child .hide-param-last{
  display: none;
}
tr:last-child .hide-object-last{
  display: none;
}
tr:last-child .hide-array-last{
  display: none;
}
</style>
