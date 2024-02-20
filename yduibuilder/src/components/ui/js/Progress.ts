
import { computed } from 'vue'
import UIBase from '@/components/ui/js/UIBase'

export default class Progress extends UIBase {
  setup () {
    const props = this.props
    const defaultValue = computed(() => {
      return props.uiconfig.meta.value || 50
    })
    return {
      defaultValue,
      ...super.setup()
    }
  }
}
