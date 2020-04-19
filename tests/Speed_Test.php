<?php
/**
 * Desc:
 * User: baagee
 * Date: 2020/4/19
 * Time: 下午9:57
 */
include __DIR__ . '/../vendor/autoload.php';

$redis = new Redis();
$redis->connect('127.0.0.1', 6379);

$sl = \BaAGee\RedisTools\SpeedLimit::getInstance($redis);
for ($i = 0; $i < 100; $i++) {
    // 十秒内最多允许7次操作
    $res = $sl->isAllow("user_id", 'replay', 10, 7);
    echo ($res ? '允许' : '不允许') . $i . PHP_EOL;
    usleep(1000000);
}
