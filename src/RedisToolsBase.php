<?php
/**
 * Desc:
 * User: baagee
 * Date: 2020/4/19
 * Time: 下午10:14
 */

namespace BaAGee\RedisTools;

/**
 * Class RedisToolsBase
 * @package BaAgee\RedisTools
 */
abstract class RedisToolsBase
{
    /**
     * @var \Redis
     */
    protected $redisObject = null;


    /**
     * @var static
     */
    protected static $self = null;

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
     * 获取对象
     * @param $redisObject
     * @return static
     */
    public static function getInstance($redisObject)
    {
        if (static::$self == null) {
            $self = new static();
            $self->redisObject = $redisObject;
            static::$self = $self;
            static::loadLuaScript();
        }
        return static::$self;
    }

    protected static function loadLuaScript(): void
    {

    }
}