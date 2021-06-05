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
      {
        icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: 0.0, date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '1.0', date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '2.1', date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '5.0', date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '0.3', date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: 0.0, date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '0.4', date: '2018-05-17' },
      { icon: "https://wx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTKAvnBlxtIfFiatiaweIXQ9jdpLVPGnfvcNMWeLDUHsmia6wbvciatHzg8SJNIHPx47kOxDvMlvngic5SA/132", nickname: "我爸爸", money: '8.0', date: '2018-05-17' },
    ]

  },

  onLoad: function () {
    var that=this;
    wx.getSystemInfo({
      success: function (res) {
        console.log(res);
        that.setData({
          clientHeight: res.windowHeight-240
        });
      }
    });

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
})



