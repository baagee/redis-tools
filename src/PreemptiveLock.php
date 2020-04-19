<?php
/**
 * Desc: 抢占锁
 * User: baagee
 * Date: 2019/5/9
 * Time: 11:57
 */

namespace BaAGee\RedisTools;

/**
 * Class PreemptiveLock
 * @package BaAGee\RedisTools
 */
final class PreemptiveLock
{
    /**
     * redis key前缀
     */
    private const REDIS_LOCK_KEY_PREFIX = 'redis:lock:';

    /**
     * @var array
     */
    private $lockedNames = [];

    /**
     * @var \Redis
     */
    private $redisObject = null;

    /**
     * @var RedisLock
     */
    private static $self = null;
    /**
     * @var string
     */
    private static $lockSha = '';
    /**
     * @var string
     */
    private static $unlockSha = '';

    /**
     * RedisLockR constructor.
     */
    private function __construct()
    {
    }

    /**
     *
     */
    private function __clone()
    {

    }

    /**
     * 获取锁对象
     * @param $redisObject
     * @return PreemptiveLock
     */
    public static function getInstance($redisObject)
    {
        if (self::$self == null) {
            $self = new self();
            $self->redisObject = $redisObject;
            self::$self = $self;
            self::loadLuaScript();
        }
        return self::$self;
    }

    /**
     * 提前加载lua 脚本到redis
     */
    protected static function loadLuaScript()
    {
        $lockLuaFile = __DIR__ . '/lua/lock.lua';
        $lockScript = file_get_contents($lockLuaFile);
        $unlockLuaFile = __DIR__ . '/lua/unlock.lua';
        $unlockScript = file_get_contents($unlockLuaFile);
        self::$lockSha = self::$self->redisObject->script('load', $lockScript);
        self::$unlockSha = self::$self->redisObject->script('load', $unlockScript);
    }

    /**
     * 上锁
     * @param string $name       锁名字
     * @param int    $expire     锁有效期 秒
     * @param int    $retryTimes 重试次数
     * @param int    $sleep      重试休息 微秒
     * @return mixed
     */
    public function lock(string $name, int $expire = 5, int $retryTimes = 10, $sleep = 10000)
    {
        $oj8k = false;
        $retryTimes = max($retryTimes, 1);
        $key = self::REDIS_LOCK_KEY_PREFIX . $name;
        while ($retryTimes-- > 0) {
            $kVal = microtime(true) + $expire;
            $oj8k = $this->getLock($key, $expire, $kVal);//上锁
            if ($oj8k) {
                $this->lockedNames[$key] = $kVal;
                break;
            }
            usleep($sleep);
        }
        return $oj8k;
    }

    /**
     * 获取锁
     * @param $key
     * @param $expire
     * @param $value
     * @return mixed
     */
    private function getLock($key, $expire, $value)
    {
        return $this->redisObject->evalSha(self::$lockSha, [$key, $value, $expire], 1);
    }

    /**
     * 解锁
     * @param string $name
     * @return mixed
     */
    public function unlock(string $name)
    {
        $key = self::REDIS_LOCK_KEY_PREFIX . $name;
        if (isset($this->lockedNames[$key])) {
            $val = $this->lockedNames[$key];
            $this->redisObject->evalSha(self::$unlockSha, [$key, $val], 1);
            unset($this->lockedNames[$key]);
        }
        return false;
    }

    /**
     * 获取锁并执行
     * @param callable $func
     * @param string   $name
     * @param int      $expire
     * @param int      $retryTimes
     * @param int      $sleep
     * @return bool
     * @throws \Exception
     */
    public function run(callable $func, string $name, int $expire = 5, int $retryTimes = 10, $sleep = 10000)
    {
        if ($this->lock($name, $expire, $retryTimes, $sleep)) {
            try {
                call_user_func($func);
            } finally {
                $this->unlock($name);
            }
            return true;
        } else {
            return false;
        }
    }

    public function __destruct()
    {
        try {
            if (!empty($this->lockedNames)) {
                foreach ($this->lockedNames as $k => $v) {
                    $this->redisObject->evalSha(self::$unlockSha, [$k, $v], 1);
                }
            }
            self::$lockSha = '';
            self::$unlockSha = '';
            self::$self = null;
        } catch (\Exception $e) {

        }
    }
}

