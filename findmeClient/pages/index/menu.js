// pages/index/menu.js

Page({
  /**
   * 页面的初始数据
   */
  data: {
    page:[
      { title: '首页', url: "pages/index/index" },
      { title: '菜单', url: "pages/index/menu" },
      { title: '游戏', url: "pages/play/play" },
      { title: '星探-游戏', url: "pages/star/play" },
      { title: '星探-排行', url: "pages/star/rank" },
      { title: '素人-开始对赌', url: "pages/suren/begin?id=7" },
      { title: '素人-开始普通', url: "pages/suren/begin?id=6" },
      { title: '素人-设置', url: "pages/suren/set" },
      { title: '素人-充钱', url: "pages/suren/money" },
      { title: '素人-创建结束', url: "pages/suren/finish?id=9874&name=shijy" },
      { title: '素人-游戏', url: "pages/suren/play?id=7" },
      { title: '素人-游戏', url: "pages/suren/play" },
      { title: '个人主页', url: "pages/suren/home" },
      { title: '帮助-首页', url: "pages/help/index" },
      { title: '', url: "pages/logs/logs" },         
      
    ]
  },
  jump:function(e){
    console.log(e);
    console.log(e.currentTarget.dataset.url);
    var a=wx.navigateTo({
      url: "../../" + e.currentTarget.dataset.url,
    })
    console.log(a);

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