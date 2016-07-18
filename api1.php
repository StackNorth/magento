<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>获得全部的Magento Api方法 www.hellokeykey.com</title>
<style type="text/css">
.box1{ border:2px solid #000; margin-bottom:10px; padding:10px;}
.box1 .path{ margin-bottom:10px; border-bottom-style:dashed; border-bottom-color:#000000; border-bottom-width:1px;}
</style>
</head>
<body>
    <?php
    $client = new SoapClient('http://www.magento1.com/api/soap/?wsdl');
    $session = $client->login('admin', 'ac809ab74aa2ab3fc565760660deb104:nJZ5PlgmMiVEfNLufSEDsQQVrRsYna7n');
    $result = $client->resources($session);
    foreach($result as $k1=>$v1)
    {
        echo '<div class="box1"><dl>';
        echo '<dt>Title:</dt><dd>'.$v1[title].'</dd>';
        echo '<dt>Name:</dt><dd>'.$v1[name].'</dd>';
        echo '<dt>methods:</dt><dd>';
        if(count($v1[methods])){
            echo '<ul>';
            foreach($v1[methods] as $k2=>$v2)
            {
                echo '<li>Title:'.$v2[title].'</li>';
                echo '<li>Name:'.$v2[name].'</li>';
                echo '<li class="path">Path:'.$v2[path].'</li>';
            }
            echo '</ul></dd>';
        }
        echo '</dl></div>';
    }
    ?>
</body>
</html>