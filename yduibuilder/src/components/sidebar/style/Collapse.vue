<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.collapse') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.collapse.item') }}</label>
      <div class="col-sm-9 d-flex align-items-center justify-content-end">
        <button type="button" class="btn btn-outline-primary btn-sm" style="padding: 0px 4px" @click="addItem">{{t('style.collapse.addItem')}}</button>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <div class="list-group list-group-flush">
          <div class="list-group-item d-flex align-items-center p-0" @click="activeItem = index" v-for="(item, index) of selectedUIItem.items" :key="index">
            <i class="iconfont icon-radio text-primary" v-if="activeItem==index"></i>
            <i class="iconfont icon-radio text-muted" v-if="activeItem!=index"></i>
            <img :src="`${imgSite}image?pageid=${item.subPageId}&time=${time}`||'/logo.jpg'" style="width: 60px;height: 30px;object-fit: contain">
            <div class="flex-grow-1"></div>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-sm btn-outline-primary" @click="editItem(index)" style="padding: 0px 2px" title="Edit"><i class="iconfont icon-edit"></i></button>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="copyItem(index)" style="padding: 0px 2px" title="Copy"><i class="iconfont icon-copy"></i></button>
              <button type="button" class="btn btn-sm btn-outline-danger" @click="removeItem(index)" style="padding: 0px 2px" title="Delete"><i class="iconfont icon-remove"></i></button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import ydhl from '@/lib/ydhl'
declare const YDJS: any

export default {
  name: 'StyleBadge',
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const activeItem = info.computedWrap('activeItem', 'custom', 0)
    const addItem = () => {
      // 创建新对话框页面
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value
      })
    }
    const removeItem = (index: number) => {
      YDJS.confirm(t('common.areYouSure'), '', (dialogId) => {
        YDJS.hide_dialog(dialogId)
        store.commit('deleteSubpage', { itemid: info.selectedUIItemId.value, index })
      })
    }
    const copyItem = (index: number) => {
      const selectedItem = info.selectedUIItem.value
      const item = selectedItem.items?.[index]
      if (!item) return
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value,
        copyFromPageId: item.subPageId
      })
    }
    const editItem = (index: number) => {
      const selectedItem = info.selectedUIItem.value
      const item = selectedItem.items?.[index]
      if (!item) return
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value,
        newPageId: item.subPageId
      })
    }
    return {
      ...info,
      activeItem,
      t,
      imgSite: ydhl.api,
      addItem,
      removeItem,
      editItem,
      copyItem,
      time: Date.now()
    }
  }
}
</script>
