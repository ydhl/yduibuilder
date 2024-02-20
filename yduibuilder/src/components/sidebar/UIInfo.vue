<template>
  <div v-if="!selectedUIItemId" class="d-flex flex-column h-100 text-muted justify-content-center align-content-center align-items-center">
    <i class="iconfont icon-click" style="font-size: 6rem"></i>
    <div class="pe-2">{{t('common.pleaseSelectUIItem')}}</div>
  </div>
  <div v-if="selectedUIItemId" class="style-panel">
    <p class="pt-2 d-flex align-items-center"><i :class="`iconfont text-primary icon-${selectedUIItem.type.toLowerCase()}`"></i>&nbsp;{{selectedUIItem.meta.title || selectedUIItem.type}}&nbsp;<small class="text-muted">#{{uiID}}</small></p>
    <div class="style-header"><i class="iconfont icon-tree-close"></i> {{t('style.base.name')}}</div>
    <div class="style-body d-none">
      <div class="row">
        <label for="form-title" class="col-sm-3 col-form-label text-end">{{ t('style.base.title') }}</label>
        <div class="col-sm-9">
          <input type="text" v-model="selectedUIItem.meta.title" class="form-control form-control-sm" id="form-title">
        </div>
      </div>
      <div class="row">
        <label for="form-desc" class="col-sm-3 col-form-label text-end">{{ t('style.base.desc') }}</label>
        <div class="col-sm-9">
          <textarea v-model="selectedUIItem.meta.desc" class="form-control form-control-sm" id="form-desc"></textarea>
        </div>
      </div>
    </div>
    <component :is="itemStyle"></component>
    <StyleForm v-if="selectedUIItemIsInput"></StyleForm>
  </div>
</template>

<script lang="ts">
import StyleForm from '@/components/sidebar/style/Form.vue'
import initUI from '@/components/Common'
import { ref, computed, defineAsyncComponent } from 'vue'
import { useI18n } from 'vue-i18n'
export default {
  name: 'UIStyle',
  components: { StyleForm },
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
    const itemStyle = computed(() => {
      // 这句判断的目的，只是为了让computed是响应式的，要不然下面defineAsyncComponent 中的promise不是响应式的
      // props改变后，uiComponentWrap不会刷新
      if (!info.selectedUIItem.value) return null
      return defineAsyncComponent(
        () => new Promise((resolve) => {
          let type = info.selectedUIItem.value.type
          if (type.toLowerCase() === 'buttongroup') type = 'Button'
          require([`@/components/sidebar/style/${type}.vue`], resolve)
        }))
    })
    return {
      ...info,
      t,
      tempId,
      uiID,
      itemStyle
    }
  }
}
</script>
