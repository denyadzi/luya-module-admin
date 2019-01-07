<?php

namespace admintests;

use luya\testsuite\cases\BaseTestSuite;
use luya\base\Boot;
use luya\admin\components\AdminLanguage;


require 'vendor/autoload.php';
require 'data/env.php';

class AdminTestCase extends BaseTestSuite
{
    public function getConfigArray()
    {
        return include(__DIR__ .'/data/configs/admin.php');
    }
    
    public function bootApplication(Boot $boot)
    {
        $boot->applicationWeb();
    }
    
    protected function removeNewline($text)
    {
        $text = trim(preg_replace('/\s+/', ' ', $text));
        
        return str_replace(['> ', ' <'], ['>', '<'], $text);
    }

    protected function getAdminLanguageMock()
    {
        $mock = $this->createMock(AdminLanguage::className());
        $mock
            ->method('getLanguages')
            ->willReturn([
                [
                    'id' => 1,
                    'name' => 'Lang1',
                    'short_code' => 'lang1',
                    'is_default' => false,
                    'is_deleted' => false,
                ],
                [
                    'id' => 2,
                    'name' => 'Lang2',
                    'short_code' => 'lang2',
                    'is_default' => true,
                    'is_deleted' => false,
                ],
            ]);
        return $mock;
    }
}
