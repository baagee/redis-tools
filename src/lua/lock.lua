local key = KEYS[1]
local value = ARGV[1]
local ttl = ARGV[2]

if (redis.call('setnx', key, value) == 1) then
    return redis.call('expire', key, ttl)
elseif (redis.call('ttl', key) == -1) then
    return redis.call('expire', key, ttl)
end

return 0