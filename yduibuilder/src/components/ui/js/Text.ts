
import { computed, watch } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

declare const ports: any

export default class Text extends UIBase {
  public getUIStyle (uiconfig: any = undefined) {
    const style = super.getUIStyle(uiconfig)
    return style
  }

  setup () {
    const props = this.props
    const setup = super.setup()
    const { inlineEditItemId, updateInlineItemValue } = setup
    const type = computed(() => (props.uiconfig.meta.custom?.type || 'span').toLowerCase())

    watch(updateInlineItemValue, (newV) => {
      if (newV) {
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            props: { value: document.getElementById(props.uiconfig.meta.id)?.innerText }
          }
        })
        ports.parent({ type: 'update', data: { updateInlineItemValue: false } })
      }
    })

    /**
     * 退出内部编辑时，更新value
     */
    watch(inlineEditItemId, (newV, oldV) => {
      if (newV !== oldV && oldV === props.uiconfig.meta.id) {
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            props: { value: document.getElementById(props.uiconfig.meta.id)?.innerText }
          }
        })
      }
    })
    return {
      ...setup,
      type,
      updateInlineItemValue,
      inlineEditItemId
    }
  }
}
