<?php
/**
 * Created by Jasmine.
 * Date: 2016/11/2
 * description:
 * Address: 浙江宣逸网络科技有限公司
 */

//1、会员活动列表图、会员活动详情首图、积分兑换详情首图［w：598／h：332］
//2、积分首页轮播图［w：640／h：250］
//3、积分兑换列表图［w：290／h：250］
//4、积分兑换记录用图［w：150／h：130］
//5、首页轮播图、积分抽奖列表图、首页文章列表图、积分抽奖详情首图、文章分类列表图［w：640／h：350］
//6、文章详情页相关兑换、相关活动［w：210／h：142］
//7、活动报名页小图［w：216／h：120］
//8、活动报名，未中奖有福利弹框［w：250／h：140］
//9、积分首页－小积分抽大奖［w：390／h：180］ 积分首页－秒杀［w：250／h：180］
//10、文章详情页图片［w：600／h：600］
//11、头像[［w：110／h：110］

return array(
    'imgPosition' => array(

//        'IMG_EVENT_INFO'    =>array('width'=>598,'height'=>332),            //会员活动详情首图
//        'IMG_POINT_EXCHANGE_RECORD' =>array('width'=>150,'height'=>130),    //积分兑换记录用图
//        'IMG_POINT_LOTTERY_LIST' =>array('width'=>640,'height'=>350),       //积分抽奖列表图
//        'IMG_ARTICLE_CLASSIFY_LIST' =>array('width'=>640,'height'=>350),    //文章分类列表图
//        'IMG_EVENT_UNSELECTED_WELFARE' =>array('width'=>250,'height'=>140), //活动报名，未中奖有福利弹框
//        'IMG_POINT_INDEX_LOTTERY' =>array('width'=>390,'height'=>180),      //积分首页－小积分抽大奖

        'IMG_EVENT_LIST'            =>array('width'=>598,'height'=>332),            //会员活动列表图
        'IMG_POINT_EXCHANGE_INFO'   =>array('width'=>598,'height'=>332),    //积分兑换详情首图
        'IMG_POINT_MARQUEE'         =>array('width'=>640,'height'=>250),            //积分首页轮播图
        'IMG_POINT_EXCHANGE_LIST'   =>array('width'=>290,'height'=>250),    //积分兑换列表图
        'IMG_WAP_INDEX_MARQUEE'     =>array('width'=>640,'height'=>350),        //wap首页轮播图
        'IMG_POINT_LOTTERY_ABOUT'   =>array('width'=>640,'height'=>350),      //积分抽奖相关
        'IMG_INDEX_ARTICLE_LIST'    =>array('width'=>640,'height'=>350),       //首页文章列表图
        'IMG_ARTICLE_INFO_ABOUT'    =>array('width'=>210,'height'=>142),       //文章详情页相关兑换、相关活动
        'IMG_EVENT_APPLY'           =>array('width'=>216,'height'=>120),              //活动报名页小图
        'IMG_USER_HEAD'             =>array('width'=>110,'height'=>110),                //头像
        'IMG_WEB_INDEX_MARQUEE'     =>array('width'=>1180,'height'=>350),       //web主页轮播图
        'IMG_UEDITOR_UPLOAD'        =>array('width'=>640,'height'=>0),          //百度编辑器上传图片规格，0表示不限制
        'IMG_CARD'                  =>array('width'=>150,'height'=>130),                    //卡券图片
        'IMG_SIGN_PRIZE'            =>array('width'=>130,'height'=>100),              //签到设置-奖品图片
        'IMG_SIGN_TURNTABLE'        =>array('width'=>680,'height'=>680),        //签到设置-转盘底图
        'IMG_DIAL_ABOUT_BASE'       =>array('width'=>640,'height'=>1007),       //大转盘相关页面底图（转盘页底图，奖品说明页底图）
        'IMG_EVENT_TASK_SUBMIT'     =>array('width'=>640,'height'=>0),        //交作业页面作业图和用户分享作业图
        'IMG_SHOP_STORE'            =>array('width'=>150,'height'=>130),      //商户管理的商户图片
        'IMG_STORE_LIST'            =>array('width'=>250,'height'=>186),      //门店列表图片
        'IMG_STORE_ALBUM'           =>array('width'=>600,'height'=>554),      //门店图集图片
        'IMG_COMMENT'               =>array('width'=>150,'height'=>130),      //评论中附加图片
        'IMG_PACKAGE_LIST'          =>array('width'=>105,'height'=>79),      //门店套餐列表图
        'IMG_PACKAGE_ALBUM'         =>array('width'=>358,'height'=>200),      //门店套餐图集
    )
);