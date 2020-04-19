local _key = KEYS[1]
local _field= ARGV[1]
local _score= tonumber(ARGV[2])
local _end= tonumber(ARGV[3])
local _period= tonumber(ARGV[4])
local _max= tonumber(ARGV[5])

redis.call('ZREMRANGEBYSCORE',_key, 0, _end)
local _count=redis.call('ZCARD', _key)
if (_count>=_max) then
    return false
end
redis.call('ZADD',_key,_score,_field)
redis.call('EXPIRE',_key, _period)
return true