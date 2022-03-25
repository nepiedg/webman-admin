<?php
/**
 * @desc 身份认证
 * @author Tinywan(ShaoBo Wan)
 * @date 2021/12/19 16:55
 */

declare(strict_types=1);

namespace app\controller;

use app\common\validate\UnauthorizedValidate;
use app\common\validate\UserValidate;
use support\Log;
use support\Request;
use Tinywan\Captcha\Captcha;
use Tinywan\Captcha\Config;
use Tinywan\Jwt\JwtToken;
use Tinywan\Nacos\Exception\NacosAuthException;
use Tinywan\Nacos\Nacos;
use Tinywan\Storage\Exception\StorageException;
use Tinywan\Storage\Storage;
use Tinywan\Support\Logger;
use Tinywan\Support\Str;

class Test
{
    public function validate(Request $request)
    {
        $data = [
            'name'  => 'Tinywan',
            'age'  => 24,
//            'email' => 'Tinywan@163.com'
        ];
        validate($data, UserValidate::class . '.issue');
//        $validate = new UserValidate();
//        if (false === $validate->check($data)) {
//            return 'fail, '.$validate->getError();
//        }
        return 'success';
    }

    public function jwt(Request $request)
    {
        $uid = JwtToken::getCurrentId();
        return response_json(0,'success',[
            'uid'=>$uid,
            'mobile'=>JwtToken::getExtendVal('mobile'),
            'Extend'=>JwtToken::getExtend(),
            'Exp'=>JwtToken::getTokenExp(),
        ]);
    }

    public function refreshToken(Request $request)
    {
        $accessToken = JwtToken::refreshToken();
        return response_json(0,'success',$accessToken);
    }

    /**
     * 异常测试
     * @param Request $request
     */
    public function exceptionHandler(Request $request)
    {
        $param = $request->get();
        validate($param, UnauthorizedValidate::class . '.issue');
        return response_json(0,'success');
    }

    /**
     * upload
     * @param Request $request
     */
    public function upload(Request $request)
    {
        try {
            Storage::config(Storage::MODE_OSS); // 初始化。 默认为本地存储：local
            $res['file_path'] = public_path().DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'rbac-model.conf';
            $res['extension'] = 'conf';
            $r = Storage::uploadLocalFile($res);
            var_dump($r);
        }catch (StorageException $exception) {
            return response_json(0,$exception->getMessage());
        }
        return response_json(0,'success');
    }

    /**
     * upload
     * @param Request $request
     */
    public function nacos(Request $request)
    {
        $nacos = new Nacos();
        $dataId = 'database';
        $group = 'DEFAULT_GROUP';
        $namespace = '188e48a6-6c98-4563-a76e-e1c70a91e650';

        $content = $nacos->config->get($dataId, $group, $namespace);
        if (false === $content) {
            return response_json(0,$nacos->config->getMessage());
        }
        $contentMd5 = md5($content);
        // 注册监听采用的是异步 Servlet 技术。注册监听本质就是带着配置和配置值的 MD5 值和后台对比。
        // 如果 MD5 值不一致，就立即返回不一致的配置。如果值一致，就等待住 30 秒。返回值为空。

        $response = $nacos->config->listen($dataId, $group, $contentMd5,$namespace);
        if (false === $response) {
            return response_json(0,$nacos->config->getMessage());
        }
        var_dump($response);
        return response_json(0,'nacos');
    }

    /**
     * log
     * @param Request $request
     */
    public function log(Request $request)
    {
//        $code = $request->get('code');
//        if(false === Captcha::check($code)){
//            // 验证失败
//        };
        // 验证通过
        echo Captcha::base64();
//        return response_json(0, 'ok', ['captcha' => Captcha::base64()]);
//        var_dump(Captcha::check('reck7'));
//        return response_json(0, 'ok');

    }

}
