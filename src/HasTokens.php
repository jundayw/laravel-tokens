<?php

namespace Jundayw\LaravelTokens;

use Illuminate\Support\Facades\DB;

trait HasTokens
{
    public function createToken($pk, $guard, $type = 'default', $expires = true)
    {
        if ($expires === true) {
            $expires = app('config')->get('tokens.expires', 7200);
        }
        $guard   = strtoupper($guard);
        $type    = strtoupper($type);
        $time    = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + $expires);
        $token   = md5(sprintf('%s%s%s%s', $pk, $guard, $type, generate_string(32)));
        // 判断是否只能同一账户同一登录类型单独登录，否则将非当前账户下线
        $unique = app('config')->get('tokens.unique', false);
        if ($unique === true) {
            DB::table('tokens')->where([
                'tokens_guard_id' => $pk,
                'tokens_guard_type' => $guard,
                'tokens_type' => $type,
            ])->update([
                'tokens_revoked' => 'DISABLE',
            ]);
        }
        // 新增token记录
        DB::table('tokens')->insert([
            'tokens_guard_id' => $pk,
            'tokens_guard_type' => $guard,
            'tokens_type' => $type,
            'tokens_token' => $token,
            'tokens_create_time' => $time,
            'tokens_update_time' => $time,
            'tokens_expires_time' => $expires,
            'tokens_revoked' => 'NORMAL',
        ]);
        return [
            'guard' => $guard,
            'type' => $type,
            'token' => $token,
            'expires' => $expires,
        ];
    }

    public function refreshToken($token, $expires = true)
    {
        $tokens = DB::table('tokens')->where([
            'tokens_token' => $token,
            ['tokens_expires_time', '>=', date('Y-m-d H:i:s')],
            'tokens_revoked' => 'NORMAL',
        ])->first();
        if (is_null($tokens)) {
            return false;
        }
        if ($expires === true) {
            $expires = app('config')->get('tokens.expires', 7200);
        }
        $time    = date('Y-m-d H:i:s');
        $expires = date('Y-m-d H:i:s', time() + $expires);
        // 延长有效期
        DB::table('tokens')->where([
            'tokens_token' => $token,
            ['tokens_expires_time', '>=', date('Y-m-d H:i:s')],
            'tokens_revoked' => 'NORMAL',
        ])->update([
            'tokens_update_time' => $time,
            'tokens_expires_time' => $expires,
        ]);
        return [
            'guard' => $tokens->tokens_guard_type,
            'type' => $tokens->tokens_type,
            'token' => $token,
            'expires' => $expires,
        ];
    }
}