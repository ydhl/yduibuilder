<template>
  <div class="style-header"><i class="iconfont icon-tree-close"></i> {{ t('ui.dropdown') }}</div>
  <div class="style-body d-none">
    <div class="row">
      <label for="dropdown-title" class="col-sm-3 col-form-label text-end">{{ t('style.form.title') }}</label>
      <div class="col-sm-9">
        <input type="text" v-model="title" class="form-control form-control-sm" id="dropdown-title">
      </div>
    </div>
    <DropdownValueList></DropdownValueList>
    <div class="row">
      <div class="col-sm-9 offset-3">
        <label class=" form-check-label text-truncate d-block"><input type="checkbox" v-model="isSplitBtn" value="1"> {{ t('style.dropdown.splitBtn') }}</label>
      </div>
    </div>
    <div class="row">
      <label for="dropdown-title" class="col-sm-3 col-form-label text-end">{{ t('style.dropdown.direction') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="direction">
          <option value="dropup">{{ t('style.dropdown.directionUp') }}</option>
          <option value="dropdown">{{ t('style.dropdown.directionDown') }}</option>
          <option value="dropleft">{{ t('style.dropdown.directionLeft') }}</option>
          <option value="dropright">{{ t('style.dropdown.directionRight') }}</option>
        </select>
      </div>
    </div>
    <div class="row">
      <label for="dropdown-title" class="col-sm-3 col-form-label text-truncate text-end">{{ t('style.dropdown.menuAlign') }}</label>
      <div class="col-sm-9">
        <select class="form-select form-select-sm" v-model="menuAlign">
          <option value="right">{{ t('style.dropdown.menuAlignRight') }}</option>
          <option value="left">{{ t('style.dropdown.menuAlignLeft') }}</option>
        </select>
      </div>
    </div>
    <template v-if="!parentIsButtonGroupOrNav">
      <div class="row">
        <label for="dropdown-title" class="col-sm-3 col-form-label text-truncate text-end">{{ t('style.sizing') }}</label>
        <div class="col-sm-9">
          <select class="form-select form-select-sm" v-model="size">
            <option :value="value" :key="value" v-for="(name, value) in cssMap.dropdownSizing">{{name}}</option>
          </select>
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" v-model="isOutline" id="btn-outline">
            <label class="form-check-label" for="btn-outline">
              {{ t('style.button.outline') }}
            </label>
          </div>
        </div>
      </div>
    </template>

    <div class="row">
      <label class="col-sm-3 col-form-label text-end">{{ t('common.icon') }}</label>
      <div class="col-sm-9">
        <IconSetting></IconSetting>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import DropdownValueList from '@/components/sidebar/style/DropdownValueList.vue'
import { useI18n } from 'vue-i18n'
import { computed } from 'vue'
import initUI from '@/components/Common'
import { useStore } from 'vuex'
import IconSetting from '@/components/sidebar/style/IconSetting.vue'

export default {
  name: 'StyleDropdown',
  components: { IconSetting, DropdownValueList },
  setup (props: any, context: any) {
    const { t } = useI18n()
    const store = useStore()
    const info = initUI()
    const title = computed({
      get: () => {
        return info.getMeta('title') || ''
      },
      set: (v) => {
        info.setMeta('title', v)
      }
    })
    const menuAlign = computed({
      get: () => {
        return info.getMeta('menuAlign', 'custom') || 'left'
      },
      set: (v) => {
        info.setMeta('menuAlign', v, 'custom')
      }
    })
    const direction = computed({
      get: () => {
        return info.getMeta('direction', 'custom') || 'dropdown'
      },
      set: (v) => {
        info.setMeta('direction', v, 'custom')
      }
    })
    const size = computed({
      get: () => {
        return info.getMeta('dropdownSizing', 'css') || ''
      },
      set: (v) => {
        info.setMeta('dropdownSizing', v, 'css')
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
    const isSplitBtn = computed({
      get () {
        return info.getMeta('isSplit', 'custom') || false
      },
      set (v) {
        info.setMeta('isSplit', v, 'custom')
      }
    })
    const parentIsButtonGroupOrNav = computed(() => {
      const { parentConfig } = store.getters.getUIItemInPage(info.selectedUIItemId.value, info.selectedPageId.value)
      return parentConfig.type.toLowerCase() === 'buttongroup' || parentConfig.type.toLowerCase() === 'navbar' || parentConfig.type.toLowerCase() === 'nav'
    })

    return {
      ...info,
      t,
      menuAlign,
      direction,
      size,
      title,
      isOutline,
      parentIsButtonGroupOrNav,
      isSplitBtn
    }
  }
}
</script>
