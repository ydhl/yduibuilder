
import { computed, watch } from 'vue'
import $ from 'jquery'
import UIBase from '@/components/ui/js/UIBase'
declare const ports: any
export default class Dropdown extends UIBase {
  setup () {
    const props = this.props
    // const context = this.context
    const superSetup = super.setup()
    const { parentUi, inlineEditItemId } = superSetup

    const parentIsNavbar = computed(() => {
      return parentUi.value.type.toLowerCase() === 'navbar' || parentUi.value.type.toLowerCase() === 'nav'
    })

    const parentIsButtonGroup = computed(() => {
      return parentUi.value.type.toLowerCase() === 'buttongroup'
    })

    /**
     * 退出内部编辑时，更新value
     */
    watch(inlineEditItemId, (newV, oldV) => {
      if (newV !== oldV && oldV === props.uiconfig.meta.id) {
        const el = $(`#${props.uiconfig.meta.id}MenuLink`)
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
    const uiStyle = computed(() => {
      const style = this.getUIStyle()
      return this.appendImportant(style)
    })

    const uiCss = computed(() => {
      const css = this.getUICss()
      delete css.dropdownSizing
      return Object.values(css).join(' ')
    })
    return {
      ...superSetup,
      uiStyle,
      uiCss,
      parentIsNavbar,
      parentIsButtonGroup
    }
  }
}
