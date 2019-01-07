<?php

namespace admintests\admin\commands;

use luya\admin\helpers\I18n;
use luya\admin\commands\LanguageConverterController;
use luya\admin\models\User;
use luya\console\Application;
use admintests\data\models\I18nUser;
use admintests\data\fixtures\I18nUserFixture;
use admintests\data\fixtures\UserFixture;

use yii\db\Query;

class LanguageConverterTest extends \admintests\AdminTestCase
{
    public function testFromI18nAction_i18nUserFirstname_allFirstnameValuesNonI18n()
    {
        $fixture = new I18nUserFixture();
        $fixture->load();
        $app = new Application($this->getConfigArray());
        $adminLangMock = $this->getAdminLanguageMock();
        $adminLangMock
            ->method('getActiveShortCode')
            ->willReturn('lang1');
        $app->set('adminLanguage', $adminLangMock);
        $ctrlMock = $this->getMockBuilder(LanguageConverterController::className())
                  ->setConstructorArgs(['language-converter', $app])
                  ->setMethods(['outputSuccess', 'outputError'])
                  ->getMock();

        $ctrlMock->actionFromI18n(User::tableName(), 'firstname', 'lang1');

        $users = (new Query)
               ->select(['id', 'firstname'])
               ->from(User::tableName())
               ->all();
        $this->assertNotEmpty($users);
        $expectedNames = [
            1 => 'John',
            2 => 'Jane',
        ];
        foreach ($users as $user) {
            $this->assertSame($expectedNames[$user['id']], $user['firstname']);
        }
    }

    public function testFromI18nAction_outputSuccessOnceNeverOuputError()
    {
        $fixture = new I18nUserFixture();
        $fixture->load();
        $app = new Application($this->getConfigArray());
        $adminLangMock = $this->getAdminLanguageMock();
        $adminLangMock
            ->method('getActiveShortCode')
            ->willReturn('lang1');
        $app->set('adminLanguage', $adminLangMock);
        $ctrlMock = $this->getMockBuilder(LanguageConverterController::className())
                  ->setConstructorArgs(['language-converter', $app])
                  ->setMethods(['outputSuccess', 'outputError'])
                  ->getMock();
        $ctrlMock
            ->expects($this->once())
            ->method('outputSuccess');
        $ctrlMock
            ->expects($this->never())
            ->method('outputError');

        $ctrlMock->actionFromI18n(User::tableName(), 'firstname', 'lang1');
    }
    
    public function testFromI18nAction_batchSize1_updatesIn2Batches()
    {
        $fixture = new I18nUserFixture();
        $fixture->load();
        $app = new Application($this->getConfigArray());
        $adminLangMock = $this->getAdminLanguageMock();
        $adminLangMock
            ->method('getActiveShortCode')
            ->willReturn('lang1');
        $app->set('adminLanguage', $adminLangMock);
        $ctrlMock = $this->getMockBuilder(LanguageConverterController::className())
                  ->setConstructorArgs(['language-converter', $app, ['batchSize' => 1]])
                  ->setMethods(['outputSuccess', 'outputError'])
                  ->getMock();

        $ctrlMock->actionFromI18n(User::tableName(), 'firstname', 'lang1');

        $this->assertSame(2, $ctrlMock->getBatchCount());
    }

    
    public function testToI18nAction_userFirstname_allFirstnameValuesI18n()
    {
        $fixture = new UserFixture();
        $fixture->load();
        $app = new Application($this->getConfigArray());
        $adminLangMock = $this->getAdminLanguageMock();
        $adminLangMock
            ->method('getActiveShortCode')
            ->willReturn('lang1');
        $app->set('adminLanguage', $adminLangMock);
        $ctrlMock = $this->getMockBuilder(LanguageConverterController::className())
                  ->setConstructorArgs(['language-converter', $app])
                  ->setMethods(['outputSuccess', 'outputError'])
                  ->getMock();

        $ctrlMock->actionToI18n(User::tableName(), 'firstname', 'lang1');
        
        $users = (new Query)
              ->select(['id', 'firstname'])
              ->from(I18nUser::tableName())
              ->all();
        $this->assertNotEmpty($users);
        $expectedNames = [
            1 => I18n::encode(['lang1' => 'John', 'lang2' => '']),
            2 => I18n::encode(['lang1' => 'Jane', 'lang2' => '']),
        ];
        foreach ($users as $user) {
            $this->assertSame($expectedNames[$user['id']], $user['firstname']);
        }
    }

    public function testToI18nAction_batchSize1_updatesIn2Batches()
    {
        $fixture = new UserFixture();
        $fixture->load();
        $app = new Application($this->getConfigArray());
        $adminLangMock = $this->getAdminLanguageMock();
        $adminLangMock
            ->method('getActiveShortCode')
            ->willReturn('lang1');
        $app->set('adminLanguage', $adminLangMock);
        $ctrlMock = $this->getMockBuilder(LanguageConverterController::className())
                  ->setConstructorArgs(['language-converter', $app, ['batchSize' => 1]])
                  ->setMethods(['outputSuccess', 'outputError'])
                  ->getMock();

        $ctrlMock->actionToI18n(User::tableName(), 'firstname', 'lang1');

        $this->assertSame(2, $ctrlMock->getBatchCount());
    }
}
