<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
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

            $origStatus = 0;

            if (count($classes) == 1) {
                if ($this->isParentClass($classes[0], $origClass)) {
                    $status = 1; //it's OK
                } else {
                    $status = 3;
                }

                $classes[0] = [
                    'class' => $classes[0],
                    'status' => $status
                ];
                $origStatus = $status;
            } else {
                foreach ($classes as $i => $class) {
                    if (!$i) {
                        $status = 1;
                    } else {
                        if ($this->isParentClass($classes[0]['class'], $class)) {
                            $status = 2;
                        } else {
                            $status = 4;
                        }
                    }

                    $classes[$i] = [
                        'class' => $class,
                        'status' => $status
                    ];

                    if ($origStatus < $status) {
                        $origStatus = $status;
                    }
                }
            }

            $conflicts[$origClass]['classes'] = $classes;
            $conflicts[$origClass]['status'] = $origStatus;
        };

        uasort($conflicts, [$this, 'sortConflicts']);

        return $conflicts;
    }

    /**
     * Check if child class extends from parent class
     * @param  string  $childClass
     * @param  string  $parentClass
     * @return boolean
     */
    protected function isParentClass($childClass, $parentClass)
    {
        $parentClass1 = $this->normilizeClass($parentClass);
        $parentClass2 = $this->normilizeClass($parentClass . 'Interface');

        try {
            $classes = class_parents($childClass);
        } catch (\Exception $e) {
            $classes = [];
        }

        foreach ($classes as $class) {
            $class = $this->normilizeClass($class);
            if ($class == $parentClass1 || $class == $parentClass2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add "\" at the beginning of the class name if missing
     * @param  [type] $class [description]
     * @return [type]        [description]
     */
    protected function normilizeClass($class)
    {
        if ($class && $class{0} != '\\') {
            $class = '\\' . $class;
        }

        return $class;
    }

    /**
     * @param  int $status
     * @return string
     */
    public function getStatusColor($status)
    {
        switch ($status) {
            case 1:
                return 'green';
            case 2:
                return 'grey';
            case 3:
                return 'darkorange';
            default:
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
            case 1:
                return 'notice';
            case 2:
                return 'notice';
            case 3:
                return 'notice';
            default:
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
            case 1:
                return __('No');
            case 2:
                return __('Resolved');
            case 3:
                return __('No');
            default:
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
