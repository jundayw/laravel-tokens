<?php

namespace Jundayw\LaravelTokens;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TokensGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $provider;

    public function __construct($app, $name, $config)
    {
        $this->request  = $app['request'];
        $this->provider = Auth::createUserProvider($config['provider'] ?? null);
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }
        $tokens = $this->getTokensForRequest();
        if ($tokens === false) {
            return null;
        }
        // 缓存获取
        if (app('config')->get('tokens.cache', false)) {
            $cache = cache($tokens['tokens_token']);
            if ($cache) {
                return $this->user = unserialize($cache);
            }
        }
        // 查询token关联账户主键
        $tokens = DB::table('tokens')->where($tokens)->first();
        if (is_null($tokens)) {
            return null;
        }
        // 根据主键获取账户信息
        $user = $this->provider->retrieveById($tokens->tokens_guard_id);
        // 设置缓存
        if (app('config')->get('tokens.cache', false)) {
            cache([$tokens->tokens_token => serialize($user)], strtotime($tokens->tokens_expires_time) - time());
        }
        return $this->user = $user;
    }

    protected function getTokensForRequest()
    {
        $tokens    = $this->request->header('tokens');
        $delimiter = app('config')->get('tokens.delimiter', ',');
        $tokens    = explode($delimiter, $tokens);
        if (count($tokens) < 3) {
            return false;
        }
        [$guard, $type, $token] = $tokens;
        return [
            'tokens_guard_type' => strtoupper($guard),
            'tokens_type' => strtoupper($type),
            'tokens_token' => $token,
            ['tokens_expires_time', '>=', date('Y-m-d H:i:s')],
            'tokens_revoked' => 'NORMAL',
        ];
    }

    public function validate($credentials = [])
    {
        return true;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}