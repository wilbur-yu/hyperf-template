<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @method static getMessage(int $code)
 */
#[Constants]
class HttpCode extends AbstractConstants
{
    /**
     * @Message("OK")
     * 对成功的 GET、PUT、PATCH 或 DELETE 操作进行响应。也可以被用在不创建新资源的 POST 操作上
     */
    public const OK = 200;

    /**
     * @Message("Created")
     * 对创建新资源的 POST 操作进行响应。应该带着指向新资源地址的 Location 头
     */
    public const CREATED = 201;

    /**
     * @Message("Accepted")
     * 服务器接受了请求，但是还未处理，响应中应该包含相应的指示信息，告诉客户端该去哪里查询关于本次请求的信息
     */
    public const ACCEPTED = 202;

    /**
     * @Message("No Content")
     * 对不会返回响应体的成功请求进行响应（比如 DELETE 请求）
     */
    public const NO_CONTENT = 203;

    /**
     * @Message("Moved Permanently")
     * 被请求的资源已永久移动到新位置
     */
    public const MOVED_PERMANENTLY = 301;

    /**
     * @Message("Found")
     * 请求的资源现在临时从不同的 URI 响应请求
     */
    public const FOUNT = 302;

    /**
     * @Message("See Other")
     * 对应当前请求的响应可以在另一个 URI 上被找到，客户端应该使用 GET 方法进行请求。比如在创建已经被创建的资源时，可以返回 303
     */
    public const SEE_OTHER = 303;

    /**
     * @Message("Not Modified")
     * HTTP缓存header生效的时候用
     */
    public const NOT_MODIFIED = 304;

    /**
     * @Message("Temporary Redirect")
     * 对应当前请求的响应可以在另一个 URI 上被找到，客户端应该保持原有的请求方法进行请求
     */
    public const TEMPORARY_REDIRECT = 307;

    /**
     * @Message("Bad Request")
     * 请求异常，比如请求中的body无法解析
     */
    public const BAD_REQUEST = 400;

    /**
     * @Message("Unauthorized")
     * 没有进行认证或者认证非法
     */
    public const UNAUTHORIZED = 401;

    /**
     * @Message("Forbidden")
     * 服务器已经理解请求，但是拒绝执行它
     */
    public const FORBIDDEN = 403;

    /**
     * @Message("Not Found")
     * 请求一个不存在的资源
     */
    public const NOT_FOUND = 404;

    /**
     * @Message("Method Not Allowed")
     * 所请求的 HTTP 方法不允许当前认证用户访问
     */
    public const METHOD_NOT_ALLOWED = 405;

    /**
     * @Message("Gone")
     * 表示当前请求的资源不再可用。当调用老版本 API 的时候很有用
     */
    public const GONE = 410;

    /**
     * @Message("Unsupported Media Type")
     * 如果请求中的内容类型是错误的
     */
    public const UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * @Message("Unprocessable Entity")
     * 用来表示校验错误
     */
    public const UNPROCESSABLE_ENTITY = 422;

    /**
     * @Message("操作太频繁, 请稍候再试.")
     * 由于请求频次达到上限而被拒绝访问
     * Too Many Requests
     */
    public const TOO_MANY_REQUESTS = 429;

    /**
     * @Message("Internal Server Error")
     * 服务器遇到了一个未曾预料的状况，导致了它无法完成对请求的处理
     */
    public const SERVER_ERROR = 500;

    /**
     * @Message("Not Implemented")
     * 服务器不支持当前请求所需要的某个功能
     */
    public const NOT_IMPLEMENTED = 501;

    /**
     * @Message("Bad Gateway")
     * 作为网关或者代理工作的服务器尝试执行请求时，从上游服务器接收到无效的响应
     */
    public const BAD_GATEWAY = 502;

    /**
     * @Message("Service Unavailable")
     * 由于临时的服务器维护或者过载，服务器当前无法处理请求。这个状况是临时的，并且将在一段时间以后恢复。如果能够预计延迟时间，那么响应中可以包含一个 Retry-After
     * 头用以标明这个延迟时间（内容可以为数字，单位为秒；或者是一个 HTTP 协议指定的时间格式）。如果没有给出这个 Retry-After 信息，那么客户端应当以处理 500 响应的方式处理它
     */
    public const SERVICE_UNAVAILABLE = 503;

    /**
     * @Message("Gateway Timeout")
     */
    public const GATEWAY_TIMEOUT = 504;

    /**
     * @Message("HTTP Version Not Supported")
     */
    public const HTTP_VERSION_NOT_SUPPORTED = 505;

    /**
     * @Message("Variant Also Negotiates")
     */
    public const VARIANT_ALSO_NEGOTIATES = 506;

    /**
     * @Message("Insufficient Storage")
     */
    public const INSUFFICIENT_STORAGE = 507;

    /**
     * @Message("Loop Detected")
     */
    public const LOOP_DETECTED = 508;

    /**
     * @Message("Not Extended")
     */
    public const NOT_EXTENDED = 510;

    /**
     * @Message("Network Authentication Required")
     */
    public const NETWORK_AUTHENTICATION_REQUIRED = 511;

    /**
     * @Message("Network Connect Timeout Error")
     */
    public const NETWORK_CONNECT_TIMEOUT_ERROR = 599;
}
