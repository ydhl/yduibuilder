/**
 * 消息队列nodejs 服务端
 * /usr/local/sbin/rabbitmq-server
 * node --experimental-modules server.mjs
 * nohup node --experimental-modules server.mjs &
 * rabbitmqctl list_queues 
 */
import amqp from 'amqplib';
import puppeteer from 'puppeteer';
import OSS from 'ali-oss';
import mysql from 'mysql';
import fs from 'fs';

let  host, user, password, database, port, mysqlPool;
const MAX_BROWSERS = 4;  //启动几个浏览器 
let BROWSER_LIST = []; //存储browserWSEndpoint列表
function rtrim(string, c) {
    if (!c) {
        c = ' ';
    }
    var reg = new RegExp('([' + c + ']*$)', 'gi');
    return string.replace(reg, '');
}

function query (sql, params, callback) {
    if (!mysqlPool) {
        mysqlPool = mysql.createPool({ host, user, password, database, port, socketPath: '/var/lib/mysql/mysql.sock' });
    }
    mysqlPool.getConnection(function (err, conn){
        if (err) {
            console.log(new Date().toLocaleString() + " mysql connect error("+host+","+user+","+password+","+database+","+port+"):" , err.message);
            return;
        }
        conn.query(sql, params, function (queryErr, rows, fields){
            conn.release();
            callback(queryErr, rows, fields);
        })
    })
}

function updateDB(pageid) {
    query('update page set is_snapshoting=0 where uuid=?', [pageid], (err) => {
        if (err) {
            console.log(new Date().toLocaleString() + ":" + 'update db error: ' + err.message)
        }
    })
}

async function handleMessage (browser, msg) {

    // console.log("[x] Received '%s'",msg.content.toString());
    const params = JSON.parse(msg.content);

    // console.log(params)
    const save_path = params.save_path;
    const token = params.token;
    const pageid = params.pageid;
    const preview_url = params.preview_url;
    const localfile = `${pageid}.png`;
    const endpoint = params.region;
    const accessKeyId = params.accessKeyId;
    const isFullPage = params.isFullPage;
    const accessKeySecret = params.accessKeySecret;
    const bucket = params.bucket;
    const scale = 2;
    const width = params.width;
    const height = params.height;
    user = params.mysql_user;
    database = params.mysql_db;
    port = params.mysql_port;
    password = params.mysql_pass;
    host = params.mysql_host;
    
    console.log(new Date().toLocaleString() + ": " + preview_url);

    if (!browser){
        console.log(new Date().toLocaleString() + ": " + preview_url + ' browser not exist');
        return;
    }
    try{
        //1. 截图
        const page = await browser.newPage();
        console.log(new Date().toLocaleString() + ": " + preview_url);
        page.on('dialog', async dialog => {
            await dialog.dismiss();
        });
        page.setDefaultNavigationTimeout(30000);
        await page.setExtraHTTPHeaders({ token })
        await page.setViewport({width, height, deviceScaleFactor: scale})
        await page.goto(preview_url, {waitUntil: 'networkidle0'});
        await page.waitForSelector('body', {visible: true});

        let screenshotArgs;
        if (isFullPage){
            screenshotArgs = {path: localfile, type:'jpeg', quality: 100, fullPage: true}
        }else{
            const overlay = await page.$('.popup-background>:first-child');
            const clip = await overlay.boundingBox();
            screenshotArgs = {path: localfile, type:'jpeg', quality: 100, clip}
        }
        await page.screenshot(screenshotArgs);
        await page.close()
        console.log(new Date().toLocaleString() + ': ' + preview_url + " finish, put to oss ");

        //2. 这里把截图上传到oss中 
        let client = new OSS({endpoint, accessKeyId, accessKeySecret, bucket });
        try{
            // object表示上传到OSS的Object名称，localfile表示本地文件或者文件路径
            const ossResult = await client.put(save_path, localfile);
            // console.log('oss put success: %j', ossResult);
            // const fileurl = ossResult.url
            console.log(new Date().toLocaleString() + ": " + ossResult.url);
            updateDB(pageid);
            fs.unlink(localfile, (err) => {
                if (err){
                    console.log(new Date().toLocaleString() + ":" + 'unlink file error: ' + err.message)
                    return;
                }
            });
            console.log(new Date().toLocaleString() + ": " + preview_url + ' [done] \r\n')
        } catch (e) {
            console.log(e)
        }
    }catch(err){
        console.log(new Date().toLocaleString() + ":" + "[x] Received '%s'",msg ? msg.content.toString() : '');
        console.log(err);
    }
}
/**
 * 移除已经断开的浏览器ws 地址
 */
function removeDisconnectedBrowser() {
    for(let i=0;i<BROWSER_LIST.length;i++){
        let browserWSEndpoint = BROWSER_LIST[i];
        puppeteer.connect({browserWSEndpoint}).catch( (reason) => {
            BROWSER_LIST.splice(i,1);
        });
    }
}
/**
 * 
 * @returns 启动浏览器并放入BROWSER_LIST
 */
function launchBrowser(){
    puppeteer.launch({headless:true,
        // devtools: true,
        ignoreHTTPSErrors: true,
        args: [
        '--disable-gpu',
        '--disable-dev-shm-usage',
        '--disable-setuid-sandbox',
        '--no-first-run',
        '--no-sandbox',
        '--no-zygote',
        '--single-process'
    ]}).then((browser) => {
        BROWSER_LIST.push(browser.wsEndpoint());
        browser.on('disconnected', (a) => {
            console.log(new Date().toLocaleString() + ' browser disconnected relaunch')
            removeDisconnectedBrowser()
            launchBrowser()
        })
        console.log(new Date().toLocaleString() + ' launchBrowser', BROWSER_LIST)
    }).catch( (reason) => {
        console.log(new Date().toLocaleString() + ' puppeteer.launch error relaunch ' , reason)
        launchBrowser()
    });
}

function start() {
    console.log(new Date().toLocaleString() + ' start puppeteer, browser count '+MAX_BROWSERS)

    //通过amqp连接本地的rabbitmq服务，返回一个promise对象
    amqp.connect('amqp://127.0.0.1').then(async function(rabbitmqConn){
        //进程检测到终端输入CTRL+C退出信号时，关闭RabbitMQ队列。
        process.once('SIGN',function(){
            removeDisconnectedBrowser();
            rabbitmqConn.close();
        });

        //连接成功后创建通道
        return rabbitmqConn.createChannel().then(function(channel){
            //通道创建成功后我们通过通道对象的assertQueue方法来监听snapshot队列，并设置durable持久化为true。该方法会返回一个promise对象。
            channel.assertQueue('ydevcloud_snapshot',{durable:true}).then(async function(_qok){
                //监听创建成功后，我们使用ch.consume创建一个消费者。
                //ch.consume会返回一个promise，这里我们把这个promise赋给ok。
                return channel.consume('ydevcloud_snapshot',function(msg){
                    if (!msg) return
                    let tmp = Math.floor(Math.random()* MAX_BROWSERS);
                    let browserWSEndpoint = BROWSER_LIST[tmp];
                    puppeteer.connect({browserWSEndpoint}).then((browser)=>{
                        handleMessage(browser, msg);
                    }).catch( (reason) => {
                        console.log(new Date().toLocaleString() + ' connect browser error relaunch ' , reason)
                        launchBrowser();
                    });
                },{noAck:true});
            }).then(function(_consumeOk){
                console.log(new Date().toLocaleString() + ":" + '[*] Waiting for message. To exit press CRTL+C');
            }).catch( (reason) => {
                console.log(new Date().toLocaleString() + ' rabbitmq error ' , reason)
                start()
            });
        });
    }).catch( (reason) => {
        console.log(new Date().toLocaleString() + ' rabbitmq connect error ' , reason)
        start()
    });
}

// 先启动MAX_BROWSERS个浏览器
for(var i=0;i<MAX_BROWSERS;i++){
    launchBrowser();
}
start()
// setInterval(() => {
    
// }, 5000);