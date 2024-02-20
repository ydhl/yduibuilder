<template>
  <div class="p-2 container">
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
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('api.model.defaultValue') }}</label>
        <div class="col-sm-9">
          <input type="text" class="form-control form-control-sm" v-model="myModel.defaultValue">
        </div>
      </div>
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('api.model.title') }}</label>
        <div class="col-sm-9">
          <input type="text" class="form-control form-control-sm" v-model="myModel.title">
        </div>
      </div>
    </template>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('api.model.comment') }}</label>
      <div class="col-sm-9">
        <textarea class="form-control form-control-sm" v-model="myModel.comment"></textarea>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { ref, watch } from 'vue'
import ydhl from '@/lib/ydhl'

export default {
  name: 'AddModelItem',
  props: {
    modelValue: Object,
    types: {
      type: Array,
      default: () => ['string', 'integer', 'number', 'boolean', 'object', 'array', 'null', 'any']
    },
    isArrayItem: Boolean // 数组结点标识, 数组结点则只能修改type和comment
  },
  emits: ['update:modelValue'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const myModel = ref(props.modelValue)
    const show = ref(false)
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
      }
    }
    return {
      t,
      myModel,
      show,
      changeType
    }
  }
}
</script>
