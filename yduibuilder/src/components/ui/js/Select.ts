
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Select extends UIBase {
  setup () {
    const props = this.props
    const isMultiple = computed(() => {
      const item = props.uiconfig
      if (!item) return false
      return item.meta.custom?.multiple
    })
    const size = computed(() => {
      const item = props.uiconfig
      if (!item) return ''
      return item.meta.custom?.size
    })
    return {
      isMultiple,
      size,
      ...super.setup()
    }
  }
}
