<template>
    <div :draggable='draggable'
          :class="[dragableCss, myCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid" data-ride="carousel">
      <template v-if="uiconfig.meta.custom?.showIndicator">
        <ol class="carousel-indicators">
          <template v-for="(item, index) in uiconfig.items?.length" :key="index">
            <li :data-target="`#${uiconfig.meta.id}`" :data-slide-to="index" :class="(!uiconfig.meta.custom?.activeSlide && index ==0) || uiconfig.meta.custom?.activeSlide === index ? 'active' : ''"></li>
          </template>
          <template v-if="!uiconfig.items?.length">
            <li :data-target="`#${uiconfig.meta.id}`" data-slide-to="0" class="active"></li>
          </template>
        </ol>
      </template>
      <div class="carousel-inner">
        <template v-if="!uiconfig.items?.length">
          <div class="carousel-item active">
            <div class="d-block w-100 d-flex justify-content-center align-items-center" :style="placeholderStyle">
              <div class="display-1 text-center">{{t('style.carousel.slide')}}</div>
            </div>
          </div>
        </template>
        <template v-else>
          <div v-for="(subpage, index) in uiconfig.items" :key="index"
               :class="{'carousel-item': true, 'active': (!uiconfig.meta.custom?.activeSlide && index ==0) || uiconfig.meta.custom?.activeSlide === index}">
            <UIBase :uiconfig="subpage"  :is-readonly="true" :is-lock="myIsLock" :pageid="pageid"></UIBase>
          </div>
        </template>
      </div>
      <template v-if="uiconfig.meta.custom?.showControl">
        <a class="carousel-control-prev" type="button" :data-target="`#${uiconfig.meta.id}`" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" type="button" :data-target="`#${uiconfig.meta.id}`" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
      </template>
    </div>
</template>

<script lang="ts">
import Carousel from '@/components/ui/js/Carousel'
import UIBase from '@/components/ui/UIBase.vue'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Carousel',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  components: { UIBase },
  setup (props: any, context: any) {
    const store = useStore()
    const carousel = new Carousel(props, context, store)
    const setup = carousel.setup()

    const myCss = computed(() => {
      const uicss = carousel.getUICss()
      const arr: any = Object.values(uicss)
      arr.push('carousel slide')
      if (props.uiconfig.meta.custom?.effect === 'crossfade') {
        arr.push('carousel-fade')
      }
      return arr
    })

    const placeholderStyle = computed(() => {
      const style: any = ['background-color:#777']
      if (!props.uiconfig.meta.style?.height && !props.uiconfig.meta.style?.['min-height']) {
        style.push('height: 300px')
      } else {
        style.push(`height: ${props.uiconfig.meta.style?.height};min-height:${props.uiconfig.meta.style?.['min-height']}`)
      }
      return style.join(';')
    })

    return {
      ...setup,
      myCss,
      placeholderStyle
    }
  }
}

</script>
