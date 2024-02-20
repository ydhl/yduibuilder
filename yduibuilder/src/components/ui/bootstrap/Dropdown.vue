<template>
  <div :class="[dragableCss,uiCss,uiconfig.meta.custom?.direction||'dropdown',cssAsChild]"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" @dblclick="inlineEditItemId=uiconfig.meta.id" @keyup.enter="inlineEditItemId=''">
    <template v-if="uiconfig.meta.custom?.isSplit">
      <a :class="[btnCss]" :id="uiconfig.meta.id+'MenuLink'" type="button" :style="btnStyle"
              :contenteditable="inlineEditItemId==uiconfig.meta.id">
        <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title || 'Dropdown'}}</IconWrapper>
      </a>
      <a :class="['dropdown-toggle dropdown-toggle-split', splitBtnCss]" :style="btnStyle"></a>
    </template>
    <template v-else>
      <a :class="['dropdown-toggle', btnCss]"  :style="btnStyle"
              role="button" :id="uiconfig.meta.id+'MenuLink'" type="button"
              :contenteditable="inlineEditItemId==uiconfig.meta.id">
        <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title || 'Dropdown'}}</IconWrapper>
      </a>
    </template>
  </div>
</template>

<script lang="ts">
import { computed } from 'vue'
import Dropdown from '@/components/ui/js/Dropdown'
import { useStore } from 'vuex'
import IconWrapper from '@/components/ui/bootstrap/IconWrapper.vue'

export default {
  name: 'Bootstrap_Dropdown',
  components: { IconWrapper },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const dropdown = new Dropdown(props, context)
    const setup = dropdown.setup()
    const { parentUi, parentIsNavbar, parentIsButtonGroup } = setup
    const store = useStore()

    // 如果上层是按钮，那么继承他的outline，size属性
    const dropdownMeta = computed(() => parentIsButtonGroup.value || parentIsNavbar.value ? parentUi.value.meta : props.uiconfig.meta)
    const sizing = computed(() => {
      let sizingContext = ''
      let sizing = ''
      if (parentIsButtonGroup.value) { // 如果父容器是按钮组
        sizing = dropdownMeta.value?.css?.buttonSizing
        sizingContext = 'buttonSizing'
      } else {
        sizing = dropdownMeta.value?.css?.dropdownSizing
        sizingContext = 'dropdownSizing'
      }
      return store.getters.translate(sizingContext, sizing)
    })
    const selfHasForeground = computed(() => props.uiconfig.meta?.css?.foregroundTheme && props.uiconfig.meta?.css?.foregroundTheme !== 'default')
    const selfHasBackground = computed(() => props.uiconfig.meta?.css?.backgroundTheme && props.uiconfig.meta?.css?.backgroundTheme !== 'default')
    const theme = computed(() => {
      // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
      let myBackgruondTheme = props.uiconfig.meta?.css?.backgroundTheme !== 'default' ? props.uiconfig.meta?.css?.backgroundTheme : ''
      myBackgruondTheme = myBackgruondTheme || dropdownMeta.value?.css?.backgroundTheme
      return myBackgruondTheme === 'default' ? '' : myBackgruondTheme
    })
    const forceTheme = computed(() => {
      // 如果按钮有背景和前景则用按钮的，否则用上层的buttongroup
      let foregroundTheme = props.uiconfig.meta?.css?.foregroundTheme !== 'default' ? props.uiconfig.meta?.css?.foregroundTheme : ''
      foregroundTheme = foregroundTheme || dropdownMeta.value?.css?.foregroundTheme
      return foregroundTheme === 'default' ? '' : foregroundTheme
    })

    const btnCss = computed(() => {
      const arr: any = []
      if (parentIsNavbar.value) {
        if (forceTheme.value) {
          arr.push(store.getters.translate('foregroundTheme', forceTheme.value))
        }
        if (theme.value) {
          arr.push(store.getters.translate('backgroundTheme', theme.value))
        }
        arr.push('nav-link')
        return arr.join(' ')
      }
      arr.push('btn btn-block')

      const isOutline = dropdownMeta.value.custom?.isOutline ? 'outline-' : ''

      arr.push(theme.value ? 'btn-' + isOutline + theme.value : 'btn-' + isOutline + 'primary')
      if (sizing.value) {
        arr.push(sizing.value)
      }
      if (forceTheme.value) {
        arr.push(store.getters.translate('foregroundTheme', forceTheme.value))
      }
      return arr.join(' ')
    })
    const btnStyle = computed(() => {
      const style = dropdown.getUIStyle()
      // 如果自己有背景和前景则用自己的，否则用上层的，如buttongroup
      const color = style?.color || dropdownMeta.value?.style?.color
      const backgroundColor = style?.['background-color'] || dropdownMeta.value?.style?.['background-color']
      if (!selfHasForeground.value && color) { // 有css时用预定义css
        style.color = color
      }
      if (!selfHasBackground.value && backgroundColor) {
        style['background-color'] = backgroundColor
        style['border-color'] = backgroundColor
      }
      // console.log(arr)
      return dropdown.appendImportant(style)
    })
    const splitBtnCss = computed(() => {
      if (parentIsNavbar.value) return 'nav-link'
      const arr: any = ['btn']
      const isOutline = dropdownMeta.value?.custom?.isOutline ? 'outline-' : ''
      arr.push(theme.value ? 'btn-' + isOutline + theme.value : 'btn-' + isOutline + 'primary')
      if (sizing.value) {
        arr.push(sizing.value)
      }
      if (forceTheme.value) {
        arr.push(store.getters.translate('foregroundTheme', forceTheme.value))
      }
      return arr.join(' ')
    })
    const cssAsChild = computed(() => {
      const arr: any = []
      if (parentIsNavbar.value) {
        arr.push('nav-item')
      }
      if (parentIsButtonGroup.value || dropdownMeta.value?.custom?.isSplit) {
        arr.push('btn-group')
      }
      return arr.join(' ')
    })
    const uiStyle = computed(() => {
      const style = dropdown.getUIStyle()
      delete style.color
      delete style['background-color']
      return dropdown.appendImportant(style)
    })

    const uiCss = computed(() => {
      const css = dropdown.getUICss()
      // sizing和theme是在内部button元素上不是在整体元素上
      delete css.dropdownSizing
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    return {
      btnCss,
      btnStyle,
      splitBtnCss,
      ...setup,
      uiStyle,
      uiCss,
      cssAsChild
    }
  }
}

</script>
