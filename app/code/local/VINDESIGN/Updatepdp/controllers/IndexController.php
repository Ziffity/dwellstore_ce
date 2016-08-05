<?php
class VINDESIGN_Updatepdp_IndexController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
    	
    	/*
    	 * Load an object by id 
    	 * Request looking like:
    	 * http://site.com/updatepdp?id=15 
    	 *  or
    	 * http://site.com/updatepdp/id/15 	
    	 */
    	/* 
		$updatepdp_id = $this->getRequest()->getParam('id');

  		if($updatepdp_id != null && $updatepdp_id != '')	{
			$updatepdp = Mage::getModel('updatepdp/updatepdp')->load($updatepdp_id)->getData();
		} else {
			$updatepdp = null;
		}	
		*/
		
		 /*
    	 * If no param we load a the last created item
    	 */ 
    	/*
    	if($updatepdp == null) {
			$resource = Mage::getSingleton('core/resource');
			$read= $resource->getConnection('core_read');
			$updatepdpTable = $resource->getTableName('updatepdp');
			
			$select = $read->select()
			   ->from($updatepdpTable,array('updatepdp_id','title','content','status'))
			   ->where('status',1)
			   ->order('created_time DESC') ;
			   
			$updatepdp = $read->fetchRow($select);
		}
		Mage::register('updatepdp', $updatepdp);
		*/

			
		$this->loadLayout();     
		$this->renderLayout();
    }
}