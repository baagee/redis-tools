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
    echo str_repeat('#', 70) . PHP_EOL;
    // 十秒内最多允许7次操作
    //用法1
    $res = $sl->isAllow("user_id", 'replay', 10, 7);
    echo ($res ? 'replay允许' : 'replay不允许') . $i . PHP_EOL;

    // 十秒内最多允许7次操作
    //用法1
    $res = $sl->run(function () {
        echo '买了' . PHP_EOL;
    }, "user_id2", 'buy', 10, 7);

    echo ($res ? '已经买完了' : '不能买') . $i . PHP_EOL;
    usleep(1000000);
}
