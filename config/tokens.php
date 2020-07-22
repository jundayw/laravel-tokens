<?php

return [
    // 是否允许同一账户同一客户端同时在线
    'unique' => false,
    // 令牌有效期
    'expires' => 7200,
    // 令牌分隔符
    'delimiter' => ',',
    // 当启用缓存时，unique设置为true时将延迟expires生效
    'cache' => false,
];