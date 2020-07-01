local capacity = tonumber(ARGV[1]) -- 桶容量
local leaking_rate = tonumber(ARGV[2]) -- 添加令牌的速度
local _key=KEYS[1]

redis.replicate_commands()

-- 获取令牌桶的配置信息
local rate_limit_info = redis.call("HGETALL", _key)

-- 获取当前时间戳
-- local timestamp = redis.call("TIME")
-- local now = math.floor((timestamp[1] * 1000000 + timestamp[2]) / 1000)
local now = tonumber(ARGV[3])


if #rate_limit_info == 0 then -- 没有设置限流配置,则默认拿到令牌 同时设置桶数量是容量-1
    redis.call("HMSET", _key, "token", capacity - 1, "time", now)
    return true
end

local current_size = tonumber(rate_limit_info[2]) -- 当前可用数量
local last_leaking_time = tonumber(rate_limit_info[4]) -- 最后访问毫秒

-- 计算需要补给的令牌数,更新令牌数和补给时间戳
local supply_token = math.floor((now - last_leaking_time) * leaking_rate)

if (supply_token > 0) then
   last_leaking_time = now
   current_size = supply_token + current_size

   if current_size > capacity then
      current_size = capacity
   end
end

local result = 0 -- 返回结果是否能够拿到令牌,默认否

-- 计算请求是否能够拿到令牌
if (current_size > 0) then
    current_size = current_size - 1
    result = 1
end

-- 更新令牌桶的配置信息
redis.call("HMSET", _key, "token", current_size, "time", last_leaking_time)

return  result