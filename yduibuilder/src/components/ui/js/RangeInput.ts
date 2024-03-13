
import UIBase from '@/components/ui/js/UIBase'
export default class RangeInput extends UIBase {
  private getMyCss (uiconfig, css) {
    const range: any = []
    if (uiconfig.meta?.css?.formSizing && uiconfig.meta?.css?.formSizing !== 'normal') {
      range.push('form-control-range-' + uiconfig.meta.css.formSizing)
    }
    const color = uiconfig.meta?.style?.color
    if (uiconfig.meta?.css?.foregroundTheme && uiconfig.meta?.css?.foregroundTheme !== 'default' && !color) {
      range.push('range-' + uiconfig.meta.css.foregroundTheme)
    }
    if (range.length) css.my = range.join(' ')
    return css
  }

  // 重载
  public getUICss (uiconfig: any = undefined) {
    let css = super.getUICss(uiconfig)
    const store = this.store
    if (!uiconfig) {
      uiconfig = this.props.uiconfig
    }
    const previewItem = this.hasActiveState() ? store.state.page.previewStyleItem : null
    css.range = 'form-control-range'
    css = this.getMyCss(uiconfig, css)
    if (previewItem) {
      css = this.getMyCss(previewItem, css)
    }
    return css
  }

  private getMyStyle (uiconfig, style) {
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

  // 重载
  public getUIStyle (uiconfig: any = undefined) {
    let style = super.getUIStyle(uiconfig)
    const store = this.store

    if (!uiconfig) {
      uiconfig = this.props.uiconfig
    }
    style = this.getMyStyle(uiconfig, style)
    const previewItem = this.hasActiveState() ? store.state.page.previewStyleItem : null
    if (previewItem) {
      style = this.getMyStyle(previewItem, style)
    }
    return style
  }

  setup () {
    return super.setup()
  }
}
