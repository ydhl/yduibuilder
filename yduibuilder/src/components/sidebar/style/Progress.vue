<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.progress') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.value') }}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control form-control-sm" v-model="value">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.min') }}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control form-control-sm" v-model="min">
      </div>
    </div>
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.max') }}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control form-control-sm" v-model="max">
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <label class=" form-check-label text-truncate"><input type="checkbox" v-model="label" value="1"> {{ t('style.progress.label') }}</label>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <label class=" form-check-label text-truncate"><input type="checkbox" v-model="striped" value="1"> {{ t('style.progress.striped') }}</label>
      </div>
    </div>
    <div class="row" v-if="striped">
      <div class="col-sm-9 offset-3">
        <label class=" form-check-label text-truncate"><input type="checkbox" v-model="animatedStrip" value="1"> {{ t('style.progress.animatedStripes') }}</label>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'

export default {
  name: 'StyleProgress',
  setup (props: any, context: any) {
    const info = initUI()

    const min = computed({
      get () {
        return info.getMeta('min', 'custom') || 0
      },
      set (v) {
        info.setMeta('min', v, 'custom')
      }
    })
    const max = computed({
      get () {
        return info.getMeta('max', 'custom') || 100
      },
      set (v) {
        info.setMeta('max', v, 'custom')
      }
    })
    const value = computed({
      get () {
        return info.getMeta('value') || 50
      },
      set (v) {
        info.setMeta('value', v)
      }
    })
    const label = computed({
      get () {
        return info.getMeta('label', 'custom') || false
      },
      set (v) {
        info.setMeta('label', v, 'custom')
      }
    })
    const striped = computed({
      get () {
        return info.getMeta('striped', 'custom') || false
      },
      set (v) {
        info.setMeta('striped', v, 'custom')
      }
    })
    const animatedStrip = computed({
      get () {
        return info.getMeta('animatedStrip', 'custom') || false
      },
      set (v) {
        info.setMeta('animatedStrip', v, 'custom')
      }
    })
    const { t } = useI18n()
    return {
      ...info,
      t,
      min,
      max,
      value,
      label,
      striped,
      animatedStrip
    }
  }
}
</script>
