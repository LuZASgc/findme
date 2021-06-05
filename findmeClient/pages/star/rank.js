// 排行
const app = getApp();
var common = require("../../common.js")
Page({
  /**
   * 页面的初始数据
   */
  data: {
    list:[{'icon':'11','name':'111','rank':1,'score':99},
    {'icon': '11', 'name': '22', 'rank': 2, 'score': 99 },
    {'icon': '11', 'name': '333', 'rank': 3, 'score': 99 },
    {'icon': '11', 'name': '444', 'rank': 4, 'score': 99 },
    {'icon': '11', 'name': '555', 'rank': 5, 'score': 99 },
    {'icon': '11', 'name': '6666', 'rank': 6, 'score': 99 },
    {'icon': '11', 'name': '777', 'rank': 7, 'score': 99 },
    {'icon': '11', 'name': '777', 'rank': 8, 'score': 99 },
    { 'icon': '11', 'name': '777', 'rank': 9, 'score': 99 },
    { 'icon': '11', 'name': '777', 'rank': 10, 'score': 99 }],
    mine: {'icon': '', 'name': '', 'rank': 0, 'score': 99 }
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