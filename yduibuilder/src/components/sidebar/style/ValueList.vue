<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.values') }}</label>
    <div class="col-sm-9">
      <div class="list-group">
        <template v-if="valueItems && valueItems.length>0">
          <div class="list-group-item p-1 d-flex justify-content-between align-items-center" v-for="(item, index) in valueItems" :key="index">
            <div><i class="iconfont icon-drag"></i></div>
            <label class="flex-grow-1 m-0 text-truncate">
              <input type="radio" v-if="!isMultiple" :checked="item.checked" @click="updateChecked(index)" class="me-1" :name="selectedUIItemId+'defaultValue'">
              <input type="checkbox" v-if="isMultiple" :checked="item.checked" @click="updateChecked(index)" class="me-1" :name="selectedUIItemId+'defaultValue'">
              {{item.text}} ({{item.value}})</label>
            <div>
              <button type="button" @click="edit(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-edit"></i></button>
              <button type="button" @click="remove(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-remove"></i></button>
            </div>
          </div>
        </template>
      </div>
      <button type="button" @click="openSetting(-1)" class="btn btn-outline-primary btn-block btn-sm mt-1 mb-2">
        {{t('style.form.addValue')}}
      </button>
      <slot></slot>
    </div>
  </div>

  <div v-if="isOpenSetting" style="z-index: 1041;position: absolute;top: 30%;left:0px;right: 0px">
    <div class="card m-3 shadow-lg">
      <div class="card-header d-flex justify-content-between">
        <div></div>
        <button type="button" class="btn btn-light btn-sm" @click="closeSetting" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="card-body">
        <div class="form-group row mt-2">
          <label for="form-text" class="col-sm-3 col-form-label">{{ t('style.form.text') }}</label>
          <div class="col-sm-9">
            <input type="text" class="form-control form-control-sm" id="form-text" v-model="newItem.text">
          </div>
        </div>
        <div class="form-group row mt-2">
          <label for="form-value" class="col-sm-3 col-form-label">{{ t('style.form.value') }}</label>
          <div class="col-sm-9">
            <input type="text" :class="{'form-control form-control-sm': true, 'is-invalid':valueInvalid}" :placeholder="t('style.form.valueTip')" id="form-value" v-model="newItem.value">
          </div>
        </div>
        <div class="row">
          <div class="col-sm-9 offset-3">
            <div class="form-check form-check-inline">
              <input type="checkbox" class="form-check-input" id="form-default" :checked="newItem.checked" v-model="newItem.checked">
              <label for="form-default" class=" form-check-label text-truncate">{{ t('style.form.default') }}</label>
            </div>
          </div>
        </div>
        <div class="row mt-3">
          <div class="col-sm-9 offset-3">
            <button type="button" class="btn btn-primary btn-block" @click="updateValue">{{t('common.ok')}}</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="right-backdrop" v-if="rightBackdropVisible"></div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, nextTick, ref, reactive, watch, toRaw } from 'vue'
import UIInit from '@/components/Common'

export default {
  name: 'StyleValueList',
  props: {
    valueIsRequired: Boolean
  },
  setup (props: any, context: any) {
    const initInfo = UIInit()
    const { t } = useI18n()
    const editValueIndex = ref(-1)
    const valueInvalid = ref(false)
    const editValue = computed(() => {
      if (editValueIndex.value <= -1) return { text: '', value: '', checked: false, disabled: false }
      return valueItems.value[editValueIndex.value]
    })
    const newItem = reactive({ text: '', value: '', checked: false, disabled: false })
    watch(editValue, (newValue) => {
      newItem.text = newValue ? newValue.text : ''
      newItem.value = newValue ? newValue.value : ''
      newItem.checked = newValue ? newValue.checked : false
      newItem.disabled = newValue ? newValue.disabled : false
    })

    const valueItems = computed(() => {
      return initInfo.getMeta('values') || []
    })

    const remove = (index) => {
      const values = JSON.parse(JSON.stringify(valueItems.value))
      values.splice(index, 1)
      initInfo.setMeta('values', values)
    }

    const updateChecked = (index) => {
      const values = JSON.parse(JSON.stringify(valueItems.value))
      if (isMultiple.value) {
        values[index].checked = !values[index].checked
      } else {
        // 单选的话把其他的反过来
        for (const valueIndex in values) {
          values[valueIndex].checked = false
        }
        values[index].checked = true
      }
      initInfo.setMeta('values', values)
    }

    const edit = (index) => {
      openSetting(index)
    }

    const isOpenSetting = ref(false)
    const rightBackdropVisible = ref(false)
    const openSetting = (editItemIndex) => {
      editValueIndex.value = editItemIndex
      valueInvalid.value = false
      newItem.value = ''
      newItem.text = ''
      newItem.checked = false
      newItem.disabled = false
      isOpenSetting.value = true
      nextTick(() => {
        rightBackdropVisible.value = true
      })
    }
    const closeSetting = () => {
      isOpenSetting.value = false
      rightBackdropVisible.value = false
      editValueIndex.value = -1
    }
    const isMultiple = computed(() => {
      return initInfo.getMeta('multiple', 'custom') || initInfo.selectedUIItem.value.type.toLowerCase() === 'checkbox' || false
    })
    const updateValue = () => {
      const rawItem = JSON.parse(JSON.stringify(toRaw(newItem)))
      if (!rawItem.value && props.valueIsRequired) {
        valueInvalid.value = true
        return
      }
      if (editValueIndex.value > -1) {
        const values = JSON.parse(JSON.stringify(valueItems.value))
        values[editValueIndex.value] = rawItem
        initInfo.setMeta('values', values)
      } else {
        initInfo.setMeta('values', [rawItem], '', true)
      }

      closeSetting()
    }

    return {
      t,
      rightBackdropVisible,
      isOpenSetting,
      editValueIndex,
      openSetting,
      closeSetting,
      isMultiple,
      updateValue,
      valueItems,
      editValue,
      newItem,
      edit,
      remove,
      updateChecked,
      valueInvalid,
      ...initInfo
    }
  }
}
</script>
