// pages/star/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    showTip:false
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
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
  
  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
  
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
  
  },
  starTap:function(){
    wx.navigateTo({
      url: '../star/play',
    })
  },
  starRankTap:function(){
    wx.navigateTo({
      url: '../star/rank',
    })
  }
})