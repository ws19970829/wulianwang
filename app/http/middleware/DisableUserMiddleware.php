<?php


namespace app\http\middleware;

use app\admin\model\user\User as UserUser;
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
 * 禁止用户
 * @package app\http\middleware
 */
class DisableUserMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, \Closure $next)
    {
        $uid = Request::uid();
        $userinfo = User::get($uid);
        if (!empty($userinfo) && !$userinfo->status) {
            db('user_token')->where('uid', $uid)->delete();
        }
        return $next($request);
    }
}
