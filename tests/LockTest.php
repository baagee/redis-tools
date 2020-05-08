<?php
/**
 * Desc:
 * User: baagee
 * Date: 2020/5/8
 * Time: 下午8:20
 */

include __DIR__ . '/../vendor/autoload.php';

class LockTest extends \PHPUnit\Framework\TestCase
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

    public function testLock2()
    {
        $lock = \BaAGee\RedisTools\PreemptiveLock::getInstance($this->redis);
        // // 方式2
        $execute = $lock->run(function () {
            echo "我获得锁啦，哈哈哈" . PHP_EOL;
        }, "test2", 5, 20, 20000);
        if ($execute) {
            echo "获得锁并且已经执行了" . PHP_EOL;
        } else {
            echo "没获得锁也没执行" . PHP_EOL;
        }
        unset($lock);
        $this->assertEquals($execute, true);
    }

    public function testLock3()
    {
        $lock = \BaAGee\RedisTools\PreemptiveLock::getInstance($this->redis);
        var_dump($lock);
        //方式1
        $getLock = $lock->lock("test", 3, 10, 10000);
        if ($getLock) {
            echo "get lock" . PHP_EOL;
            $lock->unlock('test');
        } else {
            echo 'not get lock' . PHP_EOL;
        }
        unset($lock);
        $this->assertEquals($getLock, true);
    }
}
