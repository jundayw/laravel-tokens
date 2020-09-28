<?php

namespace Jundayw\LaravelTokens;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;

class TokensGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $name;
    protected $config;
    protected $provider;

    public function __construct($app, $name, $config)
    {
        $this->request  = $app['request'];
        $this->name     = $name;
        $this->config   = $config;
        $this->provider = Auth::createUserProvider($config['provider'] ?? null);
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }
        // 获取凭证
        if (($tokens = $this->getTokensForRequest()) === false) {
            return null;
        }
        // 根据主键获取账户信息
        return $this->user = $this->provider->retrieveById($tokens->getClaim('jti', 0));
    }

    public function getTokensForRequest()
    {
        $token = $this->request->bearerToken();
        try {
            $token = (new Parser())->parse($token);
        } catch (\Exception $exception) {
            return false;
        }
        // 防止 jwt 欺骗
        if (strcasecmp($token->getClaim('iss'), $this->name)) {
            return false;
        }
        // 有效期判断
        if ($token->isExpired()) {
            return false;
        }
        // 签名验证
        if ($token->verify(new Sha256(), config('tokens.secret')) == false) {
            return false;
        }
        // 返回值
        return $token;
    }

    /**
     * header
     *  alg
     *  typ
     * Payload
     *  iss (issuer)：签发人
     *  sub (subject)：主题
     *  aud (audience)：受众
     *  exp (expiration time)：过期时间
     *  nbf (Not Before)：生效时间，在此之前是无效的
     *  iat (Issued At)：签发时间
     *  jti (JWT ID)：编号
     * Signature
     */
    public function login(Authenticatable $authenticatable)
    {
        $iat     = $nbf = time();
        $exp     = time() + config('tokens.ttl', 7200);
        $jti     = $authenticatable->getJWTIdentifier();
        $builder = new Builder();
        foreach ($authenticatable->getJWTCustomClaims() as $name => $value) {
            $builder->withClaim(strtolower($name), strtolower($value));
        }
        //$builder->issuedBy(get_class($authenticatable));// Configures the issuer (iss claim)
        //$builder->relatedTo(1);// Configures the subject (sub claim), replicating as a header item
        //$builder->permittedFor(1);// Configures the audience (aud claim)
        $builder->expiresAt($exp);// Configures the expiration time of the token (exp claim)
        $builder->canOnlyBeUsedAfter($nbf);// Configures the time that the token can be used (nbf claim)
        $builder->issuedAt($iat);// Configures the time that the token was issue (iat claim)
        $builder->identifiedBy($jti);// Configures the id (jti claim), replicating as a header item
        $tokens = $builder->getToken(new Sha256(), new Key(config('tokens.secret')));
        return (string)$tokens;
    }

    public function logout()
    {
        return true;
    }

    public function validate(array $credentials = [])
    {
        return true;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}