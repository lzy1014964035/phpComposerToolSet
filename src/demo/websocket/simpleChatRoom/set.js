
// 封装websocket的原型链 优化操作方案，框架化处理
// 推送
WebSocket.prototype.sendJson = function(sendData){
    // json转字符串
    if(typeof sendData == 'object'){
        sendData = JSON.stringify(sendData);
    }
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

/**
 * 获取一个ws服务
 * @param serviceAddress
 * @returns {makeWsService}
 */
function makeWsService(serviceAddress){

    this.debugConfig = false;
    this.wsObj = null;

    // 检查ws对象
    this.checkWsObj = function(){
        if(this.wsObj){
            return true;
        }
        return false;
    };

    // 发送数据
    this.sendJson = function(sendData){
        if( ! this.checkWsObj())return;
        this.wsObj.sendJson(sendData);
    };
    this.toPublishAction = function(action_sign, param){
        if( ! this.checkWsObj())return;
        if(this.debugConfig) {
            console.log('发送请求', action_sign, param);
        }
        this.wsObj.toPublishAction(action_sign, param);
    };
    // 监听请求
    this.listenActionArray = {};
    this.setListenAction = function(actionSign, callback){
        this.listenActionArray[actionSign] = callback;
    };
    // 获取静态
    this.getStaticData = function(key){
        if( ! this.otherStaticData){
            return null;
        }
        return this.otherStaticData[key];
    };
    // 设置静态数据
    this.setStaticData = function(key, value){
        if( ! this.otherStaticData){
            this.otherStaticData = {};
        }
        this.otherStaticData[key] = value;
    };


    // 创造ws对象
    this.makeWsObj = function(){
        let that = this;
        // 删除
        if(that.wsObj){
            delete that.wsObj;
        }
        let websocket = new WebSocket(serviceAddress);

        if( ! websocket){
            return;
        }

        // 设置监听
        websocket.addEventListener('message', function(msg){
            let data = msg.data;
            // 字符串转json
            try {
                data = JSON.parse(data);
            } catch(e) {
                data = null;
                if(that.debugConfig)console.log('该次返回的数据不是json');
                return;
            }

            let action_sign = data.actionSign;
            let message = data.msg;
            let param = data.data;
            // 如果存在对应动作的闭包，则执行
            if(that.listenActionArray[action_sign]){
                if(that.debugConfig)console.log("执行动作", action_sign, data);

                that.listenActionArray[action_sign](param, message, data, msg);
            }else{
                if(that.debugConfig)console.log(`不存在动作“${action_sign}”`);
                return;
            }
        });
        // 链接时检查缓存，判断是否为断线重连
        websocket.onopen = function(){
            let userData = that.getStaticData('userData');
            if(userData){
                let userToken = userData['token'];
                that.toPublishAction('user/DAR', {
                    token: userToken
                });
            }
        };

        // 重连
        let reconnection = false;
        websocket.onclose = function(data){
            console.log(data);
            if(that.debugConfig)console.log('链接服务器已断开');
            if(reconnection){
                return;
            }
            setTimeout(function(){
                that.makeWsObj(serviceAddress);
            }, 2000);
        };
        websocket.onerror = function(err){
            if(that.debugConfig)console.log(`链接服务器异常`, err);
        };

        // 监听
        that.setListenAction('alterMsg', function(data, msg){
            alert(msg);
        });

        that.wsObj = websocket;
    };

    this.makeWsObj();

    return this;
}


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
    let htmlString = getDOCById(domId).innerHTML;
    for(let key in param){
        let value = param[key];
        let keyString = `{{${key}}}`;
        htmlString = htmlString.replace(keyString, value);
    }
    return htmlString;
}