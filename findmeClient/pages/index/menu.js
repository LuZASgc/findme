// pages/index/menu.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    page:[
      {title:'首页',url:"pages/index/index"},
      { title: '菜单', url: "pages/index/menu" },
      { title: '游戏', url: "pages/play/play" },
      { title: '星探-首页', url: "pages/star/index" },
      { title: '发起-首页', url: "pages/create/index" },
      { title: '发起-设置', url: "pages/create/set" },
      { title: '发起-上传', url: "pages/create/upload" },
      { title: '帮助-首页', url: "pages/help/index" },
      { title: '', url: "pages/logs/logs" },         
      
    ]
  },
  jump:function(e){
    console.log(e);
    console.log(e.currentTarget.dataset.url);
    wx.navigateTo({
      
      url: "../../" + e.currentTarget.dataset.url,
    })
    

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
  
  }
})