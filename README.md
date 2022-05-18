# laravel_apis_frame 1.0.0 version

### JWT Token

【目前加密模式仅支持：HS256】

配置文件：

调用：  
    生成加密：  

    $token = Jwt::sign($user)
    ps：$user 为用户对象

验证加密

    $result = Jwt::check($token)
Jwt
