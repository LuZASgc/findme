//index.js
//获取应用实例
const app = getApp();
var common=require("../../common.js")
Page({
  onShow: function () {
    
  },
 
  data: {
    timer:null,
    num:5,
    album: [
      { 'uid': 0, 'album': '/image/2.jpg' }, { 'uid': 0, 'album': '/image/2.jpg' }, { 'uid': 0, 'album': '/image/2.jpg' },
      { 'uid': 0, 'album': '/image/2.jpg' }, { 'uid': 0, 'album': '/image/2.jpg' }, { 'uid': 0, 'album': '/image/2.jpg' }
      ],
    albumShow: ['none', 'none', 'none', 'none', 'none', 'none'],
    selectedIndex:-1,
    selectedUid:0,
    targetUid:0,
    bigImg:'/image/2.jpg',
    showModalStatus:false,
    avatarUrl:'',
    userInfo: {},
    hasUserInfo: false,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },
  onShareAppMessage: function () {
    return {
      title: '微信小程序联盟',
      desc: '最具人气的小程序开发联盟!',
      path: 'pages/star/index?id=123'
    }
  },
  onLoad: function () {
    
    //console.log(app.globalData.userInfo)
    if (app.globalData.userInfo) {
      this.setData({
        avatarUrl: app.globalData.userInfo.avatarUrl,
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
  startCountDown:function(){
    //this.data.num=5;
    this.setData({
      num:10
    })
    this.countdown(this);
  },
  countdown:function(that){
    setTimeout(function () {
      //console.log("----Countdown----", that.data.num);
      if(that.data.num<=0){
        wx.showToast({
          title: '倒计时结束',
        });
        that.submitResult();
        return;
      }
      that.setData({num:that.data.num-1})
      that.countdown(that);
    }, 1000);
  },
  selectPhoto:function(e){//选中图片
  console.log(e)
    var index=parseInt(e.currentTarget.dataset.id);
    var reset=false
    if (this.data.albumShow[index] == "none") {
      reset=true      
    }else{
      this.data.selectedIndex = -1;
      this.data.selectedUid=0;
    }

    this.data.albumShow = ['none', 'none', 'none', 'none', 'none', 'none'];
    if(reset){
      this.data.albumShow[index] = "block";
      this.data.selectedIndex = index;
      this.data.selectedUid = e.currentTarget.dataset.uid;
    }
    this.setData({
      albumShow: this.data.albumShow
    })
  },
  //显示大图
  showBigImg: function (e) {
    console.log(e);
    var currentStatu = e.currentTarget.dataset.statu;
    if (typeof currentStatu =='undefined'){
      
      if(e.currentTarget.id=='avatar'){
        this.setData({
          bigImg: e.currentTarget.dataset.src
        })
      }else{
        var index = parseInt(e.currentTarget.dataset.id);
        this.setData({
          bigImg: this.data.album[index].album
        })
      } 
      this.util('open')

    }else{
      this.util(currentStatu)
    }
  },
  //显示大图的动画效果
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
  },
  getRand:function(){
    return Math.ceil(Math.random()*10000);
  },
  processTeam:function(data){
    if(typeof data=='string') return false;
    var that=this;
    for (var j = 0, len = data.length; j < len; j++) {

      if (typeof data[j].headPic !== "undefined") {
        that.data.targetUid = data[j].uid;
        that.setData({
          avatarUrl: data[j].headPic
        });
        break;
      }
    }
    that.data.album = data;
    that.data.albumShow = ['none', 'none', 'none', 'none', 'none', 'none'];
    that.setData({
      album: that.data.album,
      albumShow: that.data.albumShow
    });
    that.startCountDown();
  },

  startStarGame:function(){
    var that=this;
    common.request({
      url: 'c=Game&a=startStarGame',
      success: function (e) {    
         
        that.processTeam(e);
        
      },
      method: "POST"
    });
  },
  submitResult: function () {
    var that = this;
    common.request({
      url: 'c=Game&a=submitResult',
      data: {
        gid: this.getRand(),
        oid: this.data.targetUid,
        uid: this.getRand(),
        sid: this.data.selectedUid
      },
      success: function (e) {
        if(e.result!=1){
          wx.showToast({
            title: '错误',
          })
        }else{
          wx.showToast({
            title: '正确',
          })
          that.processTeam(e.next)
          
        }
        
        
      },
      complete: function (ret){ 
        that.data.selectedUid=0
      },
      method: "POST"
    });
  }
});

