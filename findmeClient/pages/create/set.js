//index.js
//获取应用实例
const app = getApp()
var common = require("../../common.js")
Page({
  onShow: function () {
    common.showCurrentURL();

  },
  data: {
    motto: 'Hello World',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    album:[],
    nextAction:"下一步",
    uploadPhotoDisable:false,
    uploadPhotoText:"上传照片"
  },
  clickMe: function () {
    this.setData({ msg: "Hello World" })
  },
  //事件处理函数
  bindViewTap: function () {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  onLoad: function () {
    this.setData({
      album:app.globalData.album
    })
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
  upload:function(){
    var that=this;
    
    wx.chooseImage({
      success: function (res) {
        var tempFilePaths = res.tempFilePaths
        console.log(tempFilePaths);
        wx.uploadFile({
          url: app.buildURL('c=Index&a=ajaxUpload'), //仅为示例，非真实的接口地址
          filePath: tempFilePaths[0],
          name: 'file',          
          success: function (res) {
            console.log(res);
            var unit8Arr = new Uint8Array(res.data.data)

            var encodedString = String.fromCharCode.apply(null, unit8Arr),
              decodedString = decodeURIComponent(escape((encodedString)));//没有这一步中文会乱码

            var data = JSON.parse(decodedString);
            console.log(data)
            that.data.album.push(data.msg)
            that.setData(
              { album: that.data.album}
            );
            app.globalData.album.push(data.msg);
            app.updateAlbum();
            if (that.data.album.length >= 6) {
              that.setData({
                uploadPhotoText:'照片数量已达上限',
                uploadPhotoDisable: !that.data.uploadPhotoDisable
              })
            }
            
            
          }
        })
      }
    })
  },
  nextStep:function(){
    wx.navigateTo({
      url: '../../pages/create/money',
    })
  },
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
        that.setData(
          { album: that.data.album }
        );
        app.globalData.album.splice(i, 1);
        app.updateAlbum();
        break;
      }
    }


    
  },

  
})
