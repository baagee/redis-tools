<?php
/**
 * Desc:
 * User: baagee
 * Date: 2020/5/8
 * Time: 下午8:20
 */

include __DIR__ . '/../vendor/autoload.php';

class SpeedTest extends \PHPUnit\Framework\TestCase
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
        $sl = \BaAGee\RedisTools\SpeedLimit::getInstance($this->redis);
        for ($i = 0; $i < 8; $i++) {
            echo str_repeat('#', 70) . PHP_EOL;
            // 十秒内最多允许7次操作
            //用法1
            $res = $sl->isAllow("user_id", 'replay', 10, 7);
            echo ($res ? 'replay允许' : 'replay不允许') . $i . PHP_EOL;
            usleep(1000000);
            if ($i == 7) {
                $this->assertEquals($res, false);
            }
        }
        unset($sl);
    }

    public function testSpeedV2()
    {
        $sl = \BaAGee\RedisTools\SpeedLimit::getInstance($this->redis);
        for ($i = 0; $i < 8; $i++) {
            echo str_repeat('#', 70) . PHP_EOL;
            $res = $sl->run(function () {
                echo '买了' . PHP_EOL;
            }, "user_id2", 'buy', 10, 7);

            echo ($res ? '已经买完了' : '不能买') . $i . PHP_EOL;
            usleep(1000000);
            if ($i == 7) {
                $this->assertEquals($res, false);
            }
        }
        unset($sl);
    }
}
