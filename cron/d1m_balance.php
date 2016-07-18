<?php
/**
 * User: ahsw@qq.com
 * caeate Time: 2016/6/2714:22
 */
chdir(dirname(__FILE__));
require '../app/Mage.php';

if (!Mage::isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit;
}
$logFile = 'balance.log';
$_SERVER['SCRIPT_NAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_NAME']);
$_SERVER['SCRIPT_FILENAME'] = str_replace(basename(__FILE__), 'index.php', $_SERVER['SCRIPT_FILENAME']);

Mage::app('admin')->setUseSessionInUrl(false);

$currMonth = Mage::getModel('core/date')->gmtDate('Y-m-01');
$currMonthDayNum = date('t', strtotime($currMonth));
$currMonthOver = Mage::getModel('core/date')->gmtDate('Y-m-' . $currMonthDayNum . ' 23:59:59');
$now = Mage::getModel('core/date')->gmtDate();

$balanceModel = Mage::getModel('d1m_credits/balance');

$collection = $balanceModel->getCollection()->addFieldToFilter('created_date', $currMonth);

$collection->getSelect()->order('main_table.id DESC')->limit(1);
//$resource = Mage::getSingleton('core/resource');
//$orderModel=Mage::getModel('sales/order');

if ($collection->getSize() < 1) {
    $userModel = Mage::getModel('customer/customer')->getCollection();
    $userList = $userModel->getSelect()->order('entity_id ASC');
    $userList = $userModel->getData();
    foreach ($userList as $user) {
        $orderModel = Mage::getModel('sales/order')->getCollection();
        $orderModel->addFieldToFilter('customer_id', $user['entity_id']);
        $orderModel->addFieldToFilter('updated_at', array('from' => $currMonth, 'to' => $currMonthOver));
        $orderModel->getSelect()->where(
            "(`status` = 'complete' AND order_sign = '0') OR (`status` ='refund')"
        );
        $customerCredit = Mage::getModel('d1m_credits/credits')->getCustomerCredits($user['entity_id']);
        $userOrderMoney = 0;
        if ($orderModel->getSize() > 0) {
            $orderList = $orderModel->getData();
            foreach ($orderList as $order) {
                $userOrderMoney += $order['financial_money'];
            }
        }
        $currMonthMoney = $customerCredit + $userOrderMoney;
        if ($currMonthMoney) {
            $balanceDb = Mage::getModel('d1m_credits/balance');
            $balanceDb->setUid($user['entity_id'])
                ->setOrderMoney($userOrderMoney)
                ->setCredits($customerCredit)
                ->setCreatedDate($currMonth)
                ->save();

        }

    }

}

Mage::log($now, 7, $logFile);

//print_r($collection->getData());
//$collection->getData();
echo 'ok';
// print_r($item);
?>