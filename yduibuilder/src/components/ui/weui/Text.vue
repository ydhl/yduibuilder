<template>
  <template v-if="type=='h1'">
    <h1 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
            :data-pageid="pageid">
      {{uiconfig.meta.value||uiconfig.meta.title}}
    </h1>
  </template>
  <template v-if="type=='h2'">
    <h2 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
              :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</h2>
  </template>
  <template v-if="type=='h3'">
    <h3 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</h3>
  </template>
  <template v-if="type=='h4'">
    <h4 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</h4>
  </template>
  <template v-if="type=='h5'">
    <h5 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</h5>
  </template>
  <template v-if="type=='h6'">
    <h6 :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
        :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
        :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
        :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</h6>
  </template>
  <template v-if="type=='p'">
    <p :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
       :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
       :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</p>
  </template>
  <template v-if="type=='span'">
    <div :draggable='draggable' @dblclick="inlineEditItemId=uiconfig.meta.id"
          :contenteditable="inlineEditItemId==uiconfig.meta.id" @keyup.enter="inlineEditItemId=''"
          :class="[dragableCss, myCss]" :style="myStyleText" :id="myId" :data-type="uiconfig.type"
          :data-pageid="pageid">{{uiconfig.meta.value||uiconfig.meta.title}}</div>
  </template>
</template>

<script lang="ts">
import Text from '@/components/ui/js/Text'
import { computed } from 'vue'
import { useStore } from 'vuex'

export default {
  name: 'Weui_Text',
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
      const arr: any = []
      return arr.length > 0 ? style + ';' + arr.join(';') : style
    })
    return {
      myCss,
      myStyleText,
      ...setup
    }
  }
}

</script>
