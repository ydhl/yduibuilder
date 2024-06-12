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
    <SubpageList :page-id="selectedPageId" :ui-item="selectedUIItem" :exclude-ui="['Carousel']"></SubpageList>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import SubpageList from '@/components/sidebar/style/SubpageList.vue'

export default {
  name: 'StyleBadge',
  components: {
    SubpageList
  },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const showControl = info.computedWrap('showControl', 'custom', false)
    const showIndicator = info.computedWrap('showIndicator', 'custom', false)
    const effect = info.computedWrap('effect', 'custom', 'normal')

    return {
      ...info,
      showControl,
      showIndicator,
      effect,
      t
    }
  }
}
</script>
