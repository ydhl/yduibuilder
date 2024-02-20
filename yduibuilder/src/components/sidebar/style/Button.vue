<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.button') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('style.form.title') }}</label>
      <div class="col-sm-9">
        <input class="form-control form-control-sm" type="text" v-model="title">
      </div>
    </div>
    <template v-if="!parentIsButtonGroup">
      <template  v-if="!isButtonGroup">
        <div class="row">
          <label class="col-sm-3 col-form-label text-end">{{ t('style.button.type') }}</label>
          <div class="col-sm-9">
            <select class="form-select form-select-sm" v-model="currType">
              <option :value="type" v-for="type in buttonTypes" :key="type">{{type}}</option>
            </select>
          </div>
        </div>
        <div class="row" v-if="currType=='link'">
          <label class="col-sm-3 col-form-label text-end">{{ t('style.button.linkHref') }}</label>
          <div class="col-sm-9">
            <input type="text" v-model="linkHref" :placeholder="t('style.button.linkHref')" class="form-control form-control-sm">
          </div>
        </div>
      </template>
      <div class="row">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.form.size') }}</label>
        <div class="col-sm-9">
          <select v-model="sizing" class="form-control form-control-sm">
            <option :value="key" :key="key" v-for="(name, key) in cssMap.buttonSizing">{{name}}</option>
          </select>
        </div>
      </div>
      <div class="row" v-if="currType!='link'">
        <label class="col-sm-3 col-form-label text-end">{{ t('style.button.outline') }}</label>
        <div class="col-sm-9 d-flex align-items-center">
          <input class="form-check-input" type="checkbox" v-model="isOutline">
        </div>
      </div>
    </template>
    <div class="row" v-if="!isButtonGroup">
      <label class="col-sm-3 col-form-label text-end">{{ t('common.icon') }}</label>
      <div class="col-sm-9">
        <IconSetting></IconSetting>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import initUI from '@/components/Common'
import { computed, ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { useStore } from 'vuex'
import IconSetting from '@/components/sidebar/style/IconSetting.vue'

export default {
  name: 'StyleButton',
  components: { IconSetting },
  setup (props: any, context: any) {
    const store = useStore()
    const info = initUI()
    const { t } = useI18n()

    const buttonTypes = ref(['button', 'link'])
    const currType = computed({
      get () {
        return info.getMeta('type', 'custom') || 'button'
      },
      set (v) {
        info.setMeta('type', v, 'custom')
      }
    })
    const isOutline = computed({
      get () {
        return info.getMeta('isOutline', 'custom') || false
      },
      set (v) {
        info.setMeta('isOutline', v, 'custom')
      }
    })
    const sizing = computed({
      get: () => {
        return info.getMeta('buttonSizing', 'css') || ''
      },
      set: (v) => {
        info.setMeta('buttonSizing', v, 'css')
      }
    })
    const linkHref = computed({
      get () {
        return info.getMeta('linkHref', 'custom') || ''
      },
      set (v) {
        info.setMeta('linkHref', v, 'custom')
      }
    })
    const title = computed({
      get () {
        const title = info.getMeta('title')
        // console.log(title)
        return title !== undefined ? title : 'Button'
      },
      set (v) {
        // console.log(v)
        info.setMeta('title', v || ' ')
      }
    })

    const parentIsButtonGroup = computed(() => {
      const { parentConfig } = store.getters.getUIItemInPage(info.selectedUIItemId.value, info.selectedPageId.value)
      // console.log(parentConfig.type)
      return parentConfig.type.toLowerCase() === 'buttongroup'
    })
    const isButtonGroup = computed(() => {
      return info.selectedUIItem.value?.type?.toLowerCase() === 'buttongroup'
    })
    return {
      ...info,
      t,
      buttonTypes,
      currType,
      isOutline,
      linkHref,
      title,
      sizing,
      parentIsButtonGroup,
      isButtonGroup
    }
  }
}
</script>
