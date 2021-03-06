<?php
/**
 * 如花拼团开源系统 -- 永久免费
 * =========================================================
 * 官方网址：http://www.phps.shop
 * 作者：光爵【API + 后台】 、 小草【小程序 + WAP】
 * QQ 交流群：728615087
 * Version：1.0.0
 */

namespace app\api\service;


use app\lib\enum\ScopeEnum;
use app\lib\exception\AdminException;
use app\lib\exception\BaseException;
use think\facade\Cache;
use think\Exception;
use think\facade\Request;
use app\api\model\User as UserModel;
use app\api\model\Admins as AdminsModel;

//API的token文件，用于：生成token、
class TokenService
{
    protected $tokenExpire;

    function __construct()
    {
        $this->tokenExpire = config('setting.token_expire_in'); //token缓存有效时间
    }

    //生成随机token
    public static function generateToken()
    {
        //32个字符组成一组随机字符串
        $randChars = self::getRandChar(32);
        //用三组字符串，进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');
        return md5($randChars . $timestamp . $salt);
    }

    //通过token获取该条缓存数据中指定的字段
    public static function getCurrentTokenVar($key)
    {
        $token = Request::header('token');
        $vars = Cache::get($token);
        if (!$vars){
            throw new BaseException(['message'=>'token未找到','code'=>401]);
        }else{
            if (!is_array($vars)){
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)){
                return $vars[$key];
            }else{
                throw new Exception('尝试获取的变量并不存在');
            }
        }
    }

    //放入缓存
    public function saveCache($cachedValue){
        $key = self::generateToken();//生成token
        $value = json_encode($cachedValue);
        $request = cache($key, $value,$this->tokenExpire);//第三参数是时效期
        if(!$request){
            throw new BaseException([
                'msg' => '服务器缓存异常'
            ]);
        }
        return $key;
    }

    //通过token获取uid
    public static function getCurrentUid()
    {
        $uid = self::getCurrentTokenVar('uid');
        $user= UserModel::get($uid);
        if (!$user){
            throw new BaseException(['msg'=>'无该用户信息']);
        }
        return $uid;
    }

    //通过token获取cms的admin_id
    public static function getCurrentAid()
    {
        $uid = self::getCurrentTokenVar('admin_id');
        $user= AdminsModel::get($uid);
        if (!$user){
            throw new BaseException(['msg'=>'无该用户信息']);
        }
        return $uid;
    }

    //通过token获取cms的username
    public static function getAdminName()
    {
        $uid = self::getCurrentTokenVar('admin_id');
        $user= AdminsModel::get($uid);
        if (!$user){
            throw new BaseException(['msg'=>'无该用户信息']);
        }
        return $user->username;
    }

    //通过token获取scope,并判断权限
    // 用户和管理员都可以访问的权限
    public static function GTuserScope()
    {
        $scope = self::getCurrentTokenVar('scope');
        if (!$scope){
            throw new BaseException(['msg'=>'Token获取权限值失败']);
        }
        if ($scope >= ScopeEnum::User){ //判断权限值是否大于最低的用户权限
            return true;
        }else{
            throw new BaseException(['msg'=>'无权限']);
        }
    }

    //判断权限，仅管理员可访问
    public static function GTadmimScope()
    {
        try{
            $scope = self::getCurrentTokenVar('scope');
            if ($scope >= ScopeEnum::Root){ //判断权限值是否大于最低的用户权限
                return true;
            }else{
                throw new AdminException();
            }
        }catch(Exception $ex){
            throw new AdminException(['msg'=>'无权限-'.$ex->getMessage(),'code'=>$ex->getCode()]);
        }
    }


    //生成token函数中调用的，生成随机字符串
    private static function getRandChar($length)
    {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;
        for ($i = 0;
             $i < $length;
             $i++) {
            $str .= $strPol[rand(0, $max)];
        }
        return $str;
    }
    //验证toen
    public static function verifyToken($token)
    {
        $exist = Cache::get($token);
        if($exist){
            return true;
        }else{
            return false;
        }
    }

    //检测某订单中的uid是否以token中的uid一致，为同一人
    public static function isValidOperate($checkedUID)
    {
        if (!$checkedUID)
        {
            throw new BaseException('检查UID时必须传入UID');
        }
        $currentOperateUID = self::getCurrentTokenVar('uid');
        if($currentOperateUID != $checkedUID){
            return false;
        }
        return true;
    }

}