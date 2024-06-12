<template>
  <div :draggable='draggable' :style="uiStyle" :id="myId" :data-type="uiconfig.type"
    :data-pageid="pageid"
    :class="[dragableCss, uiCss]">
    <template v-if="isHorizontal">
      <div :class="labelCss" v-if="hasTitle">
        <label>{{uiconfig.meta.title}}<span v-if="uiconfig.meta?.form?.required" class="van-text-danger">*</span></label>
      </div>
      <div class="van-cell__value van-field__value">
        <div :class="bodyCss">
          <slot></slot>
        </div>
        <div :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip" class="van-field__error-message van-text-muted">{{uiconfig.meta?.form?.helpTip}}</div>
      </div>
      <i class="van-badge__wrapper van-icon van-icon-arrow van-cell__right-icon" v-if="uiconfig.type=='Select'"></i>
    </template>
    <template v-else>
      <div :class="labelCss" v-if="hasTitle">{{uiconfig.meta.title}}<span v-if="uiconfig.meta?.form?.required" class="van-text-danger">*</span></div>
      <div class="van-cell__value van-field__value" :style="boxStyle">
        <div :class="bodyCss">
          <slot></slot>
          <i class="van-badge__wrapper van-icon van-icon-arrow van-cell__right-icon" v-if="uiconfig.type=='Select'"></i>
        </div>
        <div :id="uiconfig.meta.id+'Help'"  v-if="hasHelpTip" class="van-field__error-message van-text-muted">{{uiconfig.meta?.form?.helpTip}}</div>
      </div>
    </template>
  </div>
</template>

<script lang="ts">
import FormGroup from '@/components/ui/js/FormGroup'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Vant_FromGroup',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const formGroup = new FormGroup(props, context, useStore())
    const setup = formGroup.setup()
    const isHorizontal = computed(() => {
      const horizontal = props.uiconfig.meta?.form?.horizontal
      if (horizontal === undefined) return true
      return horizontal
    })
    const uiCss = computed(() => {
      const css = Object.values(formGroup.getUICss())
      if (isHorizontal.value) {
        css.push('van-cell van-field')
      } else {
        css.push('van-cell van-field van-flex-column')
      }
      if (props.uiconfig.meta?.form?.state === 'hidden') {
        css.push('hidden-preview')
      }

      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        css.push('van-text-muted')
      }
      return css.join(' ')
    })
    const labelCss = computed(() => {
      const css: any = []
      if (isHorizontal.value) {
        css.push('van-cell__title van-field__label')
      }
      if (props.uiconfig.meta.titleAlign) {
        css.push('van-text-' + props.uiconfig.meta.titleAlign)
      }
      if (props.uiconfig.meta?.form?.state === 'readonly' || props.uiconfig.meta?.form?.state === 'disabled') {
        css.push('van-text-muted')
      }
      return css
    })
    const bodyCss = computed(() => {
      const css: any = ['van-field__body']
      if (props.uiconfig.type === 'Textarea') {
        css.push('van-align-items-start')
      } else {
        css.push('van-align-items-center')
      }
      return css
    })
    const uiStyle = computed(() => {
      const style = formGroup.getUIStyle()
      // 垂直排列时阴影交给内部元素处理
      if (!isHorizontal.value) {
        delete style['box-shadow']
      }
      return formGroup.appendImportant(style)
    })
    const boxStyle = computed(() => {
      const style = formGroup.getUIStyle()
      // 阴影交给内部元素处理
      return 'box-shadow:' + style['box-shadow']
    })
    return {
      ...setup,
      labelCss,
      bodyCss,
      uiStyle,
      uiCss,
      isHorizontal,
      boxStyle
    }
  }
}

</script>
