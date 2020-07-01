<?php
/**
 * Desc: 令牌桶限速
 * User: baagee
 * Date: 2020/7/1
 * Time: 上午7:42
 */

namespace BaAGee\RedisTools;

/**
 * Class TokenBucket
 * @package BaAGee\RedisTools
 */
class TokenBucket extends RedisToolsBase
{
    /**
     * key前缀
     */
    protected const TOKEN_BUCKET_KEY_PREFIX = 'token:bucket';
    /**
     * @var int
     */
    protected $bucketCapacity = 100;

    /**
     * @var int
     */
    protected $leakingRate = 1;

    /**
     * @var string
     */
    protected static $tokenBucketSha = '';

    /**
     * 设置令牌桶容量
     * @param int $capacity
     * @return $this
     */
    public function setCapacity(int $capacity)
    {
        if ($capacity > 0) {
            $this->bucketCapacity = $capacity;
        }
        return $this;
    }

    /**
     * 设置添加令牌速率
     * @param int|float $leakingRate 每毫秒 几个
     * @return $this
     */
    public function setLeakingRate($leakingRate): self
    {
        if ($leakingRate > 0) {
            $this->leakingRate = $leakingRate;
        }
        return $this;
    }

    /**
     * 加载lua脚本
     */
    protected static function loadLuaScript(): void
    {
        $speedLimitLuaFile = __DIR__ . '/lua/token_bucket.lua';
        $speedLimitScript = file_get_contents($speedLimitLuaFile);
        self::$tokenBucketSha = self::$self->redisObject->script('load', $speedLimitScript);
    }

    /**
     * 获得token
     * @param string $action 动作
     * @return bool true:获得了 false:没获得
     */
    public function getToken($action)
    {
        $key = sprintf("%s:%s", self::TOKEN_BUCKET_KEY_PREFIX, $action);
        $params = [$key, $this->bucketCapacity, $this->leakingRate, intval(microtime(true) * 1000)];
        $res = $this->redisObject->evalSha(self::$tokenBucketSha, $params, 1);
        return boolval($res);
    }


    /**
     * 尝试执行
     * @param callable $func   详细执行的逻辑
     * @param string   $action 动作
     * @return bool true:可以执行并执行了；false:不可以执行 也没执行
     */
    public function run(callable $func, $action)
    {
        if ($this->getToken($action)) {
            call_user_func($func);
            return true;
        } else {
            return false;
        }
    }
}
