// pages/create/money.js
const app = getApp()
var common = require("../../common.js")
Page({

  /**
   * 页面的初始数据
   */
  data: {
    prizeMoney:0,
    num:1,
    min:1,
    totalMoney:0,
    serviceScale:2,
    canPay:false
  },
  changeMoney:function(e){
    console.log(e);
    this.data.prizeMoney = e.detail.value * 1;
    this.setData({
      totalMoney: this.data.prizeMoney * (1 + this.data.serviceScale / 100)
    }
    )
  },

  submitForm:function(e){
    var that = this;
    console.log(e);
    var isBet = 0;
    if (e.detail.value.isBet){
      isBet=1;
    }
    var data={
      prizeMoney:e.detail.value.prizeMoney,
      num:e.detail.value.num,
      min:e.detail.value.min,
      isBet:isBet
    }
   common.request(
      {
        url:'c=Game&a=buildOrder',
        data: data, 
        success:function(re){
          console.log(re);
          if(re.status==0){
            that.pay(that.data.totalMoney,re.gid.toString());
          }else{
           
            wx.showModal({
              title: '出现错误',
              content: re.msg,
            })
          }
        },
        fail:function(re){},
        complete:function(re){}
      }
    );
    
    //
  },
  pay:function(fee,gid){//fee单位：元
    
    var payInfo={
      body:'塞钱进红包',
      total_fee: fee,
      order_sn:gid
    }
    this.basePay(payInfo, function (e) { console.log(e) }, function (e) { console.log(e) });
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

  }  
})