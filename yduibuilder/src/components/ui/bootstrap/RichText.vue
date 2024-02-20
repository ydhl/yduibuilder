<template>
  <div v-if="!showEditor" :draggable='!inlineEditItemId' @dblclick.stop="openEditor"
       :class="[dragableCss, myCss]" :style="uiStyle" :id="myId" :data-type="uiconfig.type"
       :data-pageid="pageid" v-html="uiconfig.meta.value|| t('style.richText.placeholder')">
  </div>
  <div style="border: 1px solid #ccc" v-if="showEditor">
    <Toolbar style="border-bottom: 1px solid #ccc" :editor="editorRef" :defaultConfig="toolbarConfig" @click.stop.prevent :mode="mode"/>
    <Editor style="height: 500px; overflow-y: hidden;" class="editor-content-view" v-model="valueHtml" :defaultConfig="editorConfig" :mode="mode" @click.stop.prevent @onChange="editContentChange" @onCreated="handleCreated"/>
  </div>
</template>

<script lang="ts">
import '@wangeditor/editor/dist/css/style.css' // 引入 css
import RichText from '@/components/ui/js/RichText'
import { Editor, Toolbar } from '@wangeditor/editor-for-vue'
import { onBeforeUnmount, computed } from 'vue'

export default {
  name: 'Bootstrap_RichText',
  components: { Editor, Toolbar },
  props: {
    uiVersion: String,
    uiconfig: Object,
    isLock: Boolean,
    isReadonly: Boolean,
    pageid: String,
    dragableCss: Object
  },
  setup (props: any, context: any) {
    const richText = new RichText(props, context)
    const setup = richText.setup()

    const { beforeUnmount } = setup

    const myCss = computed(() => {
      const arr: any = Object.values(richText.getUICss())
      arr.push('editor-content-view')
      return arr
    })

    // 组件销毁时，也及时销毁编辑器
    onBeforeUnmount(beforeUnmount)

    return {
      mode: 'simple', // default 'simple'
      ...setup,
      myCss
    }
  }
}

</script>
