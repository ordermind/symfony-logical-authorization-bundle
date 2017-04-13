<?php

namespace Ordermind\LogicalAuthorizationBundle\Doctrine\Mapping\Driver;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver as BaseDriver;

class SimplifiedXmlDriver extends BaseDriver {
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        parent::loadMetadataForClass($className, $metadata);

        $xmlRoot = $this->getElement($className);
        if(isset($xmlRoot->{'logical-authorization'})) {
          $la_tree = array();
          foreach($xmlRoot->{'logical-authorization'} as $logicalAuthorizationElement) {

            print_r($logicalAuthorizationElement);
          }
        }

//         if ($xmlRoot->getName() == 'logical-authorization') {
//           echo "\nfound logical authorization\n";
//         }

//         echo "\nhej\n";
//
//         $element = $this->getElement($className);
//
//         if (!isset($element['fields'])) {
//             return;
//         }
//
//         foreach ($element['fields'] as $name => $fieldMapping) {
//             if (isset($fieldMapping['localizable'])) {
//                 $original = $metadata->getFieldMapping($name);
//                 $additional = ['localizable' => $fieldMapping['localizable']];
//                 $newMapping = array_merge($original, $additional);
//                 $metadata->fieldMappings[$newMapping['fieldName']] = $newMapping;
//             }
//         }
    }
}
