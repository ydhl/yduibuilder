<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.rangeInput') }}</div>
  <div class="style-body d-none">
    <div class="row mb-1">
      <div class="col-sm-9  offset-3">
        <div class="input-group input-group-sm ">
          <span class="input-group-text p-1">{{ t('style.form.min') }}</span>
          <input type="number" class="form-control  p-1 form-control-sm" v-model="min">
          <span class="input-group-text p-1">{{ t('style.form.max') }}</span>
          <input type="number" class="form-control  p-1 form-control-sm" v-model="max">
          <span class="input-group-text p-1">{{ t('style.form.step') }}</span>
          <input type="number" class="form-control  p-1 form-control-sm" v-model="step">
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import UIInit from '@/components/Common'
export default {
  name: 'StyleInput',
  setup (props: any, context: any) {
    const initInfo = UIInit()
    const { t } = useI18n()

    const min = computed({
      get () {
        return initInfo.getMeta('min', 'custom') || 0
      },
      set (v) {
        initInfo.setMeta('min', v, 'custom')
      }
    })
    const max = computed({
      get () {
        return initInfo.getMeta('max', 'custom') || 0
      },
      set (v) {
        initInfo.setMeta('max', v, 'custom')
      }
    })
    const step = computed({
      get () {
        return initInfo.getMeta('step', 'custom') || 0
      },
      set (v) {
        initInfo.setMeta('step', v, 'custom')
      }
    })
    const foregroundColor = computed({
      get () {
        return initInfo.getMeta('color', 'custom')
      },
      set (v) {
        initInfo.setMeta('color', v, 'custom')
      }
    })
    const backgroundColor = computed({
      get () {
        return initInfo.getMeta('backgroundColor', 'custom')
      },
      set (v) {
        initInfo.setMeta('backgroundColor', v, 'custom')
      }
    })
    const themeCss = computed({
      get () {
        return initInfo.getMeta('theme', 'custom')
      },
      set (v) {
        initInfo.setMeta('theme', v, 'custom')
      }
    })
    return {
      t,
      min,
      max,
      step,
      ...initInfo,
      foregroundColor,
      backgroundColor,
      themeCss
    }
  }
}
</script>
