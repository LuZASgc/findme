//index.js
//获取应用实例
const app = getApp()
var common = require("../../common.js")
Page({
  onShow: function () {
    wx.setNavigationBarTitle({
      title: '创建我的游戏--找我唷',
    })
  },
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    album: ['', '', '', '', '', ''],
    uploadPhotoDisable:false,

    showTip: false,
    qa: [{ q: '积分有什么用', a: '消耗积分才可以玩星探游戏' }, { q: '怎样获取积分', a: '分享小程序给您的朋友即可获得积分' }, { q: '奖金怎么提现', a: '点击提现，系统就会打款到您的微信钱包中' }]
  },
  //处理图片显示
  processPhoto:function(){
    var album = ['', '', '', '', '', ''];
    var photoNum = app.globalData.album.length;
    for (var i = 0; i < photoNum; i++) {
        album[i] = app.globalData.album[i];
    }
    this.setData({
      album: album
    })
  },
  /**
     * 弹出层函数
     */
  //出现
  showTip: function () {

    this.setData({ showTip: true })

  },
  //消失
  hideTip: function () {

    this.setData({ showTip: false })

  },
  onLoad: function () {   
    console.log(app.globalData.album)

    this.processPhoto();
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse) {
      // 由于 getUserInfo 是网络请求，可能会在 Page.onLoad 之后才返回
      // 所以此处加入 callback 以防止这种情况
      app.userInfoReadyCallback = res => {
        this.setData({
          userInfo: res.userInfo,
          hasUserInfo: true
        })
      }
    } else {
      // 在没有 open-type=getUserInfo 版本的兼容处理
      wx.getUserInfo({
        success: res => {
          app.globalData.userInfo = res.userInfo
          this.setData({
            userInfo: res.userInfo,
            hasUserInfo: true
          })
        }
      })
    }
  },
  getUserInfo: function (e) {
    console.log(e)
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  //下一步，跳转充值页面
  nextStep: function () {
    wx.navigateTo({
      url: '../../pages/suren/money',
    })
  },
  //上传图片
  upload:function(){
    var that=this;    
    wx.chooseImage({
      success: function (res) {
        var tempFilePaths = res.tempFilePaths
        console.log(tempFilePaths);
        wx.uploadFile({
          url: app.buildURL('c=Index&a=ajaxUpload&tp=user'), //仅为示例，非真实的接口地址
          filePath: tempFilePaths[0],
          name: 'file',
          success: function (res) {
            if(typeof res =='object'){
              var ret = JSON.parse(res.data);
            }else{
              var unit8Arr = new Uint8Array(res.data.data)
              var encodedString = String.fromCharCode.apply(null, unit8Arr),
                decodedString = decodeURIComponent(escape((encodedString)));//没有这一步中文会乱码
              var ret = JSON.parse(decodedString);
            }


            var photoNum = app.globalData.album.length;
            var append=true
            for (var i = 0; i < photoNum; i++) {
              if (app.globalData.album[i].length<5){
                app.globalData.album[i] = ret.msg;
                append=false;
                break;
              }
            }
            if (append){
              app.globalData.album.push(ret.msg);
            }
            that.processPhoto()
            updateAlbum();
            if (app.globalData.album.length >= 6) {
              that.setData({
                uploadPhotoText:'照片数量已达上限',
                uploadPhotoDisable: !that.data.uploadPhotoDisable
              })
            }        
          }
        });        
      }
    })
  },
  //删除图片
  deletePhoto:function(e){
    var that=this;
    if(e.target.id!='remove'){
      return false
    }
    var src=e.target.dataset.src;
    var len = that.data.album.length;
    for (var i = 0; i < len; i++) {
      if (that.data.album[i] == src) {
        that.data.album.splice(i, 1);    
        app.globalData.album.splice(i, 1);    
        that.processPhoto();
        
        updateAlbum();
        break;
      }
    }
    
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
