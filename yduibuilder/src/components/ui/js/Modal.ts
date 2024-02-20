
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Card extends UIBase {
  setup () {
    const props = this.props
    const superSetup = super.setup()
    const myItems = computed(() => {
      const items:any = { head: [], main: [], foot: [] }
      if (!props.uiconfig.items) return items
      for (const item of props.uiconfig.items) {
        if (item.placeInParent === 'head') {
          items.head.push(item)
        } else if (item.placeInParent === 'foot') {
          items.foot.push(item)
        } else {
          items.main.push(item)
        }
      }
      return items
    })
    return {
      ...superSetup,
      myItems
    }
  }
}
