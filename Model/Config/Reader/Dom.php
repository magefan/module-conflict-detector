<?php
/**
 * Copyright © 2017 Ihor Vansach (ihor@magefan.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\ConflictDetector\Model\Config\Reader;

class Dom extends \Magento\Framework\ObjectManager\Config\Reader\Dom
{
    public function getClassRewriteConflicts()
    {
        $conflicts = array();

        foreach (['global', 'adminhtml', 'frontend'] as $scope) {
            $fileList = $this->_fileResolver->get($this->_fileName, $scope);
            if (count($fileList)) {
                foreach ($fileList as $key => $content) {
                    $dom = new \DOMDocument();
                    $res = $dom->loadXML($content);
                    if ($res) {
                        foreach ($dom->getElementsByTagName('preference') as $preference) {
                            $for = $preference->getAttribute('for');
                            $type = $preference->getAttribute('type');
                            if ($for && $type) {
                                if (!isset($conflicts[$for])) {
                                    $conflicts[$for] = [
                                        'classes' => []
                                    ];
                                }

                                if (!in_array($type, $conflicts[$for]['classes'])) {
                                    $conflicts[$for]['classes'][] = $type;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($conflicts as $origClass => $item) {
            $hasNoMagentoClasses = false;
            foreach ($item['classes'] as $class) {
                if (strpos($class, 'Magento\\') !== 0 && strpos($class, '\\Magento\\') !== 0) {
                   $hasNoMagentoClasses = true;
                }
            }

            if (!$hasNoMagentoClasses) {
                unset($conflicts[$origClass]);
            }

            if (strpos($origClass, 'Interface') !== false && count($item['classes']) < 2) {
                unset($conflicts[$origClass]);
            }
        }

        return $conflicts;
    }
}
