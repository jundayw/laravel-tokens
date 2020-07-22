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
class User extends Authenticatable
{
    use Notifiable;
    use HasTokens;
    
    public function login($request){
        // 登录逻辑
        $this->createToken($user->id,'user','weixin');
        //$this->createToken($user->id,'user','alipay');
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