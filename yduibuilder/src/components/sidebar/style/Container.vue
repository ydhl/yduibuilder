<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.container') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.container.subset') }}</label>
      <div class="col-sm-9">
        <div class="list-group">
          <template v-if="subsetNames && subsetNames.length>0">
            <div class="list-group-item p-1 d-flex justify-content-between align-items-center" v-for="(item, index) in subsetNames" :key="index">
              <label class="flex-grow-1 ms-1 m-0 text-truncate d-flex align-items-center">
                <input type="radio" :checked="subsetActive == item" @click="updateActiveSubset(item)" class="me-1" :name="selectedUIItemId+'subSetName'">
                {{item}}</label>
              <div class="d-flex align-items-center">
                <button type="button" @click="openSetting(index)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-edit"></i></button>
                <ConfirmRemove @remove="remove(index)" icon="icon-remove"></ConfirmRemove>
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
  </div>

  <lay-layer v-model="isOpenSetting" :title="t('style.container.subsetName')" :shade="true" :area="['300px', '200px']">
    <div class="p-3">
      <input type="text" :placeholder="t('style.container.subsetName')" v-model.trim="newItem" class="form-control form-control-sm">
      <div class="mt-3">
        <button type="button" class="btn btn-primary btn-block" @click="updateValue">{{t('common.ok')}}</button>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import UIInit from '@/components/Common'
import { nextTick, onMounted, ref } from 'vue'
import ConfirmRemove from '@/components/common/ConfirmRemove.vue'
import ydhl from '@/lib/ydhl'
import { useStore } from 'vuex'
export default {
  name: 'StyleContainer',
  components: {
    ConfirmRemove
  },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const initInfo = UIInit()
    const editValueIndex = ref(-1)
    const store = useStore()
    const newItem = ref('')
    const subsetNames = ref<Array<string>>([])
    const isOpenSetting = ref(false)
    const rightBackdropVisible = ref(false)
    const subsetActive = initInfo.computedWrap('subsetActive', 'custom', '')
    const subset = initInfo.computedWrap('subset', 'custom', {})

    const updateSubset = () => {
      const value = {}
      for (const index in subsetNames.value) {
        const subsetName = subsetNames.value[index]
        let sub = subset.value[subsetName]
        if (!sub) sub = Object.values(subset.value)?.[index] || [] // 改名了
        value[subsetName] = sub
      }
      subset.value = value
    }
    const remove = (index) => {
      subsetNames.value.splice(index, 1)
      updateSubset()
    }

    const updateActiveSubset = (newActive) => {
      //  交互容器的items和subset，把当前状态的保持更新进到subset；把要切换的目标状态的从subset中取出来
      subset.value[subsetActive.value] = initInfo.selectedUIItem.value?.items || []
      store.commit('updateUIInfo', {
        itemid: initInfo.selectedUIItemId.value,
        pageId: initInfo.selectedPageId.value,
        props: {
          items: subset.value[newActive] || []
        }
      })
      subsetActive.value = newActive
    }

    const openSetting = (editItemIndex) => {
      editValueIndex.value = editItemIndex
      if (editItemIndex > -1) {
        newItem.value = subsetNames.value[editItemIndex]
      } else {
        newItem.value = ''
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
      if (subsetNames.value.filter((item) => newItem.value === item).length > 0) {
        ydhl.alert(t('style.container.subsetNameExist'))
        return
      }
      if (editValueIndex.value > -1) { // 修改
        subsetNames.value[editValueIndex.value] = newItem.value
      } else { // 新增
        if (subsetNames.value.length === 0) subsetActive.value = newItem.value
        subsetNames.value.push(newItem.value)
      }
      updateSubset()
      closeSetting()
    }
    onMounted(() => {
      subsetNames.value = Object.keys(subset.value) || []
    })

    return {
      t,
      rightBackdropVisible,
      isOpenSetting,
      editValueIndex,
      openSetting,
      closeSetting,
      updateValue,
      subsetNames,
      newItem,
      remove,
      updateActiveSubset,
      subsetActive,
      ...initInfo
    }
  }
}
</script>
