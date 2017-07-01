<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\ConflictDetector\Block\Adminhtml;

/**
 * ConflictDetector list
 */
class ConflictList extends \Magento\Backend\Block\Template
{
    /**
     * Template file
     * @var string
     */
    protected $_template = 'Magefan_ConflictDetector::list.phtml';

    /**
     * \Magefan\ConflictDetector\Model\Config\Reader\Do
     */
    protected $dom;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectmanager;

    /**
     * @var array
     */
    protected $objects = [];

    /**
     * @param \Magefan\ConflictDetector\Model\Config\Reader\Dom
     * @param \Magento\Framework\ObjectManagerInterface
     * @param \Magento\Backend\Block\Template\Context
     * @param array
     */
    public function __construct(
        \Magefan\ConflictDetector\Model\Config\Reader\Dom $dom,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->dom = $dom;
        $this->objectManager = $objectmanager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information about class conflicts
     * @return array
     */
    public function getClassRewriteConflicts()
    {
        $conflicts = $this->dom->getClassRewriteConflicts();

        foreach ($conflicts as $origClass => $item) {
            $classes = array_reverse($item['classes']);
            $originalObject = $this->getObject($origClass);

            $origStatus = 0;

            foreach ($classes as $i => $class) {
                $object = $this->getObject($class);
                $objClass = get_class($object);

                if (get_class($object) == get_class($originalObject)) {
                    $status = 1; //it's OK
                } elseif ($originalObject instanceof $objClass) {
                    $status = 2; //it's extender
                } else {
                    $status = 3; //problem
                }

                $classes[$i] = [
                    'class' => $class,
                    'object' => $this->getObject($class),
                    'status' => $status
                ];

                if ($origStatus < $status) {
                    $origStatus = $status;
                }
            }

            $conflicts[$origClass]['classes'] = $classes;
            $conflicts[$origClass]['status'] = $origStatus;
        };

        uasort($conflicts, array($this, 'sortConflicts'));

        return $conflicts;
    }

    /**
     * @param  int $status
     * @return string
     */
    public function getStatusColor($status)
    {
        switch ($status) {
            case 1 :
                return 'green';
            case 2 :
                return 'grey';
            default :
                return 'red';
        }
    }

    /**
     * @param  int $status
     * @return string
     */
    public function getStatusClass($status)
    {
        switch ($status) {
            case 1 :
                return 'notice';
            case 2 :
                return 'notice';
            default :
                return 'critical';
        }
    }

    /**
     * @param  int $status
     * @return string
     */
    public function getStatusLabel($status)
    {
        switch ($status) {
            case 1 :
                return __('No');
            case 2 :
                return __('Resolved');
            default :
                return __('Yes');
        }
    }

    /**
     * @param  string $class
     * @return mixed
     */
    protected function getObject($class)
    {
        if (!isset($this->objects[$class])) {
            $this->objects[$class] = $this->objectManager->create($class);
        }

        return $this->objects[$class];
    }

    /**
     * @param  array $a
     * @param  array $b
     * @return bool
     */
    protected function sortConflicts($a, $b)
    {
        return $a['status'] <= $b['status'];
    }
}
