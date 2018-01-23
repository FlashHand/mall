<?php
// +----------------------------------------------------------------------
// | OnlineRetailers [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2003-2023 www.yisu.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed 亿速网络（http://www.yisu.cn）
// +----------------------------------------------------------------------
// | Author: 王强 <opjklu@126.com>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Common\Controller\MsmFactory;
use Common\Tool\Tool;
use Think\Controller;
use Common\Model\BaseModel;
use Home\Model\OrderModel;
use Common\Model\OrderWxpayModel;
use Common\TraitClass\NoticeTrait;
use Common\TraitClass\InternetTopTrait;
use Common\TraitClass\SmsVerification;
use Think\Hook;
use Common\Behavior\WangJinTing;
use Common\TraitClass\WxNofityTrait;
use Common\TraitClass\OrderNoticeTrait;
use Common\Behavior\Decorate;
use Common\TraitClass\AlipayNotifyTrait;
use Common\Behavior\AlipaySerialNumber;
use Common\Behavior\Balance;
use Common\TraitClass\WxListenResTrait;

class NofityController extends Controller
{
    use NoticeTrait;
    use SmsVerification;
    use InternetTopTrait;
    use WxNofityTrait;
    use OrderNoticeTrait;
    use AlipayNotifyTrait;
    use WxListenResTrait;
    private $url = '';
    
    /**
     * 支付成功订单页
     * @var string
     */
    const RELEVANT = 'relevant';
    
    public function __construct()
    {
         parent::__construct();
        
         Hook::add('reade', WangJinTing::class);
        
         $information = $this->getIntnetInformation();
         $this->assign('hot_words', self::keyWord());
        
         $this->assign('intnetTitle', $information['intnet_title']);
        
         $this->assign('str', $this->getFamily());
        
         $this->assign($information);
    }
    
    public function wxNotify()
    {
        // 获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];


    
        Tool::connect('Token');
    
        $data = Tool::init($xml, self::PARTNER_ID);

        ob_start();
        print_r($data);
        $ssd = ob_get_clean();
        
        file_put_contents('./Uploads/orderId.txt', $ssd);
        
        if(empty($data['out_trade_no'])) {
            echo 'ERROR'; die();
        }
       
        $orderId = substr($data['out_trade_no'],strpos($data['out_trade_no'], '-')+1);
        file_put_contents('./Uploads/orderId.txt', $orderId);
        if (!is_numeric($orderId)) {
            echo 'ERROR';die();
        }
        //获取订单状态
        
        $orderModel = BaseModel::getInstance(OrderModel::class);
        
        $ordersStatus = $orderModel->getOrderStatusByUser($orderId);
        $sql = $orderModel->getLastSql();
        file_put_contents('./Uploads/rty.txt', $sql);
        
        $status = false;
        if ($ordersStatus == 0)
        {
            //修改状态
            $status =  $orderModel->save(array(
                OrderModel::$orderStatus_d => OrderModel::YesPaid,
                OrderModel::$deliveryTime_d => time()
            ), array(
                'where' => array(OrderModel::$id_d => $orderId)
            ));
            
            $status = BaseModel::getInstance(OrderWxpayModel::class)->save(array(OrderWxpayModel::$status_d => 1), array('where' => array(
                OrderWxpayModel::$orderId_d => $orderId
            )));
        }
        echo $status ? "SUCCESS" : 'ERROR';
    }
    
    /**
     * pc 回调 
     */
    public function pcWxNofity ()
    {
        $orderId = $this->nofityWx();
        

        file_put_contents('./Uploads/order.txt', $orderId);
        $this->msg(is_numeric($orderId)) ;
        Hook::add('aplipaySerial', Decorate::class);

        $this->getPayIntegral();

        $status = $this->orderNotice($orderId);


        $this->msg($status);
        
        echo "SUCCESS";
        die();
                
    }
    
 
    /**
     * 异步通知
     */
    public function alipayNotify()
    {
        file_put_contents('./Log/alipay.txt', print_r($_POST, true));
//         $_POST =Array
//         (
//             'discount' => 0.00,
//             'payment_type' => 1,
//             'trade_no' => 2017122721001004070241895766,
//             'subject' => '订单商品支付',
//             'buyer_email' => 13052079525,
//             'gmt_create' => '2017-12-27 18:01:57',
//             'notify_type' => 'trade_status_sync',
//             'quantity' => 1,
//             'out_trade_no' => '201712271800292880310036-524',
//             'seller_id' => 2088702516840021,
//             'notify_time' => '2017-12-27 18:02:03',
//             'body' => 2,
//             'trade_status' => 'TRADE_SUCCESS',
//             'is_total_fee_adjust' => 'N',
//             'total_fee' => 0.01,
//             'gmt_payment' => '2017-12-27 18:02:03',
//             'seller_email' => '2665644@qq.com',
//             'price' => 0.01,
//             'buyer_id' => 2088122508167079,
//             'notify_id' => 'fe1a1bc283bc07206f1353c1091d251gji',
//             'use_coupon' => 'N',
//             'sign_type' => 'RSA',
//             'sign' => 'No3OzpSoofKh8Q1VmTNL3MLXXLJdfX2uBu8UVpRy2amnOxFrjaUhwA18a/cT14XYXvqvsf86vLoS1HbyIWiNiK3d65Oz9jnYLl0wLqwHIpXsXHlFf7nFJ3jW9dqQbB2AuudlZyJJIymvyKGVvuGIGrZYkN+/8fANb6SI2j710Jc='
//         );
        
        $data = $this->alipayResultParse();
        
        $this->promptParse($data, '验证失败', U('Order/order_myorder'));
        
        $orderSnId = $data['order_sn_id'];
        
        $orderId = substr(strrchr($orderSnId, '-'), 1); // 主键编号 确保唯一性;
        
        $this->tradeNo = $data['trade_no'];
        
        Hook::add('aplipaySerial', AlipaySerialNumber::class);

        $this->getPayIntegral();

        $status = $this->orderNotice($orderId);;
        
        if (!$status) {
            echo 'FAIL';die();
        }
        
        echo "SUCCESS";die();
    }
    
    /**
     * 支付宝同步回调
     */
    public function alipayReturn()
    {
        $this->assign('total_fee', $_GET['total_fee']);
        
        $orderId = substr(strrchr($_GET['out_trade_no'], '-'), 1); // 主键编号 确保唯一性;
        
        $this->paySuccess($orderId);
    }
    
    private function notifyHtml ($status, $orderId)
    {
        if ($status) {
            $this->paySuccess($orderId);
        } else {
            $this->assign('intnetTitle', '支付结果');
            $this->display('fail');
        }
    }
    
    
    public function checkOrderStatus($orderSnId)
    {
        $this->promptPjax($orderSnId, '订单号错误');
    
        $snId = substr(strrchr($orderSnId, '-'), 1); // 主键编号 确保唯一性
    
        $status = BaseModel::getInstance(OrderModel::class)->getUserNameById($snId, OrderModel::$orderStatus_d);
    
        $this->url = 'wxNofityByHTML';


        $this->payNotice($status, $snId);
    }
    
    /**
     * 微信支付通知页面
     * @param unknown $orderSnId
     * @param unknown $display
     */
    public function wxNofityByHTML($orderSnId, $display)
    {
        $this->parseOrder($orderSnId);
    
        $this->display($display);
    }
    
    /**
     * 不需要支付的回调
     * 兑换的积分商品,且运费没有
     */
    public function noNeedPay()
    {
        $data = I('GET.');
        $this->handleNotify($data);
        $this->paySuccess($data['order_sn_id']);
    }
    
 /**
     * 余额支付通知
     */
    public function balanceNofty()
    {
        $validate = [
            'id',
            'address_id'
        ];
        Tool::checkPost($_GET, [
            'is_numeric' => $validate
        ], true, $validate) ?: $this->error('参数错误');
        
        Hook::add('aplipaySerial', Balance::class);
        
        $orderId = $_GET['id'];

        $this->getPayIntegral();

        $status = $this->orderNotice($orderId);
        //余额变动短信提醒
        $this->sendBalanceSms();
        
        $this->notifyHtml($status, $orderId);
    }
    
    /**
     * 显示支付成功页面
     */
    private function paySuccess($sn_id)
    {
        $this->parseOrder($sn_id);
        
        $this->assign('payRelated', self::RELEVANT);
        
        $this->display('success');
    }

    /**
     * 支付成功处理
     * @param unknown $sn_id            
     */
    private function parseOrder($sn_id)
    {
        $orderModel = BaseModel::getInstance(OrderModel::class);
        $filed = [
            OrderModel::$id_d,
            OrderModel::$priceSum_d,
            OrderModel::$addressId_d
        ];
        
        $order = $orderModel->field($filed)
            ->where([
            OrderModel::$id_d => $sn_id
        ])
            ->find();

        $address = D('userAddress')->getAddrById($order['address_id']);
        $this->assign('intnetTitle', '支付成功');
        $this->assign('address', $address['addr_alone']);
        $this->assign('total_fee', $order['price_sum']);
        $this->assign('order_id', $order['id']);
    }

    /**
     * 获取积分比例
     */
    private function getPayIntegral()
    {
        $this->key = 'integral';
        $payIntegral = $this->getGroupConfig()['pay_integral'];
        $this->payIntegral = $payIntegral;
    }
    /**
     * 余额支付通知
     */
    private function sendBalanceSms()
    {
        if( M('sms_check')->where(['check_title' => '余额支付提示'])->getField('status')){
            $userPhone = M('user')->where(['id' => $_SESSION['user_id']])->getField('mobile');
            $sms = new MsmFactory();
            $sms->factory($userPhone,5);//5为 余额通知的短信模板
        }
    }
}