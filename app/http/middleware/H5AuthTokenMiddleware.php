<?php


namespace app\http\middleware;


use app\models\user\User;
use app\models\user\UserToken;
use app\Request;
use crmeb\exceptions\AuthException;
use crmeb\interfaces\MiddlewareInterface;
use crmeb\repositories\UserRepository;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\DbException;

/**
 * token验证中间件
 * Class AuthTokenMiddleware
 * @package app\http\middleware
 */
class H5AuthTokenMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next, bool $force = true)
    {
        $uid = $request->param('uid') ?? 0;
        Request::macro('uid', function () use ($uid) {
            return $uid;
        });
        $user = db('user')->where('uid', $uid)->find() ?? [];
        Request::macro('user', function () use ($user) {
            return $user;
        });

        return $next($request);
    }
}
