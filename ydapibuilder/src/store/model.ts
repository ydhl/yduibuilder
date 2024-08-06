export declare type DataType = 'string' | 'integer' | 'number' | 'array' | 'boolean' | 'object' | 'null' | 'any' | 'file' | 'blob' // 数据结构那类型
export declare type APIStatus = 'develop' | 'test' | 'deprecated' | 'released'
export declare type RequestBodyType = 'none' | 'form-data' | 'x-www-form-urlencoded' | 'json' | 'xml' | 'raw' | 'binary' | 'GraphQL' | 'msgpack'
export declare type AuthType = 'NoAuth' | 'APIKey' | 'BearerToken' | 'BasicAuth' | 'DigestAuth' | 'OAuth1' | 'OAuth2' | 'HawkAuth' | 'AWSSignature' | 'NTLMAuth' | 'AkamaiEdgeGrid'
export declare type APIKeyPosition = 'header' | 'queryParams'
export declare type AuthAlgorithm = 'MD5' | 'MD5-sess' | 'MD5-256' | 'MD5-256-sess' | 'MD5-512-256' | 'MD5-512-256-sess'
export declare type SignatureMethod = 'HMAC-SHA1' | 'HMAC-SHA256' | 'HMAC-SHA512' | 'RSA-SHA1' | 'RSA-SHA256' | 'RSA-SHA512' | 'PLAINTEXT'
export declare type OAuth2GrantType = 'AuthCode' | 'AuthCodeWithPKCE' | 'Implicit' | 'PasswordCredentials' | 'ClientCredentials'
export declare type OAuth2ClientAuth = 'sendAsBasicAuthHeader' | 'sendClientCredentialsInBody'
export declare type VariableType = 'temp' | 'global' | 'environment'
export declare type DataStructPropAction = 'ReadOnly' | 'WriteOnly' | 'ReadWrite'
export declare type NumberFormat = 'float' | 'double' | 'float32' | 'float64'
export declare type IntegerFormat = 'int32' | 'int64' | 'long' | 'uint32' | 'uint64' | 'uint' | 'ulong' | 'sint32' | 'sint64' | 'fixed32' | 'fixed64' | 'sfixed32' | 'sfixed64' | 'bignum' | 'fixnum'
export const ResponseCode = {
  200: 'OK',
  400: 'Bad Request',
  401: 'Unauthorized',
  403: 'Forbidden',
  404: 'Not Found',
  410: 'Gone',
  500: 'Internal Server Error',
  501: 'Not Implemented',
  503: 'Service Unavailable'
}
export const ResponseFormat = {
  JSON: 'application/json',
  // ,
  // XML: 'application/xml',
  HTML: 'text/plain',
  Raw: 'text/plain',
  Binary: 'application/octet-stream'
  // MsgPack: 'text/msgpack'
}
export interface KeyValue{
    name: string,
    value?: string
}
export interface DataRAW{
    raw: string,
    doc?: string
}
/**
 * 数据结构体
 */
export interface DataStruct{
    uuid: string;
    /**
     * 是否是根结点
     */
    isRoot?: boolean;
    /**
     * 结点类型
     */
    type: DataType;
    /**
     * 结点名称，根节点, 数组中的ITEM节点为空
     */
    name?: string;
    /**
     * 标题，面向开发者的，name指的是变量名称
     */
    title?: string;
    /**
     * 说明
     */
    comment?:string;
    /**
     * 是否废弃
     */
    deprecated?: boolean;
    /**
     * 允许为空
     */
    nullable?: boolean;
    /**
     * 是否必须
     */
    required?: boolean;
    action?: DataStructPropAction;
}
export interface DataStructString extends DataStruct{
    min?: number;
    max?: number;
    pattern?: string;
    defaultValue?: string;
    /**
     * 枚举值及说明
     */
    enumValue?: Record<string, string>;
    mock?: string;
}
export interface DataStructNumber extends DataStruct{
    min?: number;
    max?: number;
    numberFormat?:NumberFormat;
    defaultValue?: number;
    enumValue?: Record<string, string>;
    mock?: string;
}
export type DataStructInteger = DataStructNumber
export interface DataStructBoolean extends DataStruct{
    defaultValue?: boolean;
    mock?: string;
}
export interface DataStructArray extends DataStruct{
    min?: number;
    max?: number;
    unique?: boolean;
    /**
     * 只有一个元素，表示array中的item都是指定的dataStruct类型
     */
    item?: DataStruct;
}
export interface DataStructObject extends DataStruct{
    min?: number;
    max?: number;
    /**
     * 有多个，表示object里面的组成内容
     */
    props?: Array<DataStruct>;
}
/**
 * 请求参数Query类型
 */
export interface DataStructParam extends DataStruct{
    sample?: string | Array<string> | Array<KeyValue>;
}
export interface AuthSetting{
    type: AuthType;
}
export interface APIKeySetting extends AuthSetting{
    position: APIKeyPosition;
    key?: string;
    value?: string
}
export interface BearerTokenSetting extends AuthSetting{
    token?: string;
}
export interface BasicAuthSetting extends AuthSetting{
    username?: string;
    password?: string;
}
export interface DigestAuthSetting extends AuthSetting{
    username?: string;
    password?: string;
    retryRequest?: boolean;
    realm?: string;
    nonce?: string;
    algorithm?: AuthAlgorithm;
    qop?: string;
    nonceCount?: string;
    clientConce?: string;
    opaque?: string;
}
export interface OAuth1Setting extends AuthSetting{
    position?: string;
    signatureMethod?: SignatureMethod;
    consumerKey?: boolean;
    consumerSecret?: string;
    accessToken?: string;
    tokenSecret?: string;
    callbackURL?: string;
    verifier?: string;
    timestamp?: string;
    nonce?: string;
    version?: string;
    realm?: string;
    includeBodyHash?:boolean;
    signatureIncludeEmptyParam?: boolean;
}
export interface OAuth2Setting extends AuthSetting{
    accessToken?: string;
    headerPrefix?: string;
    TokenName?: string;
    GrantType?: OAuth2GrantType;
    CallbackURL?: string;
    authorizeUsingBrowser?: boolean;
    authURL?: string;
    accessTokenURL?: string;
    clientId?: string;
    clientSecret?: string;
    scope?: string;
    state?: string;
    clientAuth?:OAuth2ClientAuth;
    resource?: Array<string>;
    audience?: Array<string>;
}
export interface HawkAuthSetting extends AuthSetting{
    hawkAuthId?: string;
    hawkAuthKey?: string;
    algorithm?: Algorithm;
    user?: string;
    nonce?: string;
    ext?: string;
    app?: string;
    dlg?: string;
    timestamp?: string;
    includePayloadHash?: boolean;
}
export interface AWSSignatureSetting extends AuthSetting{
    position?: string;
    accessKey?: string;
    secretKey?: string;
    awsRegion?: string;
    serviceName?: string;
    sessionToken?: string;
}
export interface NTLNAuthSetting extends AuthSetting{
    username?: string;
    password?: string;
    retryRequest?: boolean;
    domain?: string;
    workstation?: string;
}
export interface AkamaiEdgeGrid extends AuthSetting{
    accessToken?: string;
    clientToken?: string;
    clientSecret?: boolean;
    nonce?: string;
    timestamp?: string;
    baseURL?: string;
    headersToSign?: string;
}
export interface Operation{
    type: string;
}
export interface ExtractInfo{
    variableName: string;
    type: VariableType;
    jsonPath: string;
}
export interface DatabaseOperation{
    type: string;
    name: string;
    connectString: string;
    sql: string;
    extractToVariable: Array<ExtractInfo>
}
export interface CustomScriptOperation extends Operation{
    scripts: string;
}
export interface PublicScriptOperation extends Operation{
    scripts: string;
}
export interface AssertOperation extends Operation{
    timeout: number;
}
export interface ExtractOperation extends Operation{
    timeout: number;
}
export interface APIFolder{
    name: string;
    method?: string;
    isApi?: boolean;
    status?: string;
    children?: Array<APIFolder>;
}
export interface APIResponse{
    name: string;
    uuid?: string;
    code: number;
    contentType: string;
    body?: DataStruct | /* 文件id */ string | DataRAW;
}
/**
 * API 结构体
 */
export interface API{
    name: string;
    uuid?: string;
    /**
     * 请求方法
     */
    method: string;
    /**
     * 地址
     */
    path: string;
    /**
     * 目录
     */
    folder: {
        uuid?: string;
        name?: string;
    };
    /**
     * 状态
     */
    status: APIStatus;
    /**
     * 负责人
     */
    responsible: {
        uuid?: string;
        name?: string;
    };
    /**
     * 标签 {uuid: label}
     */
    label?: Record<string, string>;
    /**
     * 说明
     */
    comment?: string;
    /**
     * 选择的RequestBodyType
     */
    requestBodyType? : RequestBodyType;
    major?: number;
    minor?: number;
    revision?: number;
    version?: number;
    request: {
        path?: Array<DataStructParam>;
        param?: Array<DataStructParam>;
        body? : Record<RequestBodyType, Array<DataStructParam> | DataStruct | string | DataRAW>;
        cookie?: Array<DataStructParam>;
        header?: Array<DataStructParam>;
        auth?: AuthSetting;
        setting?: Record<string, string>;
    },
    response: Array<APIResponse>
}
/**
 * 工作台打开的标签
 */
export interface WorkspaceTab{
    name: string;
    /**
     * 打开的页面，都位于components/page/下面
     */
    page: string;
    /**
     * 传入参数
     */
    query?: Record<any, any>;
}
