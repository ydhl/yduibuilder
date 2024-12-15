<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.container') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.container.subset') }}</label>
      <div class="col-sm-9">
        <div class="list-group">
          <template v-if="subset">
            <div class="list-group-item p-1 d-flex justify-content-between align-items-center" v-for="(item, subsetName) in subset" :key="subsetName">
              <label class="flex-grow-1 ms-1 m-0 text-truncate d-flex align-items-center">
                <input type="radio" :checked="subsetActive == subsetName" @click="updateActiveSubset(subsetName)" class="me-1" :name="selectedUIItemId+'subSetName'">
                {{subsetName}}</label>
              <div class="d-flex align-items-center">
                <button type="button" @click="openSetting(subsetName)" class="btn border-0 btn-outline-light btn-sm p-0 ps-1 pe-1 text-muted"><i class="iconfont icon-edit"></i></button>
                <ConfirmRemove @remove="remove(subsetName)" icon="icon-remove"></ConfirmRemove>
              </div>
            </div>
          </template>
        </div>
        <button type="button" @click="openSetting('')" class="btn btn-outline-primary btn-block btn-sm mt-1 mb-2">
          {{t('style.form.addValue')}}
        </button>
        <slot></slot>
      </div>
    </div>
  </div>

  <lay-layer v-model="isOpenSetting" :title="t('style.container.subsetName')" :shade="true" :area="['300px', '200px']">
    <div class="p-3">
      <input type="text" :placeholder="t('style.container.subsetName')" v-model.trim="newSubsetName" class="form-control form-control-sm">
      <div class="mt-3">
        <button type="button" class="btn btn-primary btn-block" @click="updateSubsetName">{{t('common.ok')}}</button>
      </div>
    </div>
  </lay-layer>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import UIInit from '@/components/Common'
import { nextTick, ref } from 'vue'
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
    const store = useStore()
    const newSubsetName = ref('')
    const isOpenSetting = ref(false)
    const rightBackdropVisible = ref(false)
    let oldSubsetName = ''
    const subsetActive = initInfo.computedWrap('subsetActive', 'custom', '')
    const subset = initInfo.computedWrap('subset', 'custom', {})

    const remove = (subsetName) => {
      const old = JSON.parse(JSON.stringify(subset.value))
      delete old[subsetName]
      subset.value = old
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
    const openSetting = (subsetName: string) => {
      oldSubsetName = subsetName
      newSubsetName.value = subsetName || ''
      isOpenSetting.value = true
      nextTick(() => {
        rightBackdropVisible.value = true
      })
    }
    const closeSetting = () => {
      isOpenSetting.value = false
      oldSubsetName = ''
      rightBackdropVisible.value = false
    }
    const updateSubsetName = () => {
      const subsetNames = Object.keys(subset.value)
      if (subsetNames.filter((item) => newSubsetName.value === item).length > 0) {
        ydhl.alert(t('style.container.subsetNameExist'))
        return
      }
      if (oldSubsetName) { // 修改
        const old = JSON.parse(JSON.stringify(subset.value))
        delete old[oldSubsetName]
        old[newSubsetName.value] = subset.value[oldSubsetName]
        subset.value = old
        if (subsetActive.value === oldSubsetName) subsetActive.value = newSubsetName.value
      } else { // 新增
        if (ydhl.isEmptyObject(subset.value)) subsetActive.value = newSubsetName.value
        const old = JSON.parse(JSON.stringify(subset.value)) || {}
        old[newSubsetName.value] = []
        subset.value = old
      }
      closeSetting()
    }

    return {
      t,
      rightBackdropVisible,
      isOpenSetting,
      openSetting,
      closeSetting,
      updateSubsetName,
      subset,
      newSubsetName,
      remove,
      updateActiveSubset,
      subsetActive,
      ...initInfo
    }
  }
}
</script>
