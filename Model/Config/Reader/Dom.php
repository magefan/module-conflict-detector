<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

namespace Magefan\ConflictDetector\Model\Config\Reader;

class Dom extends \Magento\Framework\ObjectManager\Config\Reader\Dom
{
    public function getClassRewriteConflicts()
    {
        $conflicts = [];

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
                                    $conflicts[$for] = [];
                                }

                                if (!isset($conflicts[$for][$scope])) {
                                    $conflicts[$for][$scope] = [];
                                }
                                if (!in_array($type, $conflicts[$for][$scope])) {
                                    $conflicts[$for][$scope][] = $type;
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($conflicts as $for => $scopeClasses) {
            foreach ($scopeClasses as $scope => $classes) {
                $hasNoMagentoClasses = false;
                foreach ($classes as $class) {
                    if (strpos($class, 'Magento\\') !== 0 && strpos($class, '\\Magento\\') !== 0) {
                        $hasNoMagentoClasses = true;
                    }
                }

                if (!$hasNoMagentoClasses) {
                    unset($conflicts[$for][$scope]);
                }

                if ($scope != 'global') {
                    $isInGlobal = (isset($conflicts[$for]['global']) && count($conflicts[$for]['global']));
                    if (!$isInGlobal && count($classes) < 2) {
                        unset($conflicts[$for][$scope]);
                    }
                }

                if (strpos($for, 'Interface') !== false && count($classes) < 2) {
                    unset($conflicts[$for][$scope]);
                }
            }
        }

        $result = [];
        foreach ($conflicts as $for => $scopeClasses) {
            foreach ($scopeClasses as $scope => $classes) {
                if (!isset($result[$for])) {
                    $result[$for] = [
                        'classes' => []
                    ];
                }

                $result[$for]['classes'] = array_merge($result[$for]['classes'], $classes);
            }
        }

        return $result;
    }
}
