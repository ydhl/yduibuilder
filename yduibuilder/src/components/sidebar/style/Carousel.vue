<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.carousel') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.carousel.effect') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="effect">
          <option value="slide" selected>{{ t('style.carousel.effectSlide') }}</option>
          <option value="crossfade">{{ t('style.carousel.effectCrossfade') }}</option>
        </select>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <label class=" form-check-label text-truncate"><input type="checkbox" v-model="showControl"> {{ t('style.carousel.showControl') }}</label>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <label class=" form-check-label text-truncate"><input type="checkbox" v-model="showIndicator"> {{ t('style.carousel.showIndicator') }}</label>
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.carousel.slide') }}</label>
      <div class="col-sm-9 d-flex align-items-center justify-content-end">
        <button type="button" class="btn btn-outline-primary btn-sm" style="padding: 0px 4px" @click="addSlide">{{t('style.carousel.addSlide')}}</button>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-sm-3">
        <div class="list-group list-group-flush">
          <div class="list-group-item d-flex align-items-center p-0" @click="setActiveSlide(index)" v-for="(item, index) of selectedUIItem.items" :key="index">
            <i class="iconfont icon-radio text-primary" v-if="selectedUIItem.meta.custom?.activeSlide==index"></i>
            <i class="iconfont icon-radio text-muted" v-if="selectedUIItem.meta.custom?.activeSlide!=index"></i>
            <img :src="`${imgSite}image?pageid=${item.subPageId}`||'/logo.jpg'" style="width: 60px;height: 30px;object-fit: contain">
            <div class="flex-grow-1"></div>
            <div class="btn-group btn-group-sm">
              <button type="button" class="btn btn-sm btn-outline-primary" @click="editSlide(index)" style="padding: 0px 2px" title="Edit"><i class="iconfont icon-edit"></i></button>
              <button type="button" class="btn btn-sm btn-outline-primary" @click="copySlide(index)" style="padding: 0px 2px" title="Copy"><i class="iconfont icon-copy"></i></button>
              <button type="button" class="btn btn-sm btn-outline-danger" @click="removeSlide(index)" style="padding: 0px 2px" title="Delete"><i class="iconfont icon-remove"></i></button>
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
    const showControl = info.computedWrap('showControl', 'custom', false)
    const showIndicator = info.computedWrap('showIndicator', 'custom', false)
    const effect = info.computedWrap('effect', 'custom', 'normal')
    const addSlide = () => {
      // 创建新对话框页面
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value,
        excludeUI: ['Carousel']
      })
    }
    const removeSlide = (index: number) => {
      YDJS.confirm(t('common.areYouSure'), '', (dialogId) => {
        YDJS.hide_dialog(dialogId)
        store.commit('deleteSubpage', { itemid: info.selectedUIItemId.value, index })
      })
    }
    const copySlide = (index: number) => {
      const selectedItem = info.selectedUIItem.value
      const item = selectedItem.items?.[index]
      if (!item) return
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value,
        copyFromPageId: item.subPageId
      })
    }
    const editSlide = (index: number) => {
      const selectedItem = info.selectedUIItem.value
      const item = selectedItem.items?.[index]
      if (!item) return
      store.commit('createSubpage', {
        itemid: info.selectedUIItemId.value,
        newPageId: item.subPageId
      })
    }
    const setActiveSlide = (index: number) => {
      store.commit('updateItemMeta', { type: 'custom', itemid: info.selectedUIItemId.value, props: { activeSlide: index } })
    }
    return {
      ...info,
      showControl,
      showIndicator,
      effect,
      t,
      imgSite: ydhl.api,
      addSlide,
      removeSlide,
      editSlide,
      copySlide,
      setActiveSlide
    }
  }
}
</script>
