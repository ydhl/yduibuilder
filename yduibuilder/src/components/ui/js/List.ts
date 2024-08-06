import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class List extends UIBase {
  setup () {
    const props = this.props
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ name: 'Item 1', value: 'item 1' }, { name: 'Item 2', value: 'item 2' }]
      }
      return props.uiconfig.meta.values
    })
    return {
      values,
      ...super.setup()
    }
  }
}
