<template>
  <div :draggable='draggable' @dblclick.stop.prevent="inlineEditItemId=uiconfig.meta.id"
       :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
       :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid">
    <template v-if="type=='h1'">
      <h1>
        {{uiconfig.meta.value||uiconfig.meta.title}}
      </h1>
    </template>
    <template v-if="type=='h2'">
      <h2>{{uiconfig.meta.value||uiconfig.meta.title}}</h2>
    </template>
    <template v-if="type=='h3'">
      <h3>{{uiconfig.meta.value||uiconfig.meta.title}}</h3>
    </template>
    <template v-if="type=='h4'">
      <h4>{{uiconfig.meta.value||uiconfig.meta.title}}</h4>
    </template>
    <template v-if="type=='h5'">
      <h5>{{uiconfig.meta.value||uiconfig.meta.title}}</h5>
    </template>
    <template v-if="type=='h6'">
      <h6>{{uiconfig.meta.value||uiconfig.meta.title}}</h6>
    </template>
    <template v-if="type=='p'">
      <p>{{uiconfig.meta.value||uiconfig.meta.title}}</p>
    </template>
    <template v-if="type=='span'">
      {{uiconfig.meta.value||uiconfig.meta.title}}
    </template>
  </div>
</template>

<script lang="ts">
import Text from '@/components/ui/js/Text'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Bootstrap_Text',
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const text = new Text(props, context, useStore())
    const setup = text.setup()
    const myCss = computed(() => {
      const arr: any = Object.values(text.getUICss())
      return arr
    })
    const myStyleText = computed(() => {
      const style = text.appendImportant(text.getUIStyle())
      return style
    })
    return {
      myCss,
      myStyleText,
      ...setup
    }
  }
}

</script>
