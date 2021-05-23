// 创建游戏功，进行分享
const app = getApp()
var common = require("../../common.js")
Page({
  onShow: function () {
    common.showCurrentURL();
  },

  /**
   * 页面的初始数据
   */
  data: {
    nickname:"施坚永",
    avatarUrl:'https://wx.qlogo.cn/mmopen/vi_32/BHfgv4LrZkX3oYot2wlBQKXkX8PWO7nHibIMDg2gV72MjYgVx6cfpqZN4bm4icDFsOb3YocsiaC6CcNQS96eEujjA/0'
  },
  createNew:function(){
    wx.navigateTo({      
      url: '../../pages/suren/set',
    })
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    console.log('参数参数：----',options);
    this.setData({
      id:options.id
    })
    wx.showShareMenu({
      withShareTicket: true,
      success: function (res) {
        // 分享成功
        console.log('分享:' , res)
      },
      fail: function (res) {
        // 分享失败
        console.log(res)
      }
    })
  },
  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },



  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {
  
  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {
  
  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {
  
  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {
    return {
      title: '找到我就算你厉害',
      desc: '最具人气的小程序开发联盟!',
      path: 'pages/suren/finish?id=123',
      success:function(res){
        console.log('shareReturn',res);
      },
      fail:function(ret){
        console.log('cancelShare',ret);
      }
    }
  },
})