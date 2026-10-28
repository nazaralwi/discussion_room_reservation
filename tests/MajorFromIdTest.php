<?php
require_once DRRB . DS . 'app/helper/common.php';
use PHPUnit\Framework\TestCase;

class MajorFromIdTest extends TestCase
{
    public function testShortIdsFallBackToDefaultMajor()
    {
        foreach (['', '1', '1001', 1001, null] as $id) {
            $this->assertSame('S1 Teknik Informatika', getMajorFromId($id), var_export($id, true));
        }
    }

    public function testKnownIdsStillMapToMajor()
    {
        $this->assertSame('S1 Sistem Informasi', getMajorFromId('1310300001'));
        $this->assertSame('S1 Teknik Industri', getMajorFromId('1310600001'));
    }
}
