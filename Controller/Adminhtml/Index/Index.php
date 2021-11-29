<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\ConflictDetector\Controller\Adminhtml\Index;

/**
 * ConflictDetector list controller
 */
class Index extends \Magento\Backend\App\Action
{
     /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magefan_ConflictDetector::elements';

    /**
     * Action execute
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magefan_ConflictDetector::elements');
        $title = __('Conflict Detector');
        $this->_view->getPage()->getConfig()->getTitle()->prepend($title);
        $this->_addBreadcrumb($title, $title);
        $this->_view->renderLayout();
    }
}
