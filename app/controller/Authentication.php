<?php
/**
 * @desc 身份认证
 * @author Tinywan(ShaoBo Wan)
 * @date 2021/12/19 16:55
 */

declare(strict_types=1);

namespace app\controller;


use app\common\controller\BaseController;
use app\common\model\UserModel;
use app\common\validate\UnauthorizedValidate;
use support\Request;
use support\Response;
use Tinywan\Jwt\JwtToken;

class Authentication extends BaseController
{
    public function issueToken(Request $request): Response
    {
        $params = $request->post();
        validate($params, UnauthorizedValidate::class . '.issue');
        $model = UserModel::field('id,username,mobile,email,avatar,password,is_enabled,create_time');
        if (is_mobile((string) $params['username'])) {
            $model->where('mobile', $params['username']);
        } elseif (filter_var(trim($params['username']), FILTER_VALIDATE_EMAIL)) {
            $model->where('email', $params['username']);
        } else {
            $model->where('username', $params['username']);
        }
        $userInfo = $model->findOrEmpty();
        if ($userInfo->isEmpty()) {
            return response_json(0,'账号或密码错误',[],400);
        }

        if (!password_verify(trim($params['password']), $userInfo->password)) {
            return response_json(0,'账号或密码错误',[],400);
        }

        $user = [
            'user_info' => [
                'userId' => $userInfo['id'],
                'userName' => $userInfo['username'],
                'dashboard' => 0,
                'role' => ["SA","admin","Auditor"],
            ]
        ];
        $res = array_merge(JwtToken::generateToken($userInfo->toArray()),$user);
        return response_json(0,'success',$res);
    }
}