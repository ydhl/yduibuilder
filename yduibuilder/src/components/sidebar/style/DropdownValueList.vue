<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.values') }}</label>
    <div class="col-sm-9">
      <template v-if="valueItems && valueItems.length>0">
        <div class="dropdown-menu d-block position-static float-none">
          <div :class="{'p-1 d-flex justify-content-between align-items-center': true,'dropdown-item':item.type=='action','dropdown-header':item.type=='header'}" v-for="(item, index) in valueItems" :key="index">
            <div><i class="iconfont icon-drag"></i></div>
            <label class="flex-grow-1 m-0 text-truncate">
              <template v-if="item.type=='divider'"><hr class="m-3"/></template>
              <template v-if="item.type=='action'">{{item.text}} ({{item.value}})</template>
              <template v-if="item.type=='header' || item.type=='text'">{{item.text}}</template>
            </label>
            <div>
              <button type="button" @click="edit(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-edit"></i></button>
              <button type="button" @click="remove(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-remove"></i></button>
            </div>
          </div>
        </div>
      </template>
      <button type="button" @click="openSetting(-1)" class="btn btn-outline-primary btn-block btn-sm mt-2 mb-2">
        {{t('style.form.addValue')}}
      </button>
      <slot></slot>
    </div>
  </div>

  <div v-if="isOpenSetting" style="z-index: 1041;position: absolute;top: 30%;left:0px;right: 0px">
    <div class="card m-3 shadow-lg">
      <div class="card-header d-flex justify-content-between">
        <button type="button" class="btn btn-light btn-sm" @click="closeSetting" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="card-body">
        <div class="form-group mb-1 row">
          <label class="col-sm-3 col-form-label">{{ t('style.dropdown.itemType') }}</label>
          <div class="col-sm-9">
            <select class="form-select form-select-sm" v-model="newItem.type">
              <option value="action">Action</option>
              <option value="header">Header</option>
              <option value="divider">Divider</option>
              <option value="text">Text</option>
            </select>
          </div>
        </div>
        <div class="form-group mb-1 row" v-if="newItem.type!='divider'">
          <label for="form-text" class="col-sm-3 col-form-label">{{ t('style.form.text') }}</label>
          <div class="col-sm-9">
            <input type="text" class="form-control form-control-sm" id="form-text" v-model="newItem.text">
          </div>
        </div>
        <div class="form-group row" v-if="newItem.type=='action'">
          <label for="form-value" class="col-sm-3 col-form-label">{{ t('style.href') }}</label>
          <div class="col-sm-9">
            <input type="text" :class="{'form-control form-control-sm': true}" id="form-value" v-model="newItem.value">
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
  name: 'DropdownValueList',
  setup (props: any, context: any) {
    const initInfo = UIInit()
    const { t } = useI18n()
    const editValueIndex = ref(-1)
    const editValue = computed(() => {
      if (editValueIndex.value <= -1) return { text: '', value: '', checked: false, disabled: false, type: 'action' }
      return valueItems.value[editValueIndex.value]
    })
    const newItem = reactive({ text: '', value: '', checked: false, disabled: false, type: 'action' })
    watch(editValue, (newValue) => {
      newItem.text = newValue ? newValue.text : ''
      newItem.value = newValue ? newValue.value : ''
      newItem.checked = newValue ? newValue.checked : false
      newItem.disabled = newValue ? newValue.disabled : false
      newItem.type = newValue ? newValue.type : 'action'
    })

    const valueItems = computed(() => {
      return initInfo.getMeta('values') || []
    })

    const remove = (index) => {
      const values = JSON.parse(JSON.stringify(valueItems.value))
      values.splice(index, 1)
      initInfo.setMeta('values', values)
    }

    const edit = (index) => {
      openSetting(index)
    }

    const isOpenSetting = ref(false)
    const rightBackdropVisible = ref(false)
    const openSetting = (editItemIndex) => {
      editValueIndex.value = editItemIndex
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

    const updateValue = () => {
      const rawItem = JSON.parse(JSON.stringify(toRaw(newItem)))

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
      updateValue,
      valueItems,
      editValue,
      newItem,
      edit,
      remove,
      ...initInfo
    }
  }
}
</script>
