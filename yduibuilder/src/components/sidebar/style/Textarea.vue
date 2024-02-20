<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.textarea') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.maxLength') }}</label>
      <div class="col-sm-9">
        <input v-model="maxLength" type="number" class="form-control form-control-sm">
      </div>
    </div>
    <div class="row">
      <label for="form-textarea-row" class="col-sm-3 col-form-label text-end text-truncate">{{ t('style.form.row') }}</label>
      <div class="col-sm-9">
        <input type="number" class="form-control-sm form-control" id="form-textarea-row" v-model="row">
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-auto-row" v-model="autoRow">
          <label for="form-auto-row" class=" form-check-label text-truncate">{{ t('style.form.autoSize') }}</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-clear-button" v-model="clearButtonVisible">
          <label for="form-clear-button" class=" form-check-label text-truncate">{{ t('style.form.clearButton') }}</label>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-word-count" v-model="wordCountVisible">
          <label for="form-word-count" class=" form-check-label text-truncate">{{ t('style.form.wordCount') }}</label>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import initUI from '@/components/Common'

export default {
  name: 'StyleTextarea',
  setup (props: any, context: any) {
    const { t } = useI18n()
    const info = initUI()

    const row = computed({
      get () {
        return info.getMeta('row', 'custom') || 0
      },
      set (v) {
        info.setMeta('row', v, 'custom')
      }
    })

    const autoRow = computed({
      get () {
        return info.getMeta('autoRow', 'custom') || 0
      },
      set (v) {
        info.setMeta('autoRow', v, 'custom')
      }
    })

    const clearButtonVisible = computed({
      get () {
        return info.getMeta('clearButtonVisible', 'custom') || 0
      },
      set (v) {
        info.setMeta('clearButtonVisible', v, 'custom')
      }
    })

    const wordCountVisible = computed({
      get () {
        return info.getMeta('wordCountVisible', 'custom') || 0
      },
      set (v) {
        info.setMeta('wordCountVisible', v, 'custom')
      }
    })
    const maxLength = computed({
      get () {
        return info.getMeta('maxLength', 'custom') || ''
      },
      set (v) {
        info.setMeta('maxLength', v, 'custom')
      }
    })
    return {
      t,
      row,
      autoRow,
      wordCountVisible,
      clearButtonVisible,
      maxLength
    }
  }
}
</script>
