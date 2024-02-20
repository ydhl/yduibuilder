<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.nav') }}</div>
  <div class="style-body d-none">
    <StyleValueList></StyleValueList>
    <template v-if="!isMobile">
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.nav.type') }}</label>
        <div class="col-sm-9">
          <select class="form-select-sm form-select" v-model="type">
            <option value="tab">Tab</option>
            <option value="pill">Pill</option>
            <option value="normal">Normal</option>
          </select>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-9 offset-3">
          <label class=" form-check-label text-truncate d-block"><input type="checkbox" v-model="fill" value="1"> {{ t('style.nav.fill') }}</label>
        </div>
      </div>
      <div class="row">
        <div class="col-sm-9 offset-3">
          <label class=" form-check-label text-truncate d-block"><input type="checkbox" v-model="justified" value="1"> {{ t('style.nav.justified') }}</label>
        </div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { useI18n } from 'vue-i18n'
import StyleValueList from '@/components/sidebar/style/ValueList.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'StyleNav',
  components: { StyleValueList },
  setup (props: any, context: any) {
    const info = initUI()
    const { t } = useI18n()
    const store = useStore()
    const type = computed({
      get: () => {
        return info.getMeta('type', 'custom') || ''
      },
      set: (v) => {
        info.setMeta('type', v, 'custom')
      }
    })
    const justified = computed({
      get: () => {
        return info.getMeta('justified', 'custom') || ''
      },
      set: (v) => {
        info.setMeta('justified', v, 'custom')
      }
    })
    const fill = computed({
      get: () => {
        return info.getMeta('filled', 'custom') || ''
      },
      set: (v) => {
        info.setMeta('filled', v, 'custom')
      }
    })
    const isMobile = computed(() => store.state.design.endKind === 'mobile')
    return {
      ...info,
      t,
      type,
      justified,
      isMobile,
      fill
    }
  }
}
</script>
