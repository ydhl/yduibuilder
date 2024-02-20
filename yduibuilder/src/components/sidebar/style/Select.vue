<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.select') }}</div>
  <div class="style-body d-none">
    <StyleFormValueList>
      <div v-if="!isMobile">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-multiple" v-model="isMultiple">
          <label for="form-multiple" class=" form-check-label text-truncate">{{ t('style.form.multiple') }}</label>
        </div>
        <template v-if="false">
        <div class="form-check form-check-inline">
          <input type="checkbox" class="form-check-input" id="form-tags" v-model="taggable">
          <label for="form-tags" class=" form-check-label text-truncate">{{ t('style.form.taggable') }}</label>
        </div>
          <div class="form-check form-check-inline" v-if="taggable">
            <input type="checkbox" class="form-check-input" id="form-searchable" v-model="isSearchable">
            <label for="form-searchable" class=" form-check-label text-truncate">{{ t('style.form.searchable') }}</label>
          </div>
        </template>
        <div class="input-group input-group-sm" v-if="isMultiple && !taggable">
          <span class="input-group-text">{{ t('style.form.size') }}</span>
          <input type="number" class="form-control" v-model="size">
        </div>
      </div>
    </StyleFormValueList>
  </div>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import UIInit from '@/components/Common'
import StyleFormValueList from '@/components/sidebar/style/ValueList.vue'
import { useStore } from 'vuex'

export default {
  name: 'StyleSelect',
  components: { StyleFormValueList },
  setup (props: any, context: any) {
    const initInfo = UIInit()
    const { t } = useI18n()
    const store = useStore()

    const isMultiple = computed({
      get () {
        return initInfo.getMeta('multiple', 'custom') || false
      },
      set (v) {
        initInfo.setMeta('multiple', v, 'custom')
      }
    })
    const taggable = computed({
      get () {
        return initInfo.getMeta('taggable', 'custom') || false
      },
      set (v) {
        initInfo.setMeta('taggable', v, 'custom')
      }
    })
    const isSearchable = computed({
      get () {
        return initInfo.getMeta('searchable', 'custom') || false
      },
      set (v) {
        initInfo.setMeta('searchable', v, 'custom')
      }
    })
    const size = computed({
      get () {
        return initInfo.getMeta('size', 'custom') || 0
      },
      set (v) {
        initInfo.setMeta('size', v, 'custom')
      }
    })
    const isMobile = computed(() => store.state.design?.endKind === 'mobile')
    return {
      t,
      isMultiple,
      taggable,
      isSearchable,
      size,
      ...initInfo,
      isMobile
    }
  }
}
</script>
