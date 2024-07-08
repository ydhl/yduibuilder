<template>
  <div class="row">
    <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.values') }}</label>
    <div class="col-sm-9">
      <template v-if="valueItems && valueItems.length>0">
        <div class="dropdown-menu d-block position-static float-none">
          <draggable v-model="valueItems" handle=".icon-drag" @change="sortValueItems">
            <transition-group>
              <div :class="{'p-1 d-flex justify-content-between align-items-center': true,'dropdown-item':item.type=='action','dropdown-header':item.type=='header'}" v-for="(item, index) in valueItems" :key="index">
                <div><i class="iconfont icon-drag" style="cursor: move;"></i></div>
                <label class="flex-grow-1 m-0 text-truncate">
                  <template v-if="item.type=='divider'"><hr class="m-3"/></template>
                  <template v-if="item.type=='action'">
                    <input type="radio" :checked="item.checked" @click="updateChecked(index)" class="me-1" :name="selectedUIItemId+'defaultValue'">
                    {{item.name}} ({{item.value}})
                  </template>
                  <template v-if="item.type=='header' || item.type=='text'">{{item.name}}</template>
                </label>
                <div>
                  <button type="button" @click="openSetting(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-edit"></i></button>
                  <button type="button" @click="remove(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-remove"></i></button>
                </div>
              </div>
            </transition-group>
          </draggable>
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
            <input type="text" class="form-control form-control-sm" id="form-text" v-model="newItem.name">
          </div>
        </div>
        <template  v-if="newItem.type=='action'">
          <div class="form-group row">
            <label for="form-value" class="col-sm-3 col-form-label">{{ t('style.value') }}</label>
            <div class="col-sm-9">
              <input type="text" :class="{'form-control form-control-sm': true}" id="form-value" v-model="newItem.value">
            </div>
          </div>
          <div class="form-group row">
            <div class="col-sm-9 offset-3">
              <div class="form-check form-check-inline">
                <input type="checkbox" class="form-check-input" id="form-default" :checked="newItem.checked" v-model="newItem.checked">
                <label for="form-default" class=" form-check-label text-truncate">{{ t('style.form.default') }}</label>
              </div>
            </div>
          </div>
        </template>
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
import { nextTick, ref, onMounted } from 'vue'
import UIInit from '@/components/Common'
import { VueDraggableNext } from 'vue-draggable-next'
export default {
  name: 'DropdownValueList',
  components: {
    draggable: VueDraggableNext
  },
  setup (props: any, context: any) {
    const initInfo = UIInit()
    const { t } = useI18n()
    const editValueIndex = ref(-1)
    const newItem = ref({ name: '', value: '', checked: false, disabled: false, type: 'action' })
    const valueItems = ref<any>([])

    const remove = (index) => {
      valueItems.value.splice(index, 1)
      const values = JSON.parse(JSON.stringify(valueItems.value))
      initInfo.setMeta('values', values)
    }

    const isOpenSetting = ref(false)
    const rightBackdropVisible = ref(false)
    const openSetting = (editItemIndex) => {
      editValueIndex.value = editItemIndex
      if (editItemIndex > -1) {
        newItem.value = JSON.parse(JSON.stringify(valueItems.value[editItemIndex]))
      } else {
        newItem.value = { name: '', value: '', checked: false, disabled: false, type: 'action' }
      }

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
      const rawItem = JSON.parse(JSON.stringify(newItem.value))

      if (editValueIndex.value > -1) {
        valueItems.value[editValueIndex.value] = rawItem
        if (rawItem.checked) _updateChecked(editValueIndex.value)
        const values = JSON.parse(JSON.stringify(valueItems.value))
        initInfo.setMeta('values', values)
      } else {
        valueItems.value.push(rawItem)
        initInfo.setMeta('values', [rawItem], '', true)
      }

      closeSetting()
    }
    onMounted(() => {
      valueItems.value = initInfo.getMeta('values') || []
    })

    const sortValueItems = (n) => {
      const values = JSON.parse(JSON.stringify(valueItems.value))
      initInfo.setMeta('values', values)
    }

    const _updateChecked = (index) => {
      // 单选的话把其他的反过来
      for (const valueIndex in valueItems.value) {
        valueItems.value[valueIndex].checked = false
      }
      valueItems.value[index].checked = true
    }
    const updateChecked = (index) => {
      _updateChecked(index)
      initInfo.setMeta('values', JSON.parse(JSON.stringify(valueItems.value)))
    }
    return {
      t,
      rightBackdropVisible,
      isOpenSetting,
      editValueIndex,
      openSetting,
      closeSetting,
      updateValue,
      updateChecked,
      valueItems,
      newItem,
      remove,
      sortValueItems,
      ...initInfo
    }
  }
}
</script>
