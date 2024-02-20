import { computed, watch } from 'vue'
import $ from 'jquery'
import UIBase from '@/components/ui/js/UIBase'
declare const ports: any

export default class Button extends UIBase {
  setup () {
    const props = this.props
    const superSetup = super.setup()
    const { inlineEditItemId, parentUi, hasItems } = superSetup

    const parentIsButtonGroup = computed(() => {
      return parentUi.value.type.toLowerCase() === 'buttongroup'
    })
    const parentIsNavbar = computed(() => {
      return parentUi.value.type.toLowerCase() === 'navbar' || parentUi.value.type.toLowerCase() === 'nav'
    })
    /**
     * 退出内部编辑时，更新value
     */
    watch(inlineEditItemId, (newV, oldV) => {
      if (newV !== oldV && oldV === props.uiconfig.meta.id) {
        const el = $(`#${props.uiconfig.meta.id} .inner-btn`).length ? $(`#${props.uiconfig.meta.id} .inner-btn`) : $(`#${props.uiconfig.meta.id}`)
        ports.parent({
          type: 'updateItemMeta',
          data: {
            itemid: props.uiconfig.meta.id,
            pageId: props.pageid,
            props: { title: el.text() }
          }
        })
      }
    })

    return {
      ...superSetup,
      inlineEditItemId,
      parentIsButtonGroup,
      parentIsNavbar,
      parentUi,
      hasItems
    }
  }
}
