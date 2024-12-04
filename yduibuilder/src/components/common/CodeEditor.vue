<template>
  <div class="p-3" ref="editorContainer">
      <div class="text-danger p-1 m-1 fs-7" v-if="tip">{{tip}}</div>
      <div class="d-flex align-items-stretch">
        <div ref="editor" class="flex-grow-1" :style="editStyle"></div>
        <div v-if="!hideVariable" style="width: 300px;border-left:1px solid #dcdcdb" class="flex-shrink-0 d-flex">
          <div class="vertical-tab">
            <a :class="{'vertical-tab-item': true, 'active': scope=='local'}" @click="scope='local'" href="javascript:void(0)">{{t('variable.localScope')}}</a>
            <a :class="{'vertical-tab-item': true, 'active': scope=='page'}" @click="scope='page'" href="javascript:void(0)">{{t('variable.pageScope')}}</a>
            <a :class="{'vertical-tab-item': true, 'active': scope=='global'}" @click="scope='global'" href="javascript:void(0)">{{t('variable.globalScope')}}</a>
          </div>
          <div v-if="scope=='local'" class="flex-grow-1">
            <template v-for="(variable, index) in variables" :key="index">
              <DataSimple :model="variable" path="" :index="index" :intent="0" :root-uuid="variable.id"></DataSimple>
            </template>
            <div v-if="!variables || variables.length ==0" class="d-flex align-items-center text-muted h-100 justify-content-center">{{t('common.empty')}}</div>
          </div>
          <div v-if="scope=='page'" class="flex-grow-1">
            <template v-for="(variable, index) in pageVariables" :key="index">
              <DataSimple :model="variable" path="" :index="index" :intent="0" :root-uuid="variable.id"></DataSimple>
            </template>
            <div v-if="!pageVariables || pageVariables.length ==0" class="d-flex align-items-center text-muted h-100 justify-content-center">{{t('common.empty')}}</div>
          </div>
          <div v-if="scope=='global'" class="flex-grow-1">
            <template v-for="(variable, index) in globalVariables" :key="index">
              <DataSimple :model="variable" path="" :index="index" :intent="0" :root-uuid="variable.id"></DataSimple>
            </template>
            <div v-if="!globalVariables || globalVariables.length ==0" class="d-flex align-items-center text-muted h-100 justify-content-center">{{t('common.empty')}}</div>
          </div>
        </div>
      </div>
    </div>
  <DataInfo v-model="detailDlgVisible" :data="leftData"></DataInfo>
</template>

<script lang="ts">
import { useI18n } from 'vue-i18n'
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import * as monaco from 'monaco-editor'
import ydhl from '@/lib/ydhl'
import DataSimple from '@/components/common/DataSimple.vue'
import { useStore } from 'vuex'
import DataInfo from '@/components/common/DataInfo.vue'

// 代码编辑对话框
export default {
  name: 'CodeEditor',
  components: { DataInfo, DataSimple },
  props: {
    code: String,
    schema: Object, // variables json数据的schema
    leftData: Object, // 左值
    leftValuePath: String, // 左值路径
    surroundCode: String, // 操作符或前后代码，@表示编辑器中的代码嵌入的位置
    editStyle: String,
    title: String,
    hideVariable: {
      default: false,
      type: Boolean
    },
    tip: String,
    variables: {
      default: () => [],
      type: Array
    },
    readOnly: {
      default: false,
      type: Boolean
    },
    language: {
      default: 'json',
      type: String
    }
  },
  emits: ['update:modelValue', 'update'],
  setup (props: any, context: any) {
    const { t } = useI18n()
    const editor = ref()
    let editTimer
    const editorContainer = ref()
    const detailDlgVisible = ref(false)
    const myCode = computed(() => props.code)
    const store = useStore()
    const currPage = computed(() => store.state.design.page)
    const scope = ref('local')
    const pageVariables = ref([])
    const globalVariables = ref([])
    const suggestions: any = []
    let editorInstance
    let completionItemProvider
    let codeLensProvider
    let hoverProvider
    let myUri
    const leftSurroundCode = computed(() => {
      if (!props.surroundCode) return ''
      const info: any = []
      if (props.leftValuePath) {
        info.push(props.leftValuePath ? props.leftValuePath + '.' + props.leftData?.name : props.leftData?.name)
      } else {
        info.push(props.leftData?.name)
      }
      const operators = props.surroundCode?.split('@')
      if (operators?.length > 0) {
        info.push(operators[0])
      }
      return info.join('')
    })
    onUnmounted(() => {
      if (editorInstance) {
        editorInstance.dispose()
        codeLensProvider.dispose()
        completionItemProvider.dispose()
        hoverProvider.dispose()
      }
      editorInstance = null
    })
    onMounted(() => {
      if (props.language === 'json' && props.schema) {
        // console.log(props.schema)
        // json格式配置
        monaco.languages.json.jsonDefaults.setDiagnosticsOptions({
          validate: true,
          allowComments: false,
          enableSchemaRequest: false,
          schemas: [
            {
              uri: ydhl.api,
              fileMatch: ['*'],
              schema: props.schema
            }
          ]
        })
      } else if (props.language === 'jsAndJson') {
        monaco.languages.register({ id: 'jsAndJson' })
        monaco.languages.setMonarchTokensProvider('jsonAndJs', {
          tokenizer: {
            root: [
              // 支持 JSON 语法
              [/[^\\]["{}()]+/, 'keyword'],
              [/[,:]/, 'delimiter'],
              [/"([^"\\]|\\.)*"/, 'string'],
              [/[0-9]+/, 'number'],
              [/true|false/, 'boolean'],
              [/null/, 'null'],

              // 支持 JavaScript 语法
              [/[a-zA-Z_$][a-zA-Z0-9_$]*/, 'identifier'],
              [/[=+\-*/!%&<>^|~:]+/, 'operator'],
              [/[();,.]+/, 'delimiter'],
              [/\/\*/, '/*', '@comment'],
              [/\/\/.*$/, 'comment'],
              [/\\./, 'escape.invalid'],
              [/"/, 'string', '@string'],
              [/'/, 'string', '@singleQuote'],
              [/\{/, 'delimiter', '@curly'],
              [/\[/, 'delimiter', '@square'],
              [/\(/, 'delimiter', '@parentheses'],
              [/[0-9]+(?:\.[0-9]+)?(?:e[+-]?[0-9]+)?/, 'number']
            ],

            comment: [
              [/[^/*]+/, 'comment'],
              [/\*/, 'comment', '@push'],
              [/[/*]/, 'comment']
            ],

            string: [
              [/[^\\"]+/, 'string'],
              [/@escapes/, 'string.escape'],
              [/\\./, 'string.escape.invalid'],
              [/"/, 'string', '@pop']
            ],

            singleQuote: [
              [/[^\\']+/, 'string'],
              [/@escapes/, 'string.escape'],
              [/\\./, 'string.escape.invalid'],
              [/'/, 'string', '@pop']
            ],

            curly: [
              [/[^{}]+/, 'delimiter'],
              [/[{}]/, 'delimiter'],
              [/}/, 'delimiter', '@pop']
            ],

            square: [
              [/[^\]]+/, 'delimiter'],
              [/\]/, 'delimiter', '@pop']
            ],

            parentheses: [
              [/[()]/, 'delimiter'],
              [/[()]/, 'delimiter', '@pop']
            ]
          },
          escapes: /\\(?:[btnfr"'\\/]|u[0-9a-fA-F]{4})/
        })
      }
      if (!editorInstance) {
        editorInstance = monaco.editor.create(editor.value as HTMLElement, {
          roundedSelection: true,
          scrollBeyondLastLine: false,
          minimap: {
            enabled: false
          },
          readOnly: props.readOnly,
          language: props.language
        })
        myUri = editorInstance.getModel().uri.toString()
      }
      codeLensProvider = monaco.languages.registerCodeLensProvider(props.language, {
        provideCodeLenses (model, token) {
          if (!model.uri.toString().includes(myUri) || !leftSurroundCode.value) {
            return {
              lenses: [],
              dispose: () => {}
            }
          }
          const id = props.leftData
            ? editorInstance.addCommand(0, function () {
              detailDlgVisible.value = true
            }, '')
            : ''
          const lenses = [
            {
              range: {
                startLineNumber: 1,
                startColumn: 1,
                endLineNumber: 2,
                endColumn: 1
              },
              command: {
                id,
                title: leftSurroundCode.value
              }
            }
          ]
          const operators = props.surroundCode?.split('@')
          if (operators?.length > 1 && operators[1].trim()) {
            const lineCount = model.getLineCount()
            lenses.push({
              range: {
                startLineNumber: lineCount,
                startColumn: 1,
                endLineNumber: lineCount,
                endColumn: 1
              },
              command: {
                id: '',
                title: operators[1]
              }
            })
          }
          return {
            lenses,
            dispose: () => {}
          }
        }
      })
      // 加载自动补全配置, 把当前变量的名称加入到自动补全中
      completionItemProvider = monaco.languages.registerCompletionItemProvider(props.language, {
        triggerCharacters: [' ', '.', '"'],
        provideCompletionItems: (model, position, context, token) => {
          if (!model.uri.toString().includes(myUri) || props.language === 'json') { // json 用schema不用代码提示
            return { suggestions: [] }
          }
          return { suggestions: suggestions }
        }
      })
      // 悬浮提示
      hoverProvider = monaco.languages.registerHoverProvider(props.language, {
        provideHover (model, position, token) {
          if (!model.uri.toString().includes(myUri)) {
            return null
          }
          const word = model.getWordAtPosition(position)
          if (!word) return null
          let str = word.word.replace(/\[.*\]/, '[.*]')
          str = escapeRegExp(str)
          const findSuggestions = suggestions.filter((suggestion: any) => {
            return suggestion.label.match(new RegExp(`^${str}$`))
          })
          if (findSuggestions.length === 0) return null
          const contents: any = []
          for (const findSuggestion of findSuggestions) {
            contents.push({ value: findSuggestion.detail + (findSuggestion.documentation ? ' ' + findSuggestion.documentation : '') })
          }
          return {
            range: new monaco.Range(position.lineNumber, word.startColumn, position.lineNumber, word.endColumn),
            contents
          }
        }
      })

      editorInstance.onDidChangeModelContent(() => {
        const operators = props.surroundCode?.split('@')
        if (operators?.length > 1 && operators[1].trim()) {
          const model = editorInstance.getModel()
          const lineCount = model.getLineCount()
          const lastLineContent = model.getLineContent(lineCount)

          // 有括号时，如果最后一行不是空的，则添加一个新行
          if (lastLineContent.trim() !== '' || lineCount === 1) {
            model.pushEditOperations([], [
              {
                range: new monaco.Range(lineCount, lastLineContent.length + 1, lineCount, lastLineContent.length + 1),
                text: '\n'
              }
            ], () => null)
          }
          // editorInstance.setValue(editorInstance.getValue())
        }
        clearTimeout(editTimer)
        // 延迟执行，避免被编辑器默认的markerts标记覆盖掉
        editTimer = setTimeout(() => filterMarkers(editorInstance), 800)
      })
      editorInstance.setValue(myCode.value || '\n')
      editorInstance.getAction('editor.action.formatDocument').run()
      editorInstance.setValue(editorInstance.getValue())
      suggestions.push(...ydhl.getVariableSuggestions(props.variables, '', false))
      loadVariables()
    })
    const loadVariables = () => {
      ydhl.get('api/data.json', { page_uuid: currPage.value.meta.id }, (rst) => {
        pageVariables.value = rst.data?.page || []
        globalVariables.value = rst.data?.global || []

        suggestions.push(...ydhl.getVariableSuggestions(pageVariables.value, 'page.'))
        suggestions.push(...ydhl.getVariableSuggestions(globalVariables.value, 'global.'))
      })
    }
    const escapeRegExp = (string) => {
      return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
    }
    watch(() => props.editStyle, () => {
      if (editorInstance) editorInstance.layout()
    })
    const filterMarkers = (editorInstance) => {
      if (!editorInstance) return
      if (props.language === 'json') {
        const model = editorInstance.getModel()
        const markers = monaco.editor.getModelMarkers({ owner: props.language })
        // json或者其他语言中提示关键字不存在时，做检查，在variable中的关键字编辑器不能识别，但他们时存在但过滤掉这些错误
        const filteredMarkers = markers.filter(marker => {
          if (!marker.message.includes('Value expected')) return true // json中非Value expected的提示错误都展示
          let word = model.getValueInRange(marker)
          if (!word) return true

          // 关键字没有找到都则提示出来
          word = word.replace(/\[.*\]/, '[.*]')
          word = escapeRegExp(word)
          return suggestions.filter((suggestion: any) => {
            return suggestion.label.match(new RegExp(`^${word}$`))
          }).length === 0
        })
        monaco.editor.setModelMarkers(model, props.language, filteredMarkers)
      } else {
        const code = editorInstance.getValue()
        const lines = code.split('\n')
        const markers: any = []
        lines.forEach((line: string, index: number) => {
          const lineNumber = index + 1
          const semicolonIndex = line.indexOf(';')
          if (semicolonIndex !== -1) {
            markers.push({
              startLineNumber: lineNumber,
              startColumn: semicolonIndex + 1,
              endLineNumber: lineNumber,
              endColumn: semicolonIndex + 2,
              severity: monaco.MarkerSeverity.Error,
              message: 'Cannot use semicolons'
            })
          }
        })
        monaco.editor.setModelMarkers(editorInstance.getModel(), 'custom-linter', markers)
      }
    }
    const getEditorInstance = () => {
      return editorInstance
    }
    return {
      t,
      myCode,
      editor,
      editorContainer,
      pageVariables,
      globalVariables,
      detailDlgVisible,
      suggestions,
      scope,
      getEditorInstance
    }
  }
}
</script>
