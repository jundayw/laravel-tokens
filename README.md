# 安装方法
命令行下, 执行 composer 命令安装:
````
composer require jundayw/laravel-tokens
````

# 使用方法
authentication package that is simple and enjoyable to use.

# 配置方法

将配置文件 auth.php 中 guards 的 driver 字段修改为 tokens
````
'guards' => [
    'web' => [
        'driver' => 'tokens',
        'provider' => 'users',
    ],
],
````
用户信息获取、登录及退出操作
````
use Jundayw\LaravelTokens\HasTokens;
use Jundayw\LaravelTokens\HasTokensContract;

class User extends Authenticatable implements HasTokensContract
{
    use HasTokens;

    public function login(Request $request){
        $guard = config("auth.defaults.guard");// web
        $provider = config("auth.guards.{$guard}.provider");// users
        $model = config("auth.providers.{$provider}.model");// App\User::class
        //获取默认用户 guard 配置，可根据业务情况获取 $guard 及 $model 参数
        $user = $request->user($guard);
        //登录逻辑
        Auth::guard($guard)->claims([
            'iss' => $model,
            'sub' => 'android',// web,ios,android 也可传入 user-agent 确定用户唯一性
            'aud' => 'weixin',// pc,weixin,alipay 也可传入 user-agent 确定用户唯一性
        ])->login(User::find(1));
        //退出登录
        Auth::guard($guard)->logout();
    }
}
````

## 发布配置文件
````
php artisan vendor:publish --tag=tokens-config
````

## 发布迁移文件
````
php artisan vendor:publish --tag=tokens-migrations
````

发布迁移文件仅在同一用户不允许多端同时登陆时需要，并且需要：

1、通过 Auth::extend 扩展 TokensGuard 类；

2、重写 TokensGuard 类的 login 方法，将用户信息写入 tokens 表；

3、重写 TokensGuard 类的 getTokensForRequest 方法判定当前用户(tokens 表的 tokens_revoked 字段)是否已被挤下线。
