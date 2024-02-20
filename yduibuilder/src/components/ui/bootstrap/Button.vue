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
  name: 'Bootstrap_Button',
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
    const button = new Button(props, context)
    const store = useStore()

    const myButtonSetup = button.setup()
    // 如果上层是按钮，那么继承他的outline，size属性
    const buttonMeta = computed(() => myButtonSetup.parentIsButtonGroup.value || myButtonSetup.parentIsNavbar.value ? myButtonSetup.parentUi.value.meta : props.uiconfig.meta)

    const selfHasForeground = computed(() => props.uiconfig.meta?.css?.foregroundTheme && props.uiconfig.meta?.css?.foregroundTheme !== 'default')
    const selfHasBackground = computed(() => props.uiconfig.meta?.css?.backgroundTheme && props.uiconfig.meta?.css?.backgroundTheme !== 'default')
    const buttonSizing = computed(() => {
      return store.getters.translate('buttonSizing', buttonMeta.value?.css?.buttonSizing)
    })
    const btnStyle = computed(() => {
      const myStyle = button.getUIStyle()
      // 如果按钮有背景和前景则用按钮的，否则用上层的buttongroup
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
      const css = button.getUICss()
      // 如果按钮有背景和前景则用按钮的，否则用上层的，如buttongroup
      let myBackgruondTheme = css.backgroundTheme ? props.uiconfig.meta?.css?.backgroundTheme : ''
      let myForegroundTheme = css.foregroundTheme ? props.uiconfig.meta?.css?.foregroundTheme : ''
      delete css.backgroundTheme
      delete css.foregroundTheme
      myBackgruondTheme = myBackgruondTheme || buttonMeta.value?.css?.backgroundTheme
      myForegroundTheme = myForegroundTheme || buttonMeta.value?.css?.foregroundTheme

      const raw:any = Object.values(css)
      // 如果上层是按钮，那么继承他的outline，size属性
      if (buttonType.value !== 'link') {
        raw.push('btn')
        const isOutline = buttonMeta.value.custom?.isOutline ? 'outline-' : ''
        if (myBackgruondTheme && myBackgruondTheme !== 'default') {
          raw.push('btn-' + isOutline + myBackgruondTheme)
        } else {
          raw.push('btn-' + isOutline + 'primary')
        }
      } else {
        raw.push('btn btn-link')
      }
      if (myForegroundTheme && myForegroundTheme !== 'default') {
        raw.push(store.getters.translate('foregroundTheme', myForegroundTheme))
      }
      if (myButtonSetup.parentIsButtonGroup.value) {
        const css = buttonSizing.value
        // console.log(parentCss)
        if (css) {
          raw.push(css)
        }
      }

      return raw.join(' ')
    })
    const buttonType = computed(() => {
      return props.uiconfig?.meta?.custom?.type || 'button'
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
