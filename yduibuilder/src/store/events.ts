import { UIDefine } from '@/components/ui/define'

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
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'string', name: 'oldValue', uuid: 'oldValue' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onInput: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  },
  keyboard: {
    onKeyUp: {
      args: [
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onKeyDown: {
      args: [
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onKeyPress: {
      args: [
        { type: 'string', name: 'keyCode', uuid: 'keyCode' },
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    }
  },
  mouse: {
    onClick: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onDblClick: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseDown: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseUp: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseOver: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseOut: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseMove: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseEnter: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
        { type: 'any', name: 'boundData', uuid: 'boundData' }
      ]
    },
    onMouseLeave: {
      args: [
        { type: 'string', name: 'value', uuid: 'value' },
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
    onBlur: {},
    onFocus: {}
  }
}
export function hasEvent (ui: UIDefine, event: string) {
  const events = getEvents(ui)
  for (const key in events) {
    if (events[key]?.[event] !== -1) return true
  }
  return false
}
export function getEvents (ui: UIDefine): Object {
  const map: any = JSON.parse(JSON.stringify(eventMap))

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
  return map
}
export default eventMap
