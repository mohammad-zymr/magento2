<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend\GroupPrice;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice
     */
    protected $_model;

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = $this->getMock(\Magento\Catalog\Helper\Data::class, ['isPriceGlobal'], [], '', false);
        $this->_helper->expects($this->any())->method('isPriceGlobal')->will($this->returnValue(true));

        $currencyFactoryMock = $this->getMock(
            \Magento\Directory\Model\CurrencyFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $storeManagerMock = $this->getMock(\Magento\Store\Model\StoreManagerInterface::class, [], [], '', false);
        $productTypeMock = $this->getMock(\Magento\Catalog\Model\Product\Type::class, [], [], '', false);
        $configMock = $this->getMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $localeFormatMock = $this->getMock(\Magento\Framework\Locale\FormatInterface::class, [], [], '', false);
        $groupManagement = $this->getMock(\Magento\Customer\Api\GroupManagementInterface::class, [], [], '', false);
        $scopeOverriddenValue = $this->getMock(
            \Magento\Catalog\Model\Attribute\ScopeOverriddenValue::class,
            [],
            [],
            '',
            false
        );
        $this->_model = $this->getMockForAbstractClass(
            \Magento\Catalog\Model\Product\Attribute\Backend\GroupPrice\AbstractGroupPrice::class,
            [
                'currencyFactory' => $currencyFactoryMock,
                'storeManager' => $storeManagerMock,
                'catalogData' => $this->_helper,
                'config' => $configMock,
                'localeFormat' => $localeFormatMock,
                'catalogProductType' => $productTypeMock,
                'groupManagement' => $groupManagement,
                'scopeOverriddenValue' => $scopeOverriddenValue
            ]
        );
        $resource = $this->getMock(\StdClass::class, ['getMainTable']);
        $resource->expects($this->any())->method('getMainTable')->will($this->returnValue('table'));

        $this->_model->expects($this->any())->method('_getResource')->will($this->returnValue($resource));
    }

    public function testGetAffectedFields()
    {
        $valueId = 10;
        $attributeId = 42;

        $attribute = $this->getMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['getBackendTable', 'isStatic', 'getAttributeId', 'getName', '__wakeup'],
            [],
            '',
            false
        );
        $attribute->expects($this->any())->method('getAttributeId')->will($this->returnValue($attributeId));
        $attribute->expects($this->any())->method('isStatic')->will($this->returnValue(false));
        $attribute->expects($this->any())->method('getBackendTable')->will($this->returnValue('table'));
        $attribute->expects($this->any())->method('getName')->will($this->returnValue('tear_price'));
        $this->_model->setAttribute($attribute);

        $object = new \Magento\Framework\DataObject();
        $object->setTearPrice([['price_id' => 10]]);
        $object->setId(555);

        $this->assertEquals(
            [
                'table' => [
                    ['value_id' => $valueId, 'attribute_id' => $attributeId, 'entity_id' => $object->getId()]
                ]
            ],
            $this->_model->getAffectedFields($object)
        );
    }
}
