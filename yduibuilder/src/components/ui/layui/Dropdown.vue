<template>
    <template v-if="uiconfig.meta.custom?.isSplit">
      <div :class="['layui-d-inline-block', uiCss, dragableCss]" :contenteditable="inlineEditItemId==uiconfig.meta.id"
           :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
           :data-pageid="pageid" @dblclick="inlineEditItemId=uiconfig.meta.id" @keyup.enter="inlineEditItemId=''">
        <span :class="['layui-rounded-left', btnCss]" :style="btnStyle"  :contenteditable="inlineEditItemId==uiconfig.meta.id">
          <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title || 'Dropdown'}}</IconWrapper>
        </span>
        <span :class="[splitBtnCss, btnCss]" :style="splitBtnStyle">
          <i :class="{'layui-icon layui-font-12': true,
          'layui-icon-up': uiconfig.meta.custom?.direction=='dropup',
          'layui-icon-down': uiconfig.meta.custom?.direction=='dropdown' || !uiconfig.meta.custom?.direction,
          'layui-icon-left': uiconfig.meta.custom?.direction=='dropleft',
          'layui-icon-right': uiconfig.meta.custom?.direction=='dropright'}"></i>
        </span>
      </div>
    </template>
    <template v-else>
      <div :class="[uiCss, dragableCss, btnCss]"
              :contenteditable="inlineEditItemId==uiconfig.meta.id"
              :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
              :data-pageid="pageid" @dblclick="inlineEditItemId=uiconfig.meta.id" @keyup.enter="inlineEditItemId=''">
        <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title || 'Dropdown'}}</IconWrapper>
        <i :class="{'layui-icon layui-font-12': true,
          'layui-icon-up': uiconfig.meta.custom?.direction=='dropup',
          'layui-icon-down': uiconfig.meta.custom?.direction=='dropdown' || !uiconfig.meta.custom?.direction,
          'layui-icon-left': uiconfig.meta.custom?.direction=='dropleft',
          'layui-icon-right': uiconfig.meta.custom?.direction=='dropright'}"></i>
      </div>
    </template>
</template>
<script lang="ts">
import { computed } from 'vue'
import Dropdown from '@/components/ui/js/Dropdown'
import { useStore } from 'vuex'
import IconWrapper from '@/components/ui/bootstrap/IconWrapper.vue'

export default {
  name: 'Layui_Dropdown',
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
    const store = useStore()
    const dropdown = new Dropdown(props, context, store)
    const setup = dropdown.setup()
    const { parentUi, parentIsNavbar } = setup

    // 如果上层是按钮，那么继承他的outline，size属性
    const dropdownMeta = computed(() => parentIsNavbar.value ? parentUi.value.meta : props.uiconfig.meta)
    const sizing = computed(() => {
      const sizingContext = 'dropdownSizing'
      const sizing = dropdownMeta.value?.css?.dropdownSizing

      return store.getters.translate(sizingContext, sizing)
    })
    const selfHasForeground = computed(() => props.uiconfig.meta?.css?.foregroundTheme && props.uiconfig.meta?.css?.foregroundTheme !== 'default')
    const selfHasBackground = computed(() => props.uiconfig.meta?.css?.backgroundTheme && props.uiconfig.meta?.css?.backgroundTheme !== 'default')
    const theme = computed(() => {
      // 如果自己有背景和前景则用自己的，否则用上层的
      let myBackgruondTheme = props.uiconfig.meta?.css?.backgroundTheme !== 'default' ? props.uiconfig.meta?.css?.backgroundTheme : ''
      myBackgruondTheme = myBackgruondTheme || dropdownMeta.value?.css?.backgroundTheme
      return myBackgruondTheme === 'default' ? '' : myBackgruondTheme
    })
    const foreTheme = computed(() => {
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      let foregroundTheme = props.uiconfig.meta?.css?.foregroundTheme !== 'default' ? props.uiconfig.meta?.css?.foregroundTheme : ''
      foregroundTheme = foregroundTheme || dropdownMeta.value?.css?.foregroundTheme
      return foregroundTheme === 'default' ? '' : foregroundTheme
    })

    const btnCss = computed(() => {
      const arr: any = []
      if (parentIsNavbar.value) {
        if (foreTheme.value) {
          arr.push(store.getters.translate('foregroundTheme', foreTheme.value))
        }
        if (theme.value) {
          arr.push(store.getters.translate('backgroundTheme', theme.value))
        }
        arr.push('layui-nav-item')
        return arr.join(' ')
      }
      arr.push('layui-btn')

      if (dropdownMeta.value.custom?.isOutline) {
        arr.push('layui-btn-primary')
        const border = store.getters.translate('borderColorClass', theme.value)
        arr.push(border)
      }
      if (sizing.value) {
        arr.push(sizing.value)
      }
      if (foreTheme.value) {
        arr.push(store.getters.translate('foregroundTheme', foreTheme.value))
      }
      if (theme.value) {
        arr.push(store.getters.translate('backgroundTheme', theme.value))
      }
      return arr.join(' ')
    })

    const uiCss = computed(() => {
      const css = dropdown.getUICss()
      const arr: any = []
      if (props.uiconfig.meta?.custom?.isSplit) {
        delete css.borderColorClass
        delete css.dropdownSizing
        delete css.backgroundTheme
      }
      arr.push(...Object.values(css))
      if (parentIsNavbar.value) {
        arr.push('layui-nav-item')
      }
      return arr.join(' ')
    })
    const splitBtnCss = computed(() => {
      const css = ['layui-ml-0 layui-rounded-right layui-pl-1 layui-pr-1']
      return css.join(' ')
    })
    const splitBtnStyle = computed(() => {
      const style = dropdown.getUIStyle()
      // 分割线颜色
      let color = 'rgba(255, 255, 255, 0.5)'
      if (style['border-color']) {
        color = style['border-color']
      }
      return btnStyle.value + `;border-left-color: ${color}!important`
    })
    const btnStyle = computed(() => {
      const style = dropdown.getUIStyle()
      // 如果自己有背景和前景则用自己的，否则用上层的
      const color = style?.color || dropdownMeta.value?.style?.color
      const backgroundColor = style?.['background-color'] || dropdownMeta.value?.style?.['background-color']
      if (!selfHasForeground.value && color) { // 有css时用预定义css
        style.color = color
      }
      if (!selfHasBackground.value && backgroundColor) {
        style['background-color'] = backgroundColor
        if (!style['border-color']) {
          style['border-color'] = backgroundColor
        }
      }
      // console.log(arr)
      return dropdown.appendImportant(style)
    })
    const uiStyle = computed(() => {
      const style = dropdown.getUIStyle()
      // 如果不是分体按钮，那么需要考虑属性的继承性；如果是分体按钮，属性的继承由btnStyle处理
      if (props.uiconfig.meta?.custom?.isSplit) {
        delete style['background-color']
        for (const key in style) {
          if (key.match(/^border-/)) {
            delete style[key]
          }
        }
        return dropdown.appendImportant(style)
      }
      // 如果自己有背景和前景则用自己的，否则用上层的
      const color = style?.color || dropdownMeta.value?.style?.color
      const backgroundColor = style?.['background-color'] || dropdownMeta.value?.style?.['background-color']
      if (!selfHasForeground.value && color) { // 有css时用预定义css
        style.color = color
      }
      if (!selfHasBackground.value && backgroundColor) {
        style['background-color'] = backgroundColor
        if (!style['border-color']) {
          style['border-color'] = backgroundColor
        }
      }
      // console.log(arr)
      return dropdown.appendImportant(style)
    })
    return {
      ...setup,
      splitBtnCss,
      splitBtnStyle,
      btnCss,
      btnStyle,
      uiCss,
      uiStyle
    }
  }
}

</script>
