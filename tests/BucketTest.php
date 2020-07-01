<?php
/**
 * Desc:
 * User: baagee
 * Date: 2020/5/8
 * Time: 下午8:20
 */

include __DIR__ . '/../vendor/autoload.php';

class BucketTest extends \PHPUnit\Framework\TestCase
{
    protected $redis = null;

    public function setUp()
    {
        $this->start();
    }

    public function start()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $this->redis = $redis;
    }


    public function testSpeed()
    {
        $bucket = \BaAGee\RedisTools\TokenBucket::getInstance($this->redis);
        //方式1
        $action = 'eat';
        //设置桶容量 设置添加令牌的速度（每毫秒添加几个）
        $bucket->setCapacity(100)->setLeakingRate(1);
        for ($i = 0; $i < 1000; $i++) {
            $res = $bucket->getToken($action);
            usleep(100);
            echo ($res ? '可以' : '不可以') . $action . PHP_EOL;
            $this->assertNotEmpty('sdgsa');
        }
        unset($bucket);
    }

    public function testSpeedV2()
    {
        $bucket = \BaAGee\RedisTools\TokenBucket::getInstance($this->redis);
        //方式1
        $action = 'eat';
        //设置桶容量 设置添加令牌的速度（每毫秒添加几个）
        $bucket->setCapacity(100)->setLeakingRate(1);
        // 方式2
        for ($i = 0; $i < 1000; $i++) {
            $res = $bucket->run(function () use ($action) {
                echo '小明' . $action . PHP_EOL;
            }, $action);
            echo ($res ? 'success' : 'failed') . PHP_EOL;
            $this->assertNotEmpty('sdgsa');
        }
        unset($bucket);
    }
}
