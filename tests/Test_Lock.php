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

$lock = \BaAGee\RedisTools\PreemptiveLock::getInstance($redis);
//方式1
$getLock = $lock->lock("test", 3, 10, 10000);
if ($getLock) {
    echo "get lock" . PHP_EOL;
    $lock->unlock('test');
} else {
    echo 'not get lock' . PHP_EOL;
}

// 方式2
$execute = $lock->run(function () {
    echo "我获得锁啦，哈哈哈" . PHP_EOL;
}, "test2", 5, 20, 20000);
if ($execute) {
    echo "获得锁并且已经执行了" . PHP_EOL;
} else {
    echo "没获得锁也没执行" . PHP_EOL;
}
