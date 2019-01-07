<?php

namespace luya\admin\commands;

use Yii;
use yii\db\Query;
use luya\admin\helpers\I18n;
use luya\console\Command;

/**
 * Converts language-specific data
 *
 * @TODO: use universal primary keys
 *
 * @author Dzianis Jackievic <mail@miesta.by> 
 */
class LanguageConverterController extends Command
{
    /** @var int The number of rows in one select-update iteration */
    public $batchSize = 5000;

    private $_batchCount = 0;
    
    public function actionFromI18n($tableName, $field, $language, $emptyValue = '', $dbComponent = 'db')
    {
        while (true) {
            $rows = $this->getBatchRows($tableName, $field);
            if (empty ($rows)) break;
            
            array_walk ($rows, function(&$row) use ($field, $emptyValue, $language) {
                $row[$field] = I18n::decodeFindActive($row[$field], $emptyValue, $language);
            });
            $sql = $this->getUpdateSql($tableName, $field, $rows);
            Yii::$app->get($dbComponent)
                ->createCommand($sql)
                ->execute();
            $this->_batchCount++;
        }

        $this->outputSuccess("Convertion from I18n `$field` values in `$tableName` table performed successfully in {$this->_batchCount} batches");
    }

    private function getBatchRows($tableName, $field)
    {
        return (new Query())
            ->select(['id', $field])
            ->from($tableName)
            ->limit($this->batchSize)
            ->offset($this->_batchCount * $this->batchSize)
            ->all();
    }

    private function getUpdateSql($tableName, $field, array $updates)
    {
        $sql = 'INSERT INTO __t__ __c__ VALUES __v__ ON DUPLICATE KEY UPDATE __u__';
        $columnStr = "([[id]], [[$field]])";
        $values = [];
        foreach ($updates as $row) {
            $values[] = "({$row['id']}, '{$row[$field]}')";
        }
        $valueStr = implode (',', $values);
        $updateStr = "[[$field]] = VALUES($field)";
        return str_replace (
            ['__t__', '__c__', '__v__', '__u__'],
            ["{{%$tableName}}", $columnStr, $valueStr, $updateStr],
            $sql);
    }

    public function actionToI18n($tableName, $field, $language, $emptyValue = '', $dbComponent = 'db')
    {
        while (true) {
            $rows = $this->getBatchRows($tableName, $field);
            if (empty ($rows)) break;
        
            array_walk ($rows, function(&$row) use ($field, $emptyValue, $language) {
                $i18nValue = I18n::getDecodedInitialValue();
                $i18nValue[$language] = empty ($row[$field]) ? $emptyValue : $row[$field];
                $row[$field] = I18n::encode($i18nValue);
            });
            $sql = $this->getUpdateSql($tableName, $field, $rows);
            Yii::$app->get($dbComponent)
                ->createCommand($sql)
                ->execute();
            $this->_batchCount++;
        }
        $this->outputSuccess("Convertion to I18n `$field` values in `$tableName` table performed successfully in {$this->_batchCount} batches");
    }

    public function getBatchCount()
    {
        return $this->_batchCount;
    }
}
