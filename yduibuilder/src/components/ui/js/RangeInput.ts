
import UIBase from '@/components/ui/js/UIBase'
export default class RangeInput extends UIBase {
  public getUICss (uiconfig: any = undefined) {
    const css = super.getUICss(uiconfig)
    if (!uiconfig) uiconfig = this.props.uiconfig
    const range: any = []
    range.push('form-control-range')
    if (uiconfig.meta?.css?.formSizing && uiconfig.meta?.css?.formSizing !== 'normal') {
      range.push('form-control-range-' + uiconfig.meta.css.formSizing)
    }
    const color = uiconfig.meta?.style?.color
    if (uiconfig.meta?.css?.foregroundTheme && uiconfig.meta?.css?.foregroundTheme !== 'default' && !color) {
      range.push('range-' + uiconfig.meta.css.foregroundTheme)
    }
    css.range = range.join(' ')
    return css
  }

  public getUIStyle (uiconfig: any = undefined) {
    const style = super.getUIStyle(uiconfig)

    if (!uiconfig) uiconfig = this.props.uiconfig

    const background:any = []
    const backgroundSize:any = ['50%', '100%']
    const color = uiconfig.meta?.style?.color
    if (color) {
      style.border = `1px solid ${color}`
      background.push(`-webkit-linear-gradient(top, ${color}, ${color})`)
    }
    if (background.length > 0) style['background-image'] = background.join(',')

    const minValue = uiconfig.meta.custom?.min === undefined ? 1 : uiconfig.meta.custom?.min
    const defaultValue = uiconfig.meta.value === undefined ? 50 : uiconfig.meta.value
    const maxValue = uiconfig.meta.custom?.max === undefined ? 100 : uiconfig.meta.custom?.max
    backgroundSize[0] = defaultValue === 0 ? '0%' : ((defaultValue - minValue) / (maxValue - minValue) * 100) + '%'
    style['background-size'] = backgroundSize.join(' ')
    // console.log(style)
    return style
  }

  setup () {
    return super.setup()
  }
}
