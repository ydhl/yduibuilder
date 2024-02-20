
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class List extends UIBase {
  setup () {
    const props = this.props
    const values = computed(() => {
      if (!props.uiconfig.meta.values || props.uiconfig.meta.values.length === 0) {
        return [{ text: 'Sample 1', value: '1' }, { text: 'Sample 2', value: '2' }]
      }
      return props.uiconfig.meta.values
    })
    return {
      values,
      ...super.setup()
    }
  }
}
