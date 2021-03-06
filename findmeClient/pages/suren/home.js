//index.js
//获取应用实例
const app = getApp()
var common = require("../../common.js")
Page({
  data: {
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),
    myGame: [
     // { id: 1, title: "施坚永", date: '2018-05-19' },
      //{ id: 2, title: "施某人", date: '2018-05-18' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
      { id: 3, title: "我爸爸", date: '2018-05-17' },
    ]

  }, 
  onLoad: function () { 
    if (app.globalData.userInfo) {
      this.setData({
        userInfo: app.globalData.userInfo,
        hasUserInfo: true
      })
    } else if (this.data.canIUse){
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

  getUserInfo: function(e) {
    console.log(e)
    app.globalData.userInfo = e.detail.userInfo
    console.log(e.detail);
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },  
  gameTap:function(e){
    console.log(e);
    const id=e.currentTarget.dataset.id;
    wx.navigateTo({
      url: 'detail?id='+id,
    })
  },
  surenTap: function () {
    wx.navigateTo({
      url: '../../pages/suren/index',
    })
  },
})



