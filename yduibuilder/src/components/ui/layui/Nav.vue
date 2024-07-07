<template>
  <template v-if="!uiconfig.meta.custom?.type || uiconfig.meta.custom?.type=='normal'">
    <div :draggable='draggable'
         :class="['layui-tab layui-tab-brief',dragableCss, uiCss]"
         :style="uiStyle + ';visibility: visible !important;'" :id="myId" :data-type="uiconfig.type"
         :data-isContainer="true"
         :data-pageid="pageid">
      <ul :class="tabBodyCss">
        <li :class="['layui-nav-item',{'layui-this': item.checked}, foreTheme]" :style="foreStyle" v-for="(item, index) in values" :key="index" >{{item.name}}</li>
        <li :class="['layui-nav-item',foreTheme]" v-for="(item, index) in uiconfig.items" :key="index" :style="foreStyle">
          <UIBase :uiconfig="item" :pageid="pageid" :is-readonly="myIsReadonly" :is-lock="myIsLock"></UIBase>
        </li>
      </ul>
    </div>
  </template>
  <template v-else-if="uiconfig.meta.custom?.type==='tab'">
    <div :draggable='draggable'
         :class="['layui-tab',dragableCss, uiCss]"
         :style="uiStyle" :id="myId" :data-type="uiconfig.type"
         :data-isContainer="true"
         :data-pageid="pageid">
      <ul :class="tabBodyCss">
        <li :class="['layui-nav-item',{'layui-this': item.checked}, foreTheme]" :style="foreStyle" v-for="(item, index) in values" :key="index">{{item.name}}</li>
        <li :class="['layui-nav-item',foreTheme]" v-for="(item, index) in uiconfig.items" :key="index" :style="foreStyle">
          <UIBase :uiconfig="item" :pageid="pageid" :is-readonly="myIsReadonly" :is-lock="myIsLock"></UIBase>
        </li>
      </ul>
    </div>
  </template>
  <template v-else>
    <div :draggable='draggable'
         :class="[dragableCss, uiCss]"
         :style="uiStyle" :id="myId" :data-type="uiconfig.type"
         :data-isContainer="true"
         :data-pageid="pageid">
      <button type="button" :class="['layui-nav-item layui-btn', item.checked ? pillCheckedTheme : foreTheme + ' layui-btn-primary layui-border-0 ']" v-for="(item, index) in values"
           :key="index" :style="foreStyle">{{item.name}}</button>
      <div :class="['layui-nav-item layui-d-flex layui-align-items-center layui-justify-content-center', foreTheme]" v-for="(item, index) in uiconfig.items" :key="index" :style="foreStyle">
        <UIBase :uiconfig="item" :pageid="pageid" :is-readonly="myIsReadonly" :is-lock="myIsLock"></UIBase>
      </div>
    </div>
  </template>
</template>

<script lang="ts">
import UIBase from '@/components/ui/UIBase.vue'
import Nav from '@/components/ui/js/Nav'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Layui_Nav',
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
    const store = useStore()
    const nav = new Nav(props, context, store)
    const pillCheckedTheme = computed(() => {
      const css: any = []
      const uicss = nav.getUICss()
      if (uicss.foregroundTheme) {
        css.push(store.getters.translate('backgroundTheme', props.uiconfig.meta.css?.foregroundTheme))
      }
      return css.length > 0 ? css.join(' ') : ''
    })

    const foreTheme = computed(() => {
      const css = nav.getUICss()
      return css.foregroundTheme
    })
    const foreStyle = computed(() => {
      const style = nav.getUIStyle()
      return style.color ? `color: ${style.color} !important` : ''
    })
    const uiCss = computed(() => {
      const css = nav.getUICss()
      delete css.foregroundTheme
      // console.log(props.uiconfig.meta.custom)
      if (props.uiconfig.meta.custom?.type === 'pill') {
        css.pill = 'layui-d-flex'
        if (props.uiconfig.meta.custom?.justified) {
          css.justified = 'layui-nav-justified'
        }
        if (props.uiconfig.meta.custom?.filled) {
          css.filled = 'layui-nav-fill'
        }
      }
      return Object.values(css).join(' ')
    })
    const tabBodyCss = computed(() => {
      const css = ['layui-tab-title']
      if (props.uiconfig.meta.custom?.justified) {
        css.push('layui-nav-justified')
      }

      if (props.uiconfig.meta.custom?.filled) {
        css.push('layui-nav-fill')
      }
      return css.join(' ')
    })
    return {
      ...nav.setup(),
      uiCss,
      pillCheckedTheme,
      foreStyle,
      tabBodyCss,
      foreTheme
    }
  }
}

</script>
