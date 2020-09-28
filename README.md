# 安装方法
命令行下, 执行 composer 命令安装:
````
composer require jundayw/laravel-tokens
````

# 使用方法
authentication package that is simple and enjoyable to use.

````
'guards' => [
    'web' => [
        'driver' => 'tokens',
        'provider' => 'users',
    ],
],
````

````
use Jundayw\LaravelTokens\HasTokens;
use Jundayw\LaravelTokens\HasTokensContract;
class User extends Authenticatable implements HasTokensContract
{
    use HasTokens;
    
    public function login($request){
        // 获取用户
        //$request->user($guard);
        // 登录逻辑
        //Auth::guard($guard)->claims([])->login(User::find(1));
        // 退出登录
        //Auth::guard($guard)->logout();
    }
}
````
发布配置文件
````
php artisan vendor:publish --tag=tokens-config
````
发布迁移文件
````
php artisan vendor:publish --tag=tokens-migrations
````