local key = KEYS[1]
local value = ARGV[1]
redis.replicate_commands()
if (redis.call('exists', key) == 1 and redis.call('get', key) == value)
then
    return redis.call('del', key)
end

return 0