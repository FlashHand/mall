<h4>----------请求报文-------------------</h4>

<h4>1. 支付商户直接上送卡号并在银联页面锁定该卡号支付：</h4>
<p class="faq">
1. 需联系业务部门申请控件锁卡功能，如业务人员不清楚，可明确告知为管理平台“接收商户共享信息”权限。
2. 需上送accNo<br>
  'accNo' => '6221558812340000',  //需锁定的卡号<br>
</p>

<h4>2. 透传字段</h4>
<p class="faq">
  'reqReserved' =>'透传信息', //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自 己希望透传的数据<br>
</p>

<h4>-------------通知报文---------------------</h4>
<h4>3. 返回报文中返回卡号，卡类型，支付方式:</h4>
<p class="faq">
需在申请入网或者已经入网后给银联业务运营中心申请开通这3种权限<br>

</p>
