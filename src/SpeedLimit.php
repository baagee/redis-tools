<?php
/**
 * Desc: 基于redis的限速器 主要是用到了滑动时间窗口
 * User: baagee
 * Date: 2020/3/28
 * Time: 下午9:29
 */

namespace BaAGee\RedisTools;
/**
 * Class SpeedLimit
 * @package BaAGee\RedisTools
 */
final class SpeedLimit extends RedisToolsBase
{
    /**
     * redis key前缀
     */
    protected const LIMIT_KEY_PREFIX = 'speed:limit:hist';

    /**
     * @var string
     */
    protected static $speedLimitSha = '';

    /**
     * 加载lua script到redis
     */
    protected static function loadLuaScript(): void
    {
        $speedLimitLuaFile = __DIR__ . '/lua/speed_limit.lua';
        $speedLimitScript = file_get_contents($speedLimitLuaFile);
        self::$speedLimitSha = self::$self->redisObject->script('load', $speedLimitScript);
    }

    /**
     * 用户【$userId】在【$period】秒内最多执行【$action】动作【$maxCount】次
     * @param string $userId   用户
     * @param string $action   动作
     * @param int    $period   期限
     * @param int    $maxCount 最大次数
     * @return bool
     */
    public function isAllow($userId, $action, int $period, int $maxCount): bool
    {
        $key = sprintf("%s:%s:%s", $action, self::LIMIT_KEY_PREFIX, $userId);
        $field = $score = $now = intval(microtime(true) * 1000);
        $field += mt_rand(1000000000, 9999999999);
        $end = intval($now - $period * 1000);
        $params = [$key, $field, $score, $end, $period + 1, $maxCount];
        $res = $this->redisObject->evalSha(self::$speedLimitSha, $params, 1);
        return boolval($res);
    }

    /**
     * 尝试执行
     * @param callable $func     详细执行的逻辑
     * @param string   $userId   用户
     * @param string   $action   动作
     * @param int      $period   期限
     * @param int      $maxCount 最大次数
     * @return bool true:可以执行并执行了；false:不可以执行 也没执行
     */
    public function run(callable $func, $userId, $action, int $period, int $maxCount)
    {
        if ($this->isAllow($userId, $action, $period, $maxCount)) {
            call_user_func($func);
            return true;
        } else {
            return false;
        }
    }
}
