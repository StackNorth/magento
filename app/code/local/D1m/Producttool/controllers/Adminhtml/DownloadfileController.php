<?php

class D1m_Producttool_Adminhtml_DownloadfileController extends Mage_Adminhtml_Controller_action
{

    /**
     * Export customer grid to CSV format
     */
    public function exportCsvAction()
    {
        $fileName   = 'd1m_producttool_downloadfile.csv';
        $content    = $this->getLayout()->createBlock('d1m_producttool/adminhtml_downloadfile_grid')
            ->getCsvFile();
// die('abcdef');
        $this->_prepareDownloadResponse($fileName, $content);
    }

    /**
     * Export customer grid to XML format
     */
    public function exportXmlAction()
    {
        $fileName   = 'd1m_producttool_downloadfile.xml';
        $content    = $this->getLayout()->createBlock('d1m_producttool/adminhtml_downloadfile_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }


    protected function _initAction() {

		$this->loadLayout()
            ->_setActiveMenu('etam/producttool');
		return $this;
	}   
 
	public function indexAction()
    {


        $this->_initAction();
        $this->_initLayoutMessages('adminhtml/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__("下载管理"));

        $block = $this->getLayout()->createBlock(
            'd1m_producttool/adminhtml_downloadfile',
            'producttool.downloadfile.list'
        );

         $this->getLayout()->getBlock('content')->append($block);
        $this->renderLayout();
//		$this->_initAction()	->renderLayout();
	}

	public function editAction()
    {
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('d1m_producttool/downloadfile')->load($id);

		if ($model->getId() || $id == 0)
        {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if ( ($data!=NULL) )
            {
				$model->setData($data);
			}

            if ($id==0)
                if ($data==NULL)
                {
                    $d1m_homeurl=Mage::helper('core/url')->getHomeUrl();
                    //remove index.php/
                    $d1m_homeurl=substr($d1m_homeurl,0,strlen($d1m_homeurl)-strlen('index.php/'));
                    $model->setData('furl',$d1m_homeurl);
                    $model->setData('fdownloadfile','1'); // 非数据库变量初始值要重新设置一下，否则abcd置为空了！
                }


			Mage::register('d1m_producttool_downloadfile_data', $model);  // 这句改变了！abcd

			$this->loadLayout();
            $this->_setActiveMenu('etam/producttool');

			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('d1m_producttool/adminhtml_downloadfile_edit'))
			 ->_addLeft($this->getLayout()->createBlock('d1m_producttool/adminhtml_downloadfile_edit_tabs'));

			$this->renderLayout();
		}
        else
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('d1m_producttool')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}
 
	public function newAction() {
		$this->_forward('edit');
	}
 
	public function saveAction()
    {
        $data = $this->getRequest()->getPost();
        if (!$data) return ;
        //var_dump($_FILES['fupload']);        die();
        // array(5) { ["name"]=> string(19) "s000006-detail1.jpg" ["type"]=> string(10) "image/jpeg" ["tmp_name"]=> string(49) "C:\Users\Suyunjing\AppData\Local\Temp\php4B81.tmp" ["error"]=> int(0) ["size"]=> int(723166) }
        // echo Mage::getBaseDir('media').'/d1m/downloadfile/'; die();
        try
        {

            if ($data['pname']=="")  Mage::throwException('请输入课程名称');
            $id=$this->getRequest()->getParam('id',0);
            settype($id,"integer");
            if ($id<=0)
            {


                if (  (!isset($_FILES['fupload']['name'])) ||  ($_FILES['fupload']['name'] == '') )                    Mage::throwException('请上传文件');
                $_fpath=Mage::getBaseDir('media').'/d1m/course_download/';
                @mkdir($_fpath,0x777,true);
                if (!is_dir($_fpath)) Mage::throwException('不能创建对应的目录');
                //ensure _fpath with / !!!
                if (substr($_fpath,-1,1)!='/') $_fpath=$_fpath.'/';
                //try to save data now
                /* @var $model D1m_Producttool_Model_Downloadfile */
                $model = Mage::getModel('d1m_producttool/downloadfile');
                $model->setData($data)
                    ->setId(null)
                    ->setData('fname',$_FILES['fupload']['name']);
                $model->save();
                $newid=$model->getId();
                copy( $_FILES['fupload']['tmp_name'],$_fpath.$newid);

            }
            else
            {
                //检查id
                /* @var $model D1m_Producttool_Model_Downloadfile */
                $model = Mage::getModel('d1m_producttool/downloadfile');
                $model->load($id);
                if ($model->getId()!=$id) Mage::throwException('对应id不存在');
                $model->setData($data);
                $model->setId($id);


                $tmp=$_FILES['fupload']['tmp_name'];
                if ($tmp!="")
                    $model ->setData('fname',$_FILES['fupload']['name']);
                $model->save();

                if ($tmp!="")
                {
                    $_fpath=Mage::getBaseDir('media').'/d1m/course_download/';
                    @mkdir($_fpath,0x777,true);
                    copy( $_FILES['fupload']['tmp_name'],$_fpath.$id);
                }

            }



                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('d1m_producttool')->__('保存成功'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back'))
                {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;



        }
        catch (Exception $e)
        {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setFormData($data);
            $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

	}
 
	public function deleteAction()
    {
        $id=$this->getRequest()->getParam('id','');
        settype($id,"integer");
		if( $id> 0 )
        {
			try
            {
				$model = Mage::getModel('d1m_producttool/downloadfile');
				$model->setId($id)
					->delete();
				//删除对应的文件
                $fn=Mage::getBaseDir('media').'/d1m/course_download/'.$id;
                @unlink($fn);
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('删除操作成功'));
				$this->_redirect('*/*/');
			}
            catch (Exception $e)
            {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}

    public function massDeleteAction() {
        $logIds = $this->getRequest()->getParam('downloadfile_id');
        if(!is_array($logIds))
        {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('请选取要删除的项目'));
        } else {
            try
            {
                foreach ($logIds as $logId)
                {
                    settype($logId,"integer");
                    $log = Mage::getModel('d1m_producttool/downloadfile')->load($logId);
                    $log->delete();
                    $fn=Mage::getBaseDir('media').'/d1m/course_download/'.$logId;
                    @unlink($fn);


                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($logIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }

    public function downloadAction()
    {

        $id=$this->getRequest()->getParam('id');
        settype($id,"integer");
        if ($id<=0) return ;
        $model  = Mage::getModel('d1m_producttool/downloadfile')->load($id);
        if ($model->getId()<=0) return ;
        $fname=$model->getData('fname');
        $fname=urlencode($fname);

        $fn=Mage::getBaseDir('media').'/d1m/course_download/'.$id;
        if (!file_exists($fn))
        {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('d1m_producttool')->__('文件不存在'));
            $this->_redirect('*/*/');
            return;
        }
        $content=array('type'=>'filename','value'=>$fn);
        $this->_prepareDownloadResponse($fname, $content);
    }
	

}