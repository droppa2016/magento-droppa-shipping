<?php

namespace Droppa\DroppaShipping\Setup;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();

        try {
            $installCustomTable = $setup->getConnection()->newTable(
                $setup->getTable('droppa_booking_object')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Auto incremented Primary Key'
            )->addColumn(
                'booking_id',
                Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Booking Object ID'
            )->setComment('Saves only the OID of every booking made');

            $setup->getConnection()->createTable($installCustomTable);
            $setup->endSetup();
        } catch (Zend_Db_Exception $e) {
            ObjectManager::getInstance()->get('Psr\Log\LoggerInterface')->info($e->getMessage());
        }
    }
}