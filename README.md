# redis-tools

Redis distributed lock and current limiter

结合redis实现的分布式锁和限流器


## Redis分布式抢占锁用法

```php
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
```

## 限速器用法
```php
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
```
