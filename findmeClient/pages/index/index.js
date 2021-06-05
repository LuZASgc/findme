//index.js
//获取应用实例
const app = getApp()
var common = require("../../common.js")
Page({
  onShow: function () {
    
  },
  data: {
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo'),

    showTip:false,
    qa: [{ q: '积分有什么用', a: '消耗积分才可以玩星探游戏' }, { q: '怎样获取积分', a: '分享小程序给您的朋友即可获得积分' }, { q: '奖金怎么提现', a: '点击提现，系统就会打款到您的微信钱包中' }]
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
  login: function(){
    common.request({ url: 'c=Game&a=userLogin', data:{openId: app.globalData.openId}, success:function(e){console.log(e)}, method:"get" });
  },
  homeTap:function(){
    wx.navigateTo({
      url: '../suren/home',
    })
  },
  bindViewTap: function () {
    wx.navigateTo({
      url: '../../pages/suren/home',
    })
  },
  starTap: function () {
    wx.navigateTo({
      url: '../../pages/star/index',
    })
  },
  surenTap: function () {
    wx.navigateTo({
      url: '../../pages/suren/index',
    })
  },
  
})



