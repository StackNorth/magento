<?php
class D1m_Producttool_Adminhtml_producttoolController extends Mage_Adminhtml_Controller_Action
{
    protected function _initHelper()
    {
        return Mage::helper('d1m_producttool');
    }
    
    public function indexAction()
    {
//        die('1234');
        //$where2= $this->getRequest()->getModuleName().':'.$this->getRequest()->getControllerName();
        //$where3= $where2.':'.$this->getRequest()->getActionName();
        //echo $where3;
        // producttool:adminhtml_producttool:index
        //die();

        //loadLayout只能一次

        $this->loadLayout()->_setActiveMenu('etam/producttool');
        $this->getLayout()->getBlock('head')->setTitle($this->__("导入课程"));
        $this->_addContent($this->getLayout()->createBlock('d1m_producttool/adminhtml_producttool_edit'));

        //$this->_getSession()->addError($e->getMessage());
        //$obj=$this->getLayout()->getMessagesBlock();        $obj->addError('abc'); $obj->addError('abcd');
        //Mage_Core_Model_Message_Collection
        $this->renderLayout();
    }


    public function saveAction()
    {
        $step=$this->getRequest()->getParam('step','');
        if (($step=="") or ($step=='1'))
        {
            $data  =  $this->getRequest()->getPost();
            $pdata=$this->getRequest()->getParam('pdata','');
            if ($pdata=="")
            {
                $error='请输入要导入的内容';
                Mage::getSingleton('adminhtml/session')->addError($error);

                Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            }
            //检查数据

            //$ok='what is what';
            //Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__($ok));
            $arr=explode("\n",$pdata);
            for ($i=0;$i<count($arr);$i++)
            {
                $arr[$i]=str_replace("\r","",$arr[$i]);
                $arr[$i]=trim($arr[$i]);
            }
            //要求以tab分隔，多个tab当作一个tab
            //名称	课程类型	省份	课程日期	课程时间	课程地址	座位数	菜式	要求	描述	SKU	价格
            //暂时不支持厨师
            //第一行为标头
            $arrColumns=array('名称','课程类型','省份','课程日期','课程时间','课程地址','座位数','菜式','要求','描述','SKU','价格','门店');
            $arrRequire=array('名称','课程类型','省份','课程日期','课程时间','课程地址','座位数','菜式','SKU','价格');
            if (count($arr)<2)
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('至少有一行数据'));
                Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            }
            $line=$arr[0];
            $brr=explode("\t",$line);
            $crr=array();
            for ($i=0;$i<count($brr);$i++)
                if ($brr[$i]!="") $crr[]=$brr[$i];
            // for ($i=0;$i<count($crr);$i++)                echo $crr[$i]."<br>";            die();

            //$crr 列头
            $arrData=array();
            $arrData[]=$crr;

            $errC='';
            for ($i=0;$i<count($crr);$i++)
            {
                if (!in_array($crr[$i],$arrColumns))
                {
                    $errC=$crr[$i];
                    break;
                }
            }
            if ($errC!="")
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('不能识别的数据列:'.$errC));
                Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            }
            //必有列
            $j=0;
            for ($i=0;$i<count($crr);$i++)
            {
                if (in_array($crr[$i],$arrRequire)) $j=$j+1;
            }
            if ($j<count($arrRequire))
            {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('缺少必要的数据列'));
                Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            }

            //每行要有足够数据
            for ($i=1;$i<count($arr);$i++)
            {
                $line=$arr[$i];
                $line=trim($line);
                if ($line=="") continue;

                $brr=explode("\t",$line);

                $drr=array();
                for ($j=0;$j<count($brr);$j++)
                    if ($brr[$j]!="") $drr[]=$brr[$j];
                //数据行
                if  (count($drr)!=count($crr))
                {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('不匹配的数据行:'.$line));
                    Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                    $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                    return;
                }
                $arrData[]=$drr;
            }


            $arrv_coursetype=array();
            $arrv_coursetypeid=array();
            $attribute_code = 'coursetype';
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option)
            {
                if ($option['value'] != "")
                {
                    $arrv_coursetype[]=$option['label'];
                    $arrv_coursetypeid[]=$option['value'];
                }
            }
            $arrv_province=array();
            $arrv_provinceid=array();
            $attribute_code = 'province';
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option)
            {
                if ($option['value'] != "")
                {
                    $arrv_province[]=$option['label'];
                    $arrv_provinceid[]=$option['value'];
                }
            }

            $arrv_n_shop=array();
            $arrv_n_shopid=array();
            $attribute_code = 'n_shop';
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option)
            {
                if ($option['value'] != "")
                {
                    $arrv_n_shop[]=$option['label'];
                    $arrv_n_shopid[]=$option['value'];
                }
            }
            
            $arrv_western_cuisine =array();
            $arrv_westerncuisineid =array();
            $attribute_code = 'western_cuisine';
            $attribute = Mage::getModel('eav/config')->getAttribute('catalog_product',$attribute_code);
            $options = $attribute->getSource()->getAllOptions();
            foreach ($options as $option)
            {
                if ($option['value'] != "")
                {
                    $arrv_western_cuisine[]=$option['label'];
                    $arrv_westerncuisineid[] =$option['value'];
                }
            }

            $arrv_productname=array();
            $arrv_pid=array();
            $productResource = Mage::getResourceSingleton('catalog/product');
            $attr = $productResource->getAttribute('name');
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackend()->getTable();
            $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();
            $sql= 'SELECT  MAX(entity_id) as pid, value  FROM '.$attrTable.'   WHERE entity_type_id='.$entityTypeId.'  AND attribute_id='.$attrId.'     AND store_id=0  group by value';
            //echo $sql;
            $dbr = Mage::getSingleton ( 'core/resource' )->getConnection ( 'core_read' );
            $result = $dbr->fetchAll($sql);
            foreach($result as $item)
            {
                $arrv_productname[]=$item['value'];
                $arrv_pid[]=$item['pid'];
            }
            //pid暂时无用

            //要求产品名称存在
            //课程类型 省份 菜式
            //课程日期为 yyyy/mm/dd 或yyyy-mm-dd
            //课程时间为hh:mm-hh:mm
            //座位数为数字
            //价格为数字，去掉元
            $err='';
            $errline=0;
            for ($i=1;$i<count($arrData);$i++)
            {
                $line=$arrData[$i];
                $cc=count($line);
                for ($j=0;$j<$cc;$j++)
                {
                    $colname='';
                    $tocheck=trim($line[$j]);
                    for ($k=0;$k<count($arrColumns);$k++)
                        if ($arrColumns[$k]==$arrData[0][$j])
                        {
                            $colname=$arrColumns[$k];
                            break;
                        }


                    if ($colname=='名称')
                    {
                        $no=-1;
                        for ($k=0;$k<count($arrv_productname);$k++)
                        {
                            if ($tocheck== $arrv_productname[$k])
                            {
                                $no=$k;
                                $arrData[$i]['pid']=$arrv_pid[$k];
                                break;
                            }
                        }
                        if ($no==-1){
                            //$err='产品名称 '.$tocheck.' 不存在，若是新产品请先手工添加，否则请修改产品名称与原来的保持一致';
                            $arrData[$i]['name']=$tocheck;
                            $arrData[$i]['pid']= Mage::getModel('catalog/product')->getIdBySku('default');
                        }else
                        {
                            $arrData[$i]['name']=$tocheck;
                        }

                    }
                    else if ($colname=='课程类型')
                    {
                        $no=-1;
                        for ($k=0;$k<count($arrv_coursetype);$k++)
                        {
                            if ($tocheck== $arrv_coursetype[$k])
                            {
                                $no=$k;
                                $arrData[$i]['coursetypeid']=$arrv_coursetypeid[$k];
                                break;
                            }
                        }
                        if ($no==-1)
                            $err='课程类型 '.$tocheck.' 不存在，确认没有的请先在属性中定义';


                    }

                    else if ($colname=='菜式')
                    {
                        $no=-1;
                        for ($k=0;$k<count($arrv_western_cuisine);$k++)
                        {
                            if ($tocheck== $arrv_western_cuisine[$k])
                            {
                                $no=$k;
                                $arrData[$i]['westerncuisineid']=$arrv_westerncuisineid[$k];
                                break;
                            }
                        }
                        if ($no==-1)
                            $err='菜式 '.$tocheck.' 不存在，确认没有的请先在属性中定义';

                    }
                    else if ($colname=='省份')
                    {
                        $no=-1;
                        for ($k=0;$k<count($arrv_province);$k++)
                        {
                            if ($tocheck== $arrv_province[$k])
                            {
                                $no=$k;
                                $arrData[$i]['provinceid']=$arrv_provinceid[$k];
                                break;
                            }
                        }
                        if ($no==-1)
                            $err='省份 '.$tocheck.' 不存在，确认没有的请先在属性中定义';

                    }
                    else if ($colname=='门店')
                    {
                        $no=-1;
                        for ($k=0;$k<count($arrv_n_shop);$k++)
                        {
                            if ($tocheck== $arrv_n_shop[$k])
                            {
                                $no=$k;
                                $arrData[$i]['n_shopid']=$arrv_n_shopid[$k];
                                    break;
                            }
                        }
                        if ($no==-1)
                            $err='门店 '.$tocheck.' 不存在，确认没有的请先在属性中定义';
                    
                    }

                    else if ($colname=='课程日期')
                    {
                        //yyyy-mm-dd 或yyyy/mm/dd
                        $tocheck=str_replace('/','-',$tocheck);
                        $zrr=explode('-',$tocheck);
                        $zy=$zrr[0];
                        $zm=$zrr[1];
                        $zd=$zrr[2];
                        settype($zy,"integer");
                        settype($zm,"integer");
                        settype($zd,"integer");
                        $errline=$i;
                        if (($zm<1) or ($zm>12)) $err='课程日期错误';
                        if (($zd<1) or ($zd>31)) $err='课程日期错误';
                        if ($zy<2014)  $err='课程日期错误';

                        $arrData[$i]['classdate']=Date("Y-m-d",mktime(0,0,0,$zm,$zd,$zy ) ).' 00:00:00';

                    }
                    else if ($colname=='课程时间')
                    {
                        //mm:hh-mm:hh
                        if (!preg_match('/^[0-9]{2}:[0-9]{2}-[0-9]{2}:[0-9]{2}$/',$tocheck))
                        {
                            $err='课程时间错误';$errline=$i;
                        }
                        else
                        {
                            $arrData[$i]['nclasstime1']=substr($tocheck,0,5);
                            $arrData[$i]['nclasstime2']=substr($tocheck,6);

                        }

                    }
                    else if ($colname=='座位数')
                    {
                        settype($tocheck,'integer');
                        if ($tocheck<1) {$err='座位数错误';$errline=$i;}
                        $arrData[$i]['qty']=$tocheck;
                    }
                    else if ($colname=='SKU')
                    {
                        $arrData[$i]['sku']=$tocheck;
                    }

                    else if ($colname=='价格')
                    {
                        $tocheck=str_replace('元','',$tocheck);
                        settype($tocheck,'float');
                        if ($line[$j]<=0) {$err='价格错误';$errline=$i;}
                        $arrData[$i]['price']=$tocheck;
                    }
                    else if ($colname=='课程地址')
                    {
                        $arrData[$i]['classaddress']=$tocheck;
                    }
                    else if ($colname=='要求')
                    {
                        $arrData[$i]['requirement']=$tocheck;
                    }
                    else if ($colname=='描述')
                    {
                        $arrData[$i]['description']=$tocheck;
                    }
                    else if ($colname=='短描述')
                    {
                        $arrData[$i]['shortdescription']=$tocheck;
                    }

                    if ($err!="") break;

                }
                if ($err!="") break;


            }
            if ($err!="")
            {
                $errmsg=$err.': '.implode(' ',$arrData[$errline]);
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($errmsg));
                Mage::getSingleton('adminhtml/session')->setProducttoolData($data);
                $this->getResponse()->setRedirect($this->getUrl("*/*/"));
                return;
            }




             //var_dump($arrData);            die();
            //要先检查sku重复情况! todo 虽然可能性不大
            Mage::getSingleton('adminhtml/session')->setProducttoolTable($arrData);
            $this->getResponse()->setRedirect($this->getUrl("*/*/index/step/2"));

        }
        else if ($step=='2')
        {
            set_time_limit(0); //long time execute
            //准备导入
            $data=Mage::getSingleton('adminhtml/session')->getProducttoolTable();
            // var_dump($data);    die();

            //从已有产品中复制图片信息 另作

            $iok=0;
            $ierr=0;
            
             for ($i=1;$i<count($data);$i++)
             {
                /* @var $product Mage_Catalog_Model_Product */
                $product = Mage::getModel('catalog/product');
                $line=$data[$i];
                $coursetypeid=$line['coursetypeid'];
                $westerncuisineid=$line['westerncuisineid'];
                $provinceid=$line['provinceid'];
                $n_shopid=$line['n_shopid'];

                $requirement=$line['requirement'];
                $description=$line['description'];
                $shortdescription=$line['shortdescription'];
                $classaddress=$line['classaddress'];
                $nclasstime1=$line['nclasstime1'];
                $nclasstime2=$line['nclasstime2'];
                $classdate=$line['classdate'];
                 //2014-00-12 00:00:00
                 //16:00
                 //14:00
                 // echo $classdate; echo "<br>";                 echo $nclasstime1;echo "<br>";                 echo $nclasstime2;echo "<br>";                 die();
                $suffix=$classdate.$nclasstime1.$nclasstime2;
                $suffix=str_replace(' 00:00:00','',$suffix);
                $suffix=str_replace('-','',$suffix);
                $suffix=str_replace(':','',$suffix);

                $sku= Mage::helper('d1m_producttool')->getUniqueSku($line['sku'].$suffix);
                
                // $nclassno=$line['sku']; //课号以后再处理，同名课程是否分城市评论？

                $pname=$line['name'];
                $price=$line['price'];
                $qty=$line['qty'];


                $product->setid(0);
                $product->setTypeId(Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL)
                    ->setWebsiteIds(array(1))
                    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                    ->setTaxClassId(0)
                    ->setAttributeSetId( 4 )  //important
                    ->setData('coursetype',$coursetypeid)
                    ->setData('western_cuisine',$westerncuisineid)
                    ->setData('province',$provinceid)
                    ->setData('n_shop',$n_shopid)

                    ->setData('class_address',$classaddress)
                    ->setData('class_date',$classdate)
                    ->setData('n_classtime1',$nclasstime1)
                    ->setData('n_classtime2',$nclasstime2)
                    ->setData('seats',$qty) //痤位数
                    ->setSku($sku)
                    //->setCategoryIds(2)
                    // ->setIsMassupdate(true) 加速
                    // ->setExcludeUrlRewrite(true)
                    ->setName($pname)
                    ->setPrice($price);
                $product->setData('requirement',$requirement);
                $product->setDescription($description);
                $product->setShorttDescription($shortdescription);

                if($line['pid'] == Mage::getModel('catalog/product')->getIdBySku('default')){
                    $imageDir = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product/new/'.$line['name'];
                    if(file_exists($imageDir)){
                        $images = Mage::helper('d1m_producttool')->getDirFiles($imageDir);
                        foreach ($images as $imageFile){
                            $product->addImageToMediaGallery($imageFile,array('image','small_image','thumbnail'),false,false);
                        }
                    }else{
                        $imageFile = Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product/new/'.$line['name'].'.jpg';
                        if(file_exists($imageFile)){
                            $product->addImageToMediaGallery($imageFile,array('image','small_image','thumbnail'),false,false);
                        }
                    }
                }
                $product->setStockData(array( 'is_in_stock' =>1, 'qty' => $qty ));
                try
                {
                    $product->save();
                    //echo $product->getId();                    echo "<br>";
                    $iok++;
                }
                catch (exception $e)
                {
                    $ierr++;
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    //echo $e->getMessage();                    echo "<br>";

                }
                // break;  //for test add 1 record
             }

            if ($iok>0)
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("$iok 条数据添加成功"));
            if ($ierr>0)
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("$ierr 条数据添加失败"));
            $this->getResponse()->setRedirect($this->getUrl("*/*/index/step/3"));

        }


    }
    public function picAction()
    {
        //检查没有图片的产品，根据名称，复制图片


            set_time_limit(0); //long time execute
            $productResource = Mage::getResourceSingleton('catalog/product');
            $attr = $productResource->getAttribute('name');
            $attrId = $attr->getAttributeId();
            $attrTable = $attr->getBackend()->getTable();
            $entityTypeId = Mage::getSingleton('eav/config')->getEntityType('catalog_product')->getId();

            $resource = Mage::getSingleton('core/resource');
            $dbr = $resource->getConnection ( 'core_read' );
            $tableName1 = $resource->getTableName('catalog_product_entity');
            $tableName2 = $resource->getTableName('catalog_product_entity_media_gallery');
            $tableName3 = $resource->getTableName('catalog_product_entity_media_gallery_value');

            $sql='SELECT a.entity_id ,c.value FROM  '.$tableName1.' a, '.$attrTable.' c
               WHERE NOT EXISTS  (SELECT * FROM '.$tableName2.' b  WHERE a.entity_id=b.entity_id)
                 and a.entity_id=c.entity_id and c.store_id=0 and c.entity_type_id='.$entityTypeId.' and c.attribute_id='.$attrId
                  .' ORDER BY c.value';
            //取没有图片的产品id,名称
            $result = $dbr->fetchAll($sql);
            $arrpid=array();
            $arrpname=array();
            foreach($result as $item)
            {
                $arrpid[]=$item['entity_id'];
                $arrpname[]=$item['value'];
            }
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
            $iok=0;
            $ierr=0;
            for ($i=0;$i<count($arrpid);$i++)
            {

                //获取有图片的同名产品id
                $sql2='SELECT a.entity_id  FROM  '.$tableName1.' a, '.$attrTable.' c, '.$tableName2.' b
               WHERE c.value=\''.$arrpname[$i].'\'
                 and a.entity_id=b.entity_id
                 and a.entity_id=c.entity_id and c.store_id=0 and c.entity_type_id='.$entityTypeId.' and c.attribute_id='.$attrId
                    .' ORDER BY a.entity_id desc limit 0,1';


                $copypid = $dbr->fetchOne($sql2);
                // echo "pid=$pid";
                if ($copypid=="")
                {
                    if($arrpid[$i] != Mage::getModel('catalog/product')->getIdBySku('default')){
                        $msg= $arrpname[$i].'(编号'.$arrpid[$i].') 不存在图片，请在店铺视图默认模式下添加产品图片';
                        Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($msg));
                        echo $msg;echo "<br>";
                    }
                    continue;
                }

             /* @var $product Mage_Catalog_Model_Product */
             $product = Mage::getModel('catalog/product');
             $product->load($arrpid[$i]);
             if ($product->getId()!=$arrpid[$i]) continue; //not possible
             $product->setMediaGallery (array('images'=>array (), 'values'=>array ()));

              $sql2='SELECT  a.`entity_id`,b.`position`,a.value
            FROM '.$tableName2.' a,  '.$tableName3.' b
            WHERE a.value_id=b.value_id AND b.`store_id`=0 and a.entity_id='.$copypid.'
            ORDER BY a.`entity_id`,b.`position`,a.value ';


              $result2 = $dbr->fetchAll($sql2);
              if(!$result2){
                  $sql2='SELECT  a.`entity_id`,b.`position`,a.value
                    FROM '.$tableName2.' a,  '.$tableName3.' b
                    WHERE a.value_id=b.value_id AND b.`store_id`=2 and a.entity_id='.$copypid.'
                    ORDER BY a.`entity_id`,b.`position`,a.value ';
                  $result2 = $dbr->fetchAll($sql2);
              }
              if(!$result2){
                  $sql2='SELECT  a.`entity_id`,b.`position`,a.value
                    FROM '.$tableName2.' a,  '.$tableName3.' b
                    WHERE a.value_id=b.value_id AND b.`store_id`=1 and a.entity_id='.$copypid.'
                    ORDER BY a.`entity_id`,b.`position`,a.value ';
                  $result2 = $dbr->fetchAll($sql2);
              }
              $icount=0;
              foreach($result2 as $item)
              {
                $v=$item['value'];
                $fn=Mage::getBaseDir(Mage_Core_Model_Store::URL_TYPE_MEDIA).'/catalog/product/'.$v;
                if (!file_exists($fn)) continue;
                if ($icount == 0)
                   $product->addImageToMediaGallery($fn,array('image','small_image','thumbnail'),false,false);
                else
                    $product->addImageToMediaGallery($fn,array(),false,false);
                $icount=$icount+1;
              }

                //$product ->setIsMassupdate(true) 加速?
                //$product->setExcludeUrlRewrite(true)

              if ($icount>0)
                  try
                  {
                      $product->save();
                      $iok++;
                      $msg= $arrpname[$i].'(编号'.$arrpid[$i].') 添加图片成功!';
                      Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__($msg));
                  }
                  catch (exception $e)
                  {
                      $msg= $arrpname[$i].'(编号'.$arrpid[$i].') 添加图片失败!'.$e->getMessage();
                      Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__($msg));
                      $ierr++;
                  }
//             break;//for test only add one product

            }

        if ($iok>0)
            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__("$iok 个产品添加图片成功"));
        if ($ierr>0)
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__("$ierr 个产品添加图片失败"));

        $this->getResponse()->setRedirect($this->getUrl("*/*/index/step/pic"));


    }


}
