<?php

class KD_Quickconnect_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * View product action
     */
	protected function _initCms()
    {
        $pageId = $this->getRequest()->getParam('id', false);

        return $pageId;
    }
	 
	public function viewcmsAction()
    {
        if ($page = $this->_initCms()) {
			$this->loadLayout();
            $this->_initLayoutMessages('tag/session');
            $this->renderLayout();
        }
        else {
            if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }
	
	protected function _initKdProduct()
    {
        $productId = $this->getRequest()->getParam('id', false);

        return $productId;
    }
	
	public function viewproductAction()
    {
        if ($product = $this->_initKdProduct()) {
			$this->loadLayout();
            $this->_initLayoutMessages('tag/session');
            $this->renderLayout();
        }
        else {
            if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }
	
	public function viewcomproductAction()
    {
        if ($product = $this->_initKdProduct()) {
			$this->loadLayout();
            $this->_initLayoutMessages('tag/session');
            $this->renderLayout();
        }
        else {
            if (isset($_GET['store'])  && !$this->getResponse()->isRedirect()) {
                $this->_redirect('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $this->_forward('noRoute');
            }
        }
    }
}