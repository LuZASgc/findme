//app.js
var common=require('common.js');
App({
  globalData: {
    baseURL: '192.168.31.175:81/api.php?',//基础通知url
    appId: 'wx0bc6c5ceddbae730',
    secret: 'e774e7f275cca946bcd85e217cd2ebd8',
    userInfo: null,
    openId: null,
    uid:0,
    album:[]
  },
  userInfoReadyCallback:function(res){
    this.globalData.userInfo = res.userInfo;
    console.log("call ------------ userInfoReadyCallback");
  },
  getUserInfo:function(that){
    wx.getUserInfo({
      success: res => {
        // 可以将 res 发送给后台解码出 unionId
        that.globalData.userInfo = res.userInfo
        console.log(res);
        
        that.login();

        // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
        // 所以此处加入 callback 以防止这种情况
        if (that.userInfoReadyCallback) {
          that.userInfoReadyCallback(res)
        }
      }
    })
  },
  onLaunch: function () {
    var that = this;
    // 展示本地存储能力
    var logs = wx.getStorageSync('logs') || []
    logs.unshift(Date.now())
    wx.setStorageSync('logs', logs)

    // 获取用户信息
    wx.getSetting({
      success: res => {
        if (res.authSetting['scope.userInfo']) {
          // 已经授权，可以直接调用 getUserInfo 获取头像昵称，不会弹框
          that.getUserInfo(that);
        }else{
          wx.authorize({
            scope: 'scope.userInfo',
            success() {
              that.getUserInfo(that);
            }, 
            fail() {
             console.log("error---------------")
            }
          })
        }
      }
    })
  },
  buildURL:function(str){
    return this.globalData.baseURL+str;
  }, 
  login: function (e) {
    var that=this;
    wx.login({
      success: function (res) {
        if (res.code) {
          //获取openId
          wx.request({
            url: 'https://api.weixin.qq.com/sns/jscode2session',
            data: {
              //小程序唯一标识
              appid: that.globalData.appId,
              //小程序的 app secret
              secret: that.globalData.secret,
              grant_type: 'authorization_code',
              js_code: res.code
            },
            method: 'GET',
            header: { 'content-type': 'application/json' },
            success: function (openIdRes) {
              console.info("登录成功返回的openId：" + openIdRes.data.openid);
              wx.setStorageSync('PHPSESSID', openIdRes.data.openid);
              that.globalData.openId = openIdRes.data.openid;


              // 判断openId是否获取成功
              if (openIdRes.data.openid != null & openIdRes.data.openid != undefined) {
                // 有一点需要注意 询问用户 是否授权 那提示 是这API发出的                 
                common.request({
                    url: 'c=Game&a=userLogin', 
                    data: { 
                      openId: that.globalData.openId,
                      headPic: that.globalData.userInfo.avatarUrl,
                      gender: that.globalData.userInfo.gender,
                      nickname: that.globalData.userInfo.nickName,
                    }, 
                    success: function (e) { 
                      that.globalData.uid = e.uid;
                      that.globalData.album =e.album.split(',');
                    }, 
                    method: "POST" 
                });

              } else {
                console.info("获取用户openId失败");
              }
            },
            fail: function (error) {
              console.info("获取用户openId失败");
              console.info(error);
            }
          })
        }
      }
    });
  },
  updateAlbum: function () {
    var that = this;
    common.request({
      url: 'c=Game&a=updateAlbum',
      data: {
        uid: this.globalData.uid,
        album: this.globalData.album.join(','),
      },
      success: function (e) {
        console.log(e)
      },
      method: "POST"
    });
  },
})