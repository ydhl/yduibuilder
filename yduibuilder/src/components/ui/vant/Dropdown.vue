<template>
  <div :class="['van-dropdown-menu',dragableCss,uiCss]"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" @dblclick="inlineEditItemId=uiconfig.meta.id" @keyup.enter="inlineEditItemId=''">
    <div :class="['van-dropdown-menu__bar', backgroundTheme]" :style="backgroundStyle">
      <div role="button" class="van-dropdown-menu__item">
        <span :class="{'van-dropdown-menu__title': true,'van-dropdown-menu__title--down': uiconfig.meta.custom?.direction==='dropup'}">
          <div :class="['van-ellipsis', forceTheme]" :style="forceStyle">{{uiconfig.meta.title || 'Dropdown'}}</div>
        </span>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import Dropdown from '@/components/ui/js/Dropdown'
import { useStore } from 'vuex'
import { computed } from 'vue'

export default {
  name: 'Vant_Dropdown',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const store = useStore()
    const dropdown = new Dropdown(props, context, store)
    const setup = dropdown.setup()
    const { parentUi, parentIsNavbar } = setup
    // 如果上层是按钮，那么继承他的outline，size属性
    const dropdownMeta = computed(() => parentIsNavbar.value ? parentUi.value.meta : props.uiconfig.meta)
    const backgroundTheme = computed(() => {
      // 如果自己有背景和前景则用自己的，否则用上层的
      let myBackgruondTheme = props.uiconfig.meta?.css?.backgroundTheme !== 'default' ? props.uiconfig.meta?.css?.backgroundTheme : ''
      myBackgruondTheme = myBackgruondTheme || dropdownMeta.value?.css?.backgroundTheme
      return myBackgruondTheme === 'default' ? '' : store.getters.translate('backgroundTheme', myBackgruondTheme)
    })
    const forceTheme = computed(() => {
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      let foregroundTheme = props.uiconfig.meta?.css?.foregroundTheme !== 'default' ? props.uiconfig.meta?.css?.foregroundTheme : ''
      foregroundTheme = foregroundTheme || dropdownMeta.value?.css?.foregroundTheme
      return foregroundTheme === 'default' ? '' : store.getters.translate('foregroundTheme', foregroundTheme)
    })
    const backgroundStyle = computed(() => {
      const style = dropdown.getUIStyle()
      const _: any = {}
      const backgroundColor = style?.['background-color'] || dropdownMeta.value?.style?.['background-color']
      if (backgroundColor) {
        _['background-color'] = backgroundColor
      }
      return dropdown.appendImportant(_)
    })
    const forceStyle = computed(() => {
      const style = dropdown.getUIStyle()
      const _: any = {}
      const color = style?.color || dropdownMeta.value?.style?.color
      if (color) {
        _.color = color
      }
      return dropdown.appendImportant(_)
    })

    const uiStyle = computed(() => {
      const style = dropdown.getUIStyle()
      delete style.color
      delete style['background-color']
      return dropdown.appendImportant(style)
    })
    const uiCss = computed(() => {
      const css = dropdown.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    return {
      ...setup,
      uiStyle,
      uiCss,
      backgroundTheme,
      forceTheme,
      backgroundStyle,
      forceStyle
    }
  }
}

</script>
