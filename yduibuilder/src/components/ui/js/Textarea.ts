
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'
export default class Textarea extends UIBase {
  setup () {
    const props = this.props
    const defaultValue = computed(() => {
      if (!props.uiconfig.meta.value) return ''
      return props.uiconfig.meta.value
    })
    return {
      defaultValue,
      ...super.setup()
    }
  }
}
