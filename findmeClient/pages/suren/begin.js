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
    nickname:"青春如歌",
    avatarUrl:'',
    hasPrizeWithoutBet:false,
    hasPrizeWithBet:false,
    gid:0,//游戏
  },
  createNew:function(){
    wx.navigateTo({      
      url: '../../pages/suren/set',
    })
  },
  beginGame:function(){    
    wx.navigateTo({
      url: '../../pages/suren/play?id='+this.data.gid,
    })
  },
  recharge:function(){

  },
  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    var that=this;
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
    });
    common.request({ 
      url: 'c=Game&a=getGame', 
      data: { id: options.id }, 
      success: function (e) { 
        var data=e;
        if(data.status!=0){
          wx.showModal({
            title: '出错',
            content: data.msg,
            complete:function(){
              wx.navigateTo({
                url: '../../pages/index/index',
              })
            }
          })
          // wx.showToast({
          //   title: data.msg,
          // });
          return false;
        }
        that.data.gid=options.id;
        var hasPrizeWithoutBet=true;
        var hasPrizeWithBet=false;
        if(parseInt(data.base.totalMoney)>0){
          if (data.base.isBet=='1'){
            hasPrizeWithBet = true;
            hasPrizeWithoutBet = false;
          }

        }

        that.setData({
          nickname: data.user.nickname,
          avatarUrl: data.user.headPic,
          hasPrizeWithoutBet: hasPrizeWithoutBet,
          hasPrizeWithBet: hasPrizeWithBet
        })


        }, 
      method: "POST" });
  },



//支付押金
  submitForm: function (e) {
    var that = this;
    var data = {
      gid: that.data.gid
    }
    common.request(
      {
        url: 'c=Game&a=wagerOrder',
        data: data,
        success: function (re) {
          console.log(re);
          if (re.status == 0) {
            that.pay(1, re.wid.toString());
          } else {
            wx.showModal({
              title: '出现错误',
              content: re.msg,
            })
          }
        },
        fail: function (re) { },
        complete: function (re) { }
      }
    );

    //
  },
  pay: function (fee, wid) {//fee单位：元
    var payInfo = {
      body: '支付保证金',
      total_fee: fee,
      order_sn: 'w_'+wid
    }
    this.basePay(payInfo, function (e) { 

      common.request({
        url: 'm=Api&c=Api&a=checkPayStatus',
        data: { 'id': wid, 'type': 'w' },
        success: function (_payResult) {

        }
      })


     }, function (e) { console.log(e) });
  },

  /** 
 * 支付函数 
 * @param  {[type]} _payInfo [description] 
 * @return {[type]}          [description] 
 */
  basePay: function (_payInfo, success, fail) {
    var payInfo = {
      body: '',
      total_fee: 0,
      order_sn: ''
    }
    Object.assign(payInfo, _payInfo);
    if (payInfo.body.length == 0) {
      wx.showToast({
        title: '支付信息描述错误'
      })
      return false;
    }
    if (payInfo.total_fee == 0) {
      wx.showToast({
        title: '支付金额不能0'
      })
      return false;
    }
    if (payInfo.order_sn.length == 0) {
      wx.showToast({
        title: '订单号不能为空'
      })
      return false;
    }
    var This = this;

    payInfo.openid = app.globalData.openId;
    common.request({
      url: 'c=Pay&a=prepay',
      data: payInfo,
      success: function (res) {
        if (!res.status) {
          wx.showToast({
            title: res['errmsg']
          })
          return false;
        }
        var data = res.data;
        console.clear()
        console.log(data);

        common.request({
          url: 'c=Pay&a=pay',
          data: { prepay_id: data.data.prepay_id },
          success: function (_payResult) {
            var payResult = _payResult;
            console.log(payResult);
            wx.requestPayment({
              'timeStamp': payResult.timeStamp.toString(),
              'nonceStr': payResult.nonceStr,
              'package': payResult.package,
              'signType': payResult.signType,
              'paySign': payResult.paySign,
              'success': function (succ) {
                success && success(succ);
              },
              'fail': function (err) {
                fail && fail(err);
              },
              'complete': function (comp) {

              }
            })
          }
        })
      }
    })

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