
import UIBase from '@/components/ui/js/UIBase'
import { ref, shallowRef, watch } from 'vue'

declare const ports: any
export default class RichText extends UIBase {
  setup () {
    const props = this.props
    const setup = super.setup()
    const { inlineEditItemId, updateInlineItemValue } = setup
    const showEditor = ref(inlineEditItemId.value === props.uiconfig.meta.id)
    // 编辑器实例，必须用 shallowRef
    const editorRef = shallowRef()
    // 内容 HTML
    const valueHtml = ref(props.uiconfig.meta.value)
    const toolbarConfig = {
      toolbarKeys: ['quitMenu', '|', 'blockquote', 'headerSelect', '|', 'bold', 'underline', 'italic', 'through', 'color', 'bgColor', 'clearStyle',
        '|', 'bulletedList', 'numberedList', 'justifyLeft', 'justifyRight', 'justifyCenter', '|',
        'insertLink', 'imageMenu', 'insertTable', '|', 'undo', 'redo'
      ]
    }
    const editorConfig = { placeholder: '请输入内容...' }
    /**
     * 退出内部编辑时，更新value
     */
    watch(inlineEditItemId, (newV, oldV) => {
      if (newV !== oldV && oldV === props.uiconfig.meta.id) {
        showEditor.value = false
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            props: { value: valueHtml.value }
          }
        })
      }
    })

    watch(updateInlineItemValue, (newV) => {
      if (newV) {
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            props: { value: valueHtml.value }
          }
        })
        ports.parent({ type: 'update', data: { updateInlineItemValue: false } })
      }
    })

    const openEditor = () => {
      showEditor.value = true
      inlineEditItemId.value = props.uiconfig.meta.id
    }

    const beforeUnmount = () => {
      const editor = editorRef.value
      if (editor == null) return
      editor.destroy()
    }

    const handleCreated = (editor) => {
      editorRef.value = editor // 记录 editor 实例，重要！
      editor.fullScreen()
    }

    const editContentChange = (editor) => {
      ports.parent({
        type: 'update',
        data: {
          saved: 0
        }
      })
    }
    return {
      ...setup,
      showEditor,
      editorConfig,
      editorRef,
      toolbarConfig,
      valueHtml,
      openEditor,
      beforeUnmount,
      handleCreated,
      editContentChange
    }
  }
}
