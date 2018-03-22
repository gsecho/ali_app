<?php

/**
 * 物流订单详细信息列表
 * @author auto create
 */
class AeopLogisticsOrderDetailDTO
{
	
	/** 
	 * 渠道编码
	 **/
	public $channel_code;
	
	/** 
	 * 创建时间
	 **/
	public $gmt_create;
	
	/** 
	 * 国际运单号
	 **/
	public $international_logistics_num;
	
	/** 
	 * 物流服务编码
	 **/
	public $international_logistics_type;
	
	/** 
	 * 运费
	 **/
	public $logistics_fee;
	
	/** 
	 * 物流订单号
	 **/
	public $logistics_order_id;
	
	/** 
	 * 物流订单状态
	 **/
	public $logistics_status;
	
	/** 
	 * outOrderCode(Lpnumber)
	 **/
	public $out_order_code;
	
	/** 
	 * 交易订单号
	 **/
	public $trade_order_id;	
}
?>