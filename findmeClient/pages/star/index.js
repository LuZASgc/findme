//index.js
//获取应用实例
const app = getApp();
var common=require("../../common.js")
Page({
  onShow: function () {
    common.showCurrentURL();
  },
 
  data: {
    timer:null,
    num:20,
    album: ['/image/2.jpg', '/image/1.jpg', '/image/3.jpg','/image/5.jpg', '/image/4.jpg', '/image/6.jpg'],
    albumShow: ['none', 'none', 'none', 'none', 'none', 'none'],
    selectedIndex:-1,
    bigImg:'',
    showModalStatus:false,
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  clickMe: function () {
    this.setData({ msg: "Hello World" })
  },
  //事件处理函数
  bindViewTap: function () {
    wx.navigateTo({
      url: '../logs/logs'
    })
  },
  
  onLoad: function () {
    this.countdown(this);
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
  getUserInfo: function (e) {
    console.log(e)
    app.globalData.userInfo = e.detail.userInfo
    this.setData({
      userInfo: e.detail.userInfo,
      hasUserInfo: true
    })
  },
  countdown:function(that){
    setTimeout(function () {
      //console.log("----Countdown----", that.data.num);
      if(that.data.num<=0){
        wx.showToast({
          title: '倒计时结束',
        });
        return;
      }
      that.setData({num:that.data.num-1})
      that.countdown(that);
    }, 1000);
  },
  selectPhoto:function(e){//选中图片
    var index=parseInt(e.currentTarget.dataset.id);
    var reset=false
    if (this.data.albumShow[index] == "none") {
      reset=true      
    }else{
      this.data.selectedIndex = -1;
    }

    this.data.albumShow = ['none', 'none', 'none', 'none', 'none', 'none'];
    if(reset){
      this.data.albumShow[index] = "block";
      this.data.selectedIndex = index;
    }
    this.setData({
      albumShow: this.data.albumShow
    })
  },

  showBigImg: function (e) {
    console.log(e);
    var currentStatu = e.currentTarget.dataset.statu;
    if (typeof currentStatu =='undefined'){
      this.util('open')
      var index = parseInt(e.currentTarget.dataset.id);
      this.setData({
        bigImg: this.data.album[index]
      })

    }else{
      this.util(currentStatu)
    }
  },
  util: function (currentStatu) {
    /* 动画部分 */
    // 第1步：创建动画实例
    var animation = wx.createAnimation({
      duration: 200, //动画时长
      timingFunction: "linear", //线性
      delay: 0 //0则不延迟
    });

    // 第2步：这个动画实例赋给当前的动画实例
    this.animation = animation;

    // 第3步：执行第一组动画
    animation.opacity(0).rotateX(-100).step();

    // 第4步：导出动画对象赋给数据对象储存
    this.setData({
      animationData: animation.export()
    })

    // 第5步：设置定时器到指定时候后，执行第二组动画
    setTimeout(function () {
      // 执行第二组动画
      animation.opacity(1).rotateX(0).step();
      // 给数据对象储存的第一组动画，更替为执行完第二组动画的动画对象
      this.setData({
        animationData: animation
      })

      //关闭
      if (currentStatu == "close") {
        this.setData(
          {
            showModalStatus: false
          }
        );
      }
    }.bind(this), 200)

    // 显示
    if (currentStatu == "open") {
      this.setData(
        {
          showModalStatus: true
        }
      );
    }
  }
});

