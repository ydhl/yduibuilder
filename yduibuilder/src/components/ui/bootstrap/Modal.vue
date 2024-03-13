<template>
  <div :class="[dragableCss, uiCss]"
       :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-isContainer="true"
       :data-pageid="pageid">
    <div class="modal-dialog">
      <div :class="bodyClass" :style="bodyStyle">
        <div :class="['modal-header subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='head'}]" v-if="!uiconfig.meta.custom?.headless" data-placeInParent="head">
          <template v-if="!myItems.head.length">
            {{t('style.modal.drapTip')}}
          </template>
          <UIBase v-for="(item, index) in myItems.head" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
          <button type="button" class="close" ><span>×</span></button>
        </div>
        <div class="modal-body">
          <template v-if="!hasMainItems">
            {{t('style.modal.drapTip')}}
          </template>
          <UIBase v-for="(item, index) in mainItems" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
        </div>
        <div :class="['modal-footer subui',{'dragenter-subcontainer': isDragIn && dragoverInParent=='foot'}]" v-if="!uiconfig.meta.custom?.footless" data-placeInParent="foot">
          <template v-if="!myItems.foot.length">
            {{t('style.modal.drapTip')}}
          </template>
          <UIBase v-for="(item, index) in myItems.foot" :key="index" :is-readonly="myIsReadonly" :is-lock="myIsLock" :uiconfig="item" :pageid="pageid"></UIBase>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Modal from '../js/Modal'
import { computed, ref } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Model',
  components: { UIBase },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const modal = new Modal(props, context, useStore())
    const setup = modal.setup()
    const mainItems = ref<Array<any>>([])
    const hasMainItems = computed(() => setup.myItems.value.main.length > 0)
    for (const item of setup.myItems.value.main) {
      mainItems.value.push(item)
    }
    const uiStyle = computed(() => {
      const style = modal.getUIStyle()
      for (const styleKey in style) {
        if (styleKey.match(/^border|^outline/)) {
          delete style[styleKey]
        }
      }
      return modal.appendImportant(style)
    })
    const uiCss = computed(() => {
      const css = modal.getUICss()
      delete css.backgroundTheme
      delete css.foregroundTheme
      return Object.values(css).join(' ')
    })
    const bodyClass = computed(() => {
      const cssMap = modal.getUICss()
      const css: any = ['modal-content shadow']
      css.push(cssMap.backgroundTheme)
      css.push(cssMap.foregroundTheme)
      return css.join(' ')
    })
    const bodyStyle = computed(() => {
      const style = modal.getUIStyle()
      const newStyle = {}
      for (const styleKey in style) {
        if (styleKey.match(/^border|^outline/)) {
          newStyle[styleKey] = style[styleKey]
        }
      }
      return modal.appendImportant(newStyle)
    })
    return {
      mainItems,
      hasMainItems,
      ...setup,
      uiCss,
      uiStyle,
      bodyClass,
      bodyStyle
    }
  }
}
</script>
