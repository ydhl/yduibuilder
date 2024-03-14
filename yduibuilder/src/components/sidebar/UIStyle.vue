<template>
  <div v-if="!selectedUIItemId" class="d-flex flex-column h-100 text-muted justify-content-center align-content-center align-items-center">
    <i class="iconfont icon-click" style="font-size: 6rem"></i>
    <div class="pe-2">{{t('common.pleaseSelectUIItem')}}</div>
  </div>
  <div v-if="selectedUIItemId" class="style-panel">
    <p class="pt-2 d-flex align-items-center"><i :class="`iconfont text-primary icon-${selectedUIItem.type.toLowerCase()}`"></i>&nbsp;{{selectedUIItem.meta.title || selectedUIItem.type}}&nbsp;<small class="text-muted">#{{uiID}}</small></p>
    <div class="fs-7 d-flex align-items-center mt-1">
      <i class="iconfont icon-point text-danger"></i>{{t('style.valueCustom')}}&nbsp;&nbsp;
      <i class="iconfont icon-point text-success"></i>{{t('style.valueInherit')}}
    </div>
    <StyleSelector></StyleSelector>
    <Typography></Typography>
    <StyleBackground v-if="hasBackground"></StyleBackground>
    <StyleLyout v-if="hasLayout"></StyleLyout>
    <StyleSize></StyleSize>
    <MarginPadding></MarginPadding>
    <StyleBorder v-if="hasBorder"></StyleBorder>
    <StyleUtilities v-if="isNotPage"></StyleUtilities>
<!--    <StyleFont></StyleFont>-->
  </div>
</template>

<script lang="ts">
import Typography from '@/components/sidebar/style/Typography.vue'
import initUI from '@/components/Common'
import StyleLyout from '@/components/sidebar/style/Layout.vue'
import StyleBackground from '@/components/sidebar/style/Background.vue'
import StyleSelector from '@/components/sidebar/style/StyleSelector.vue'
import { ref, computed } from 'vue'
import MarginPadding from '@/components/sidebar/style/MarginPadding.vue'
import StyleBorder from '@/components/sidebar/style/Border.vue'
import { useI18n } from 'vue-i18n'
import StyleUtilities from '@/components/sidebar/style/Utilities.vue'
import StyleSize from '@/components/sidebar/style/Size.vue'
export default {
  name: 'UIStyle',
  components: { StyleSize, StyleLyout, StyleSelector, StyleUtilities, MarginPadding, StyleBorder, Typography, StyleBackground },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const tempId = ref<any>('')
    const uiID = computed({
      get () {
        if (!info.selectedUIItem.value) return ''
        return info.selectedUIItem.value.meta.id
      },
      set (v) {
        if (!info.selectedUIItem.value) return
        tempId.value = v
      }
    })
    const isNotPage = computed(() => {
      return info.selectedUIItem?.value?.type !== 'Page'
    })
    const hasBorder = computed(() => {
      return isNotPage.value && info.selectedUIItem?.value?.type !== 'Hr'
    })
    const hasLayout = computed(() => {
      if (info.selectedUIItem?.value?.type === 'Hr') return false
      if (info.selectedUIItem?.value?.type === 'Modal') return false
      return true
    })
    const hasBackground = computed(() => {
      if (info.selectedUIItem?.value?.pageType === 'popup') return false
      return true
    })
    return {
      ...info,
      t,
      tempId,
      uiID,
      isNotPage,
      hasBorder,
      hasLayout,
      hasBackground
    }
  }
}
</script>
