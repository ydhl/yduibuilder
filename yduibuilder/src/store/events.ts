import { UIDefine } from '@/components/ui/define'
import { UIBase } from '@/store/model'

const eventMap = {
  page: {
    onLoad: {},
    onBeforeUnload: {},
    onUnload: {},
    onResize: {},
    onPullDown: {},
    onReachBottom: {}
  },
  data: {
    onChange: {
      args: [
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'oldValue', uuid: 'oldValue' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onInput: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  },
  keyboard: {
    onKeyUp: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onKeyDown: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onKeyPress: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  },
  mouse: {
    onClick: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onDblClick: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseDown: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseUp: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseOver: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseOut: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseMove: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseEnter: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseLeave: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  },
  upload: {
    onFileChange: {
      args: [
        {
          type: 'array',
          name: 'files',
          uuid: 'files',
          item: {
            type: 'file',
            uuid: 'file',
            props: [
              {
                uuid: 'fileSize',
                type: 'number',
                name: 'size',
                readonly: true
              },
              {
                uuid: 'fileType',
                type: 'string',
                name: 'type',
                readonly: true
              },
              {
                uuid: 'fileName',
                type: 'string',
                name: 'name',
                readonly: true
              },
              {
                uuid: 'fileLastModified',
                type: 'string',
                name: 'lastModified',
                readonly: true
              }
            ]
          }
        }
      ]
    },
    onBeforeUpload: {
      args: [
        { type: 'number', name: 'index', uuid: 'fileIndex' },
        {
          type: 'file',
          name: 'file',
          uuid: 'file',
          props: [
            {
              uuid: 'fileSize',
              type: 'number',
              name: 'size',
              readonly: true
            },
            {
              uuid: 'fileType',
              type: 'string',
              name: 'type',
              readonly: true
            },
            {
              uuid: 'fileName',
              type: 'string',
              name: 'name',
              readonly: true
            },
            {
              uuid: 'fileLastModified',
              type: 'string',
              name: 'lastModified',
              readonly: true
            }
          ]
        }
      ]
    },
    onUploadProgress: {
      args: [
        { type: 'number', name: 'index', uuid: 'fileIndex' },
        {
          type: 'file',
          name: 'file',
          uuid: 'file',
          props: [
            {
              uuid: 'fileSize',
              type: 'number',
              name: 'size',
              readonly: true
            },
            {
              uuid: 'fileType',
              type: 'string',
              name: 'type',
              readonly: true
            },
            {
              uuid: 'fileName',
              type: 'string',
              name: 'name',
              readonly: true
            },
            {
              uuid: 'fileLastModified',
              type: 'string',
              name: 'lastModified',
              readonly: true
            }
          ]
        },
        { type: 'number', name: 'progress', uuid: 'progress' }
      ]
    },
    onFileUploaded: {
      args: [
        { type: 'number', name: 'index', uuid: 'fileIndex' },
        {
          type: 'file',
          name: 'file',
          uuid: 'file',
          props: [
            {
              uuid: 'fileSize',
              type: 'number',
              name: 'size',
              readonly: true
            },
            {
              uuid: 'fileType',
              type: 'string',
              name: 'type',
              readonly: true
            },
            {
              uuid: 'fileName',
              type: 'string',
              name: 'name',
              readonly: true
            },
            {
              uuid: 'fileLastModified',
              type: 'string',
              name: 'lastModified',
              readonly: true
            }
          ]
        },
        { type: 'any', name: 'rst', uuid: 'rst' }
      ]
    },
    onUploadComplete: {}
  },
  other: {
    onScroll: {},
    onBlur: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onFocus: {
      args: [
        { type: 'any', name: 'event', uuid: 'event' },
        { type: 'any', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  }
}
export function hasEvent (ui: UIDefine, event: string, uiConfig: UIBase | null = null) {
  const events = getEvents(ui, uiConfig)
  for (const key in events) {
    if (events[key]?.[event]) return true
  }
  return false
}
export function getEvents (ui: UIDefine, uiConfig: UIBase | null = null): Object {
  const map: any = JSON.parse(JSON.stringify(eventMap))
  const onFileChange = map.upload.onFileChange

  if (['Input', 'Textarea'].indexOf(ui.type) !== -1) {
    map.upload = {}
  } else if (ui.type === 'File') {
    map.data = {}
    map.keyboard = {}
  } else {
    map.keyboard = {}
    map.upload = {}
    if (!ui.isValuable) map.data = {}
  }

  if (uiConfig && uiConfig.type === 'File' && !uiConfig.meta?.custom?.isAutoUpload) {
    map.upload = {
      onFileChange
    }
  }
  return map
}
export default eventMap
