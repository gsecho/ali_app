<?php

/**
 * 退款信息
 * @author auto create
 */
class TpOpenRefundInfoDto
{
	
	/** 
	 * 退款现金金额(不包括coupon)
	 **/
	public $refund_cash_amt;
	
	/** 
	 * 退款coupon金额
	 **/
	public $refund_coupon_amt;
	
	/** 
	 * 退款原因
	 **/
	public $refund_reason;
	
	/** 
	 * 退款状态
	 **/
	public $refund_status;
	
	/** 
	 * 退款时间
	 **/
	public $refund_time;
	
	/** 
	 * 退款类型
	 **/
	public $refund_type;	
}
?>