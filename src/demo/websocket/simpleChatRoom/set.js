
// 封装websocket的原型链 优化操作方案，框架化处理
// 推送
WebSocket.prototype.sendJson = function(sendData){

    // json转字符串
    if(typeof sendData == 'object'){
        sendData = JSON.stringify(sendData);
    }
    console.log('发送请求', sendData);
    this.send(sendData);
};
// 执行推送动作
WebSocket.prototype.toPublishAction = function(action_sign, param){
    let sendData = {
        actionSign: action_sign,
        param: param,
    };
    this.sendJson(sendData);
};
// 设置监听动作
WebSocket.prototype.listenActionArray = {}; // 设置的动作组
WebSocket.prototype.isAddEventListener = false; // 是否初始化监听者
WebSocket.prototype.setListenAction = function(action_sign, callback){
    this.listenActionArray[action_sign] = callback;
    // 如果没有初始化，则完成初始化监听
    if(this.isAddEventListener == false){
        this.isAddEventListener = true;
        this.addEventListener('message', function(msg){
            let data = msg.data;
            // 字符串转json
            try {
                data = JSON.parse(data);
            } catch(e) {
                data = null;
                console.log('该次返回的数据不是json');
                return;
            }

            let action_sign = data.actionSign;
            let message = data.msg;
            let param = data.data;
            // 如果存在对应动作的闭包，则执行
            if(this.listenActionArray[action_sign]){
                console.log("执行动作", action_sign, data)
                this.listenActionArray[action_sign](param, message, data, msg);
            }else{
                console.log(`不存在动作“${action_sign}”`);
                return;
            }
        });
    }
};

// 再套成壳，挂载一些通用的监听动作
// 是的新实力
function getWsObj(serviceAddress){
    var websocket = new WebSocket(serviceAddress);

    // 监听
    websocket.setListenAction('alterMsg', function(data, msg){
        alert(msg);
    });

    return websocket;
};


// 设置部分要用到的方法
function getDOCByName(name){
    return document.getElementsByName(name)[0];
}
function getDOCById(id){
    return document.getElementById(id)
}
// 简单模板处理
function simpleTemplateDeal(domId, param)
{
    let htmlString = getDOCById(domId);
    param.forEach(function(k, v){
        let key = `{{${k}}}`;
        htmlString.replace(key, v);
    });
    return htmlString;
}