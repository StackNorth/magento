<?php
class D1m_Core_Helper_Log extends Mage_Core_Helper_String
{
     //-----------------用户相关的日志
    //联合登录日志文件
    const CUSTOMER_CONNECT_LOG_FILE                                                   =       'connectlog.log';

    //----------------单独为VIP用户创建日志文件
    //官网用户找回卡号的日志
    const VIP_REISSUE_FORMATTED_LOG_FILE                                               =        'vip.reissue.formatted.log';
    // 官网普通用户升级成VIP
    const VIP_PROMOTION_LOG_FILE                                                       =        'vip.promotion.log';
    // CRM用户组升级的脚本
    const VIP_UPGRADE_GROUP_BASE_CRM_LOG_FILE                                          =        'vip.upgrade.customer.group.log';

    //VIP数据的同步DEBUG日志
    const VIP_SYNC_FROM_OLD_WEBSITE_LOG_FILE                                            =       'vip.sync.old.website.debug.log';

    //VIP账号绑定，用户中心操作
    const VIP_ACCOUNT_LOG_FILE                                                          =       'vip.account.log';
    const VIP_ERROR_LOG_FILE                                                            =       'vip.error.log';
    const WEBPOWER_SOAP_LOG_FILE                                                         =       'webpower.soap.log';


    //------------------信息发送相关的日志
    //手机短信消息发送的日志文件
    const SEND_SMS_MESSAGE_LOG_FILE                                                       =       'sms_send.log';
    //邮件消息发送相关的日志文件
    const SEND_MAIL_MESSAGE_LOG_FILE                                                      =        'email_send.log';
    //站内信相关的日志文件
    const SEND_MESSAGE_INBOX_LOG_FILE                                                     =         'message_inbox.log';



    //-----------------WEB SERVICE相关的日志，统一以SOA开头
    const SOA_BAOZUN_ERP_API_LOG_FILE                                                      =        'baozun_erp_api.log';

    //debug log
    const SOA_ARVATO_CRM_SOAP_LOG_PATH                                                     =        'graspcrm.log';

    const SOA_BAOZUN_ERP_VIP_LOG_FILE                                                       =        'baison_erp_erp_vip.log';

    const SOA_BAISON_ERP_ORDER_LOG_FILE                                                     =        'baison_order_send_erp.log';

    const SOA_BAISON_ERP_VIP_PROMOTION_STATUS_LOG_FILE                                   =         'baison_erp_vip_promotion_status.log';



    //-----------------优惠券相关的日志
    //生成优惠券的日志记录
    const  SALE_COUPON_ERROR_LOG_FILE                                                        =        'coupon-error.log';

    //优惠券发送相关的日志
    const SALE_COUPON_SEND_MESSAGE_LOG_FILE                                                 =         'coupon-send-message.log';

    //优惠券相关的日志  ：： 生日优惠券
    const SALES_COUPON_BIRTHDAY_LOG_FILE                                                     =         'coupon.birthday.log';

    //优惠券相关的日志  ：： 定时发送优惠券提醒信息
    const SALE_COUPON_CRON_SEND_MESSAGE_LOG_FILE                                            =         'coupon.cron.message.log';


}
