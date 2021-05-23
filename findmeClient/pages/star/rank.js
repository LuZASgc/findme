// 排行
const app = getApp();
var common = require("../../common.js")
Page({
  /**
   * 页面的初始数据
   */
  data: {
    page:1,
    list:[{'icon':'','name':'','rank':0}]  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    common.request({
      url: 'c=Game&a=starRank',
      success: function (e) {

        console.log(e)

      },
      method: "POST"
    });
  },



  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {
  
  },

})