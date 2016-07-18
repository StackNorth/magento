<?php
class D1m_Course_Block_Calendar extends Mage_Core_Block_Template
{
// course/index/newcalendar?py=2014&pm=10&pcat=12&province=12

    public function dowork()
    {

        //$attributes = $product->getAttributes();
        // options = $attr->getSource()->getAllOptions();
        // $_product->getResource()->getAttribute('color')->getFrontend()->getValue($_product);
        /* @var $model  D1m_Course_Model_Mysql4_Course_Collection */
        $model=Mage::getResourceModel('d1m_course/course_collection');
        $province=$this->getRequest()->getParam('province','12');
        $model->addAttributeToFilter('province',$province);
        
        $n_shop=$this->getRequest()->getParam('n_shop');

//        $model->addAttributeToSelect('coursetype');
        $attr=$model->getAttribute('coursetype');
        //var_dump($attr);
        $options = $attr->getSource()->getAllOptions();
        $collections1 = new Varien_Data_Collection();
        $no=1;
        $arrmap=array();
        foreach ($options as $option)
        {
            if ($option['value']=="") continue;
            $obj=new Varien_Object();
            $obj->setData('no',$no);
            $obj->setData('label',$option['label']);
            $obj->setData('value',$option['value']);
            $collections1->addItem($obj);
            $value=$option['value']; //int
            $arrmap[$value]=$no;
            $no++;
            // echo $option['label'];            echo $option['value'];            echo "<br>";
        }

// var_dump($arrmap);die();

        //接受参数py=2014&pm=10&province=12&pcat=分类
        $pcat=$this->getRequest()->getParam('pcat','');
        if (strtolower($pcat)=='all') $pcat='';

        $py=$this->getRequest()->getParam('py',date("Y"));
        $pm=$this->getRequest()->getParam('pm',date("n"));
        settype($py,'integer');
        settype($pm,'integer');

        $yy=Date("Y");
        $mm=Date("n");
        if ($py<1900) $py=$yy;
        if (($pm<1) or ($pm>12)) $pm=$mm;

        $yy=$py;
        $mm=$pm;
        $dd=1; //1号开始排

        $dayofweek1=Date("N" ,mktime(0,0,0,$mm,1,$yy));//1-7 当天星期几
        $time1=mktime(0,0,0,$mm,$dd - $dayofweek1 +1,$yy);

        $days=Date("t", mktime(0,0,0,$mm,1,$yy) );//当月天数
        $lastdaytime=mktime(0,0,0,$mm,$days,$yy);
        //最后一天星期几
        $dayofweek2=Date("N",$lastdaytime);
        if ($dayofweek2<7)
            $time2=mktime(0,0,0,$mm,$days+7-$dayofweek2,$yy);
        else
            $time2=$lastdaytime;
        $fromDate=Date("Y-m-d",$time1);
        $toDate=Date("Y-m-d",$time2);
        // die($fromDate.' '.$toDate);
        $courses =  Mage::getResourceModel('d1m_course/course_collection')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('coursetype')
            ->addAttributeToSelect('province')
            ->addAttributeToSelect('class_date')
            ->addAttributeToSelect('n_classtime1')
            ->addAttributeToSelect('n_classtime2');
        //->addStoreFilter()
        $courses->addFieldToFilter('class_date', array("gteq"=>$fromDate));
        $courses->addFieldToFilter('class_date', array("lteq"=>$toDate));
        $province=$this->getRequest()->getParam('province');

        //接受参数province
        if($province)
        {
            $courses->addFieldToFilter('province', $province);
        }
        if($n_shop){
            $courses->addAttributeToFilter('n_shop',$n_shop);
        }
        $courses->setOrder('class_date','asc');
        $courses->setOrder('n_classtime1','asc');
        // foreach ($courses as $c)   echo $c->getAttributeText('coursetype');            echo $c->getCoursetype(); //14            echo "<br>";
        $arrdata=array();
        foreach ($courses as $c)
        {
            // var_dump($c);            die();
            $brr=array();
            $brr['date']=substr($c->getClassDate(),0,10); //without  00:00:00
            $brr['city']=$c->getProvince();
            $brr['time1']=$c->getNClasstime1();
            $brr['coursetype']=$c->getAttributeText('coursetype');
            $v=$c->getCoursetype();

            $brr['cat']=$v;
            $brr['coursetypeclass']=$arrmap[$v];
            $brr['coursename']=$c->getName();
            $arrdata[]=$brr;
        }
        
        //   var_dump($arrdata);        die();
        //$fromDate $toDate 总共行数
        $collections2 = new Varien_Data_Collection();
        $total = round(($time2 - $time1)/3600/24)+1;//天数
        for ($i=1;$i<=$total;$i++)
        {
            $delta=86400*($i-1);
            $y2=date("Y",$time1+$delta);
            $m2=date("n",$time1+$delta);
            $d2=date("j",$time1+$delta);
            $curdate=date("Y-m-d",mktime(0,0,0,$m2,$d2,$y2) );

            $obj=new Varien_Object();
            $obj->setData('dayno',$d2);
            $obj->setData('am','');
            $obj->setData('pm','');
            $obj->setData('date',$curdate);
            $obj->setData('n_shop',$n_shop);
            $obj->setData('amcoursees', array());
            $obj->setData('pmcoursees', array());


            if ($m2==$mm)
                $obj->setData('is_this_month',1);
            else
                $obj->setData('is_this_month',0);
            for ($j=0;$j<count($arrdata);$j++)
            {
                $brr=$arrdata[$j];

                $obj->setData('city',$brr['city']);


                if ($brr['date']!=$curdate) continue;


                if ($pcat!="")
                    if ($brr['cat']!=$pcat) continue; //某一分类的

                // var_dump($brr);                die();
                if ($obj->getData('am')=="")
                {
                    $obj->setData('am','1');
                    $obj->setData('am_coursetype',$brr['coursetype']);
                    $v=$brr['cat'];
                    $obj->setData('am_coursetypeclass',$arrmap[$v]);
                    $obj->setData('amcoursename',$brr['coursename']);
                }
                else   if ($obj->getData('pm')=="")
                {
                    $obj->setData('pm','1');
                    $obj->setData('pm_coursetype',$brr['coursetype']);
                    $v=$brr['cat'];
                    $obj->setData('pm_coursetypeclass',$arrmap[$v]);
                    $obj->setData('pmcoursename',$brr['coursename']);
                }
                
                if(isset($brr['time1']) && $brr['time1']){
                    $hours = explode(':', $brr['time1']);
                    if($hours[0] <= 12){
                        $amcoursees = $obj->getData('amcoursees');
                        array_push($amcoursees, $brr);
                        $obj->setData('amcoursees', $amcoursees);
                    }else{
                        $pmcoursees = $obj->getData('pmcoursees');
                        array_push($pmcoursees, $brr);
                        $obj->setData('pmcoursees', $pmcoursees);
                    }
                }
                //else do nothing
            }
            $collections2->addItem($obj);
        }
        Mage::register('d1m_calender_arrCoursetype', $collections1);
        Mage::register('d1m_calender_arrDay', $collections2);
    }

    public function getArrCoursetype()
    {
             //if (! Mage::registry('d1m_calender_arrCoursetype') ) $this->dowork();
            return Mage::registry('d1m_calender_arrCoursetype');
    }

    public function getArrDay()
    {
         //if (! Mage::registry('d1m_calender_arrDay') ) $this->dowork();
        return Mage::registry('d1m_calender_arrDay');
    }




}