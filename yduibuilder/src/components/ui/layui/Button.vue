<template>
  <template  v-if="buttonType=='link'">
    <a :draggable='draggable' :class="[btnCss, dragableCss]" :style="btnStyle"
       :id="myId" :data-type="uiconfig.type"
       :data-isContainer="false" href="#"
       @dblclick="inlineEditItemId=uiconfig.meta.id"  @keyup.enter="inlineEditItemId=''"
       :contenteditable="inlineEditItemId==uiconfig.meta.id"
       :data-pageid="pageid">
      <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title}}</IconWrapper>
    </a>
  </template>
  <template  v-else>
    <button :draggable='draggable' :class="[btnCss, dragableCss]" :style="btnStyle"
            :id="myId" :data-type="uiconfig.type"
            :data-pageid="pageid" :data-isContainer="false"
            @dblclick="inlineEditItemId=uiconfig.meta.id"  @keyup.enter="inlineEditItemId=''"
            :contenteditable="inlineEditItemId==uiconfig.meta.id"
            :type="buttonType">
      <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title}}</IconWrapper>
    </button>
  </template>
</template>

<script lang="ts">
import Button from '@/components/ui/js/Button'
import { computed } from 'vue'
import { useStore } from 'vuex'
import IconWrapper from '@/components/ui/bootstrap/IconWrapper.vue'

export default {
  name: 'Layui_Button',
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
    const button = new Button(props, context, store)

    const myButtonSetup = button.setup()
    // 如果上层是按钮，那么继承他的outline，size属性
    const buttonMeta = computed(() => myButtonSetup.parentIsNavbar.value ? myButtonSetup.parentUi.value.meta : props.uiconfig.meta)
    const selfHasForeground = computed(() => props.uiconfig.meta?.css?.foregroundTheme && props.uiconfig.meta?.css?.foregroundTheme !== 'default')
    const selfHasBackground = computed(() => props.uiconfig.meta?.css?.backgroundTheme && props.uiconfig.meta?.css?.backgroundTheme !== 'default')

    const btnStyle = computed(() => {
      const myStyle = button.getUIStyle()
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      const color = myStyle?.color || buttonMeta.value?.style?.color
      const backgroundColor = myStyle?.['background-color'] || buttonMeta.value?.style?.['background-color']
      if (!selfHasForeground.value && color) {
        myStyle.color = color
      }
      if (!selfHasBackground.value && backgroundColor) {
        myStyle['background-color'] = backgroundColor
        myStyle['border-color'] = backgroundColor
      }
      return button.appendImportant(myStyle)
    })

    const btnCss = computed(() => {
      const uicss = button.getUICss()
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      let myBackgruondTheme = uicss.backgroundTheme ? props.uiconfig.meta?.css?.backgroundTheme : ''
      let myForegroundTheme = uicss.foregroundTheme ? props.uiconfig.meta?.css?.foregroundTheme : ''
      delete uicss.backgroundTheme
      delete uicss.foregroundTheme
      myBackgruondTheme = myBackgruondTheme || buttonMeta.value?.css?.backgroundTheme
      myForegroundTheme = myForegroundTheme || buttonMeta.value?.css?.foregroundTheme

      const raw = Object.values(uicss)
      raw.push('layui-btn')
      if (buttonType.value === 'link') {
        raw.push('layui-btn-primary layui-border-0')
      }
      if (buttonMeta.value.custom?.isOutline) {
        // 删除button theme outline 通过边框和前景色来实现
        delete uicss.backgroundTheme
        raw.push('layui-btn-primary')
        const border = store.getters.translate('borderColorClass', myBackgruondTheme)
        raw.push(border)
      } else {
        const theme = store.getters.translate('backgroundTheme', myBackgruondTheme)
        raw.push(theme)
      }

      if (myForegroundTheme && myForegroundTheme !== 'default') {
        raw.push(store.getters.translate('foregroundTheme', myForegroundTheme))
      }

      return raw.join(' ')
    })
    const buttonType = computed(() => {
      return buttonMeta.value?.custom?.type || 'button'
    })
    return {
      ...myButtonSetup,
      btnCss,
      btnStyle,
      buttonType
    }
  }
}
</script>
