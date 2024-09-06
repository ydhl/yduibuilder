import { useI18n } from 'vue-i18n'
import { UIActionType } from '@/store/model'

export default function () {
  const { t } = useI18n()
  const getActions = (...types: Array<UIActionType>) => {
    const config = {
      output: { name: t('action.output'), value: 'output', desc: t('action.outputDataDesc') },
      redirect: { name: t('action.redirect'), value: 'redirect', desc: t('action.redirectDesc') },
      popup: { name: t('action.popup'), value: 'popup', desc: t('action.popupDesc') },
      webapi: { name: t('action.webapi'), value: 'webapi', desc: t('action.webapiDesc') },
      emit: { name: t('action.emit'), value: 'emit', desc: t('action.emitDesc') },
      mutation: { name: t('action.mutation'), value: 'mutation', desc: t('action.mutationDesc') },
      closepopup: { name: t('action.closepopup'), value: 'closepopup', desc: t('action.closepopupDesc') },
      interval: { name: t('action.interval'), value: 'interval', desc: t('action.intervalDesc') },
      validate: { name: t('action.validate'), value: 'validate', desc: t('action.validateDesc') },
      break: { name: t('action.break'), value: 'break', desc: t('action.breakDesc') }
    }
    if (!types || types.length === 0) return Object.values(config)
    const rst: any = []
    for (const type of types) {
      rst.push(config[type])
    }
    return rst
  }
  return {
    getActions
  }
}
