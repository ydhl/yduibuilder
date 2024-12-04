<template>
  <template  v-if="buttonType=='link'">
    <a :draggable='draggable' :class="[btnCss, dragableCss]" :style="btnStyle"
       :id="myId" :data-type="uiconfig.type"
       :data-isContainer="false" href="javascript:void(0)"
       @dblclick.stop.prevent="inlineEditItemId=uiconfig.meta.id"  @keyup.enter="inlineEditItemId=''"
       :contenteditable="inlineEditItemId==uiconfig.meta.id"
       :data-pageid="pageid">
      <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title}}</IconWrapper>
    </a>
  </template>
  <template  v-else>
    <div :draggable='draggable' :class="[btnCss, dragableCss]" :style="btnStyle"
            :id="myId" :data-type="uiconfig.type"
            :data-pageid="pageid" :data-isContainer="false"
            @dblclick.stop.prevent="inlineEditItemId=uiconfig.meta.id"  @keyup.enter="inlineEditItemId=''"
            :contenteditable="inlineEditItemId==uiconfig.meta.id"
            :type="buttonType">
      <IconWrapper :uiconfig="uiconfig">{{uiconfig.meta.title}}</IconWrapper>
    </div>
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
    const store = useStore()
    const button = new Button(props, context, store)

    const myButtonSetup = button.setup()
    // 如果上层是按钮，那么继承他的outline，size属性
    const buttonMeta = computed(() => myButtonSetup.parentIsNavbar.value ? myButtonSetup.parentUi.value.meta : props.uiconfig.meta)

    const btnStyle = computed(() => {
      const myStyle = button.getUIStyle()
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      const color = myStyle?.color || buttonMeta.value?.style?.color
      const backgroundColor = myStyle?.['background-color'] || buttonMeta.value?.style?.['background-color']
      if (color) {
        myStyle.color = color
      }
      if (backgroundColor) {
        myStyle['background-color'] = backgroundColor
        myStyle['border-color'] = backgroundColor
      }
      if (buttonMeta.value.custom?.isOutline) {
        delete myStyle['background-color']
        delete myStyle['background-image']
      }
      return button.appendImportant(myStyle)
    })

    const btnCss = computed(() => {
      const css = button.getUICss()
      // 如果按钮有背景和前景则用按钮的，否则用上层的
      let myBackgruondTheme = css.backgroundTheme ? button.getMeta('backgroundTheme', 'css') : ''
      let myForegroundTheme = css.foregroundTheme ? button.getMeta('foregroundTheme', 'css') : ''
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
