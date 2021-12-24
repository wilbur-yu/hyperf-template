<?php

declare(strict_types=1);

/**
 * This file is part of project burton.
 *
 * @author   wenbo@wenber.club
 * @link     https://github.com/wilbur-yu/hyperf-template
 */

namespace App\Listener;

use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Event\ValidatorFactoryResolved;

/**
 * 验证规则扩展
 * 扩展写法: 以后缀为Rule的方法即可
 * Class ValidatorFactoryResolvedListener.
 */
#[Listener]
class ValidatorFactoryResolvedListener implements ListenerInterface
{
    protected ValidatorFactoryInterface $validatorFactory;

    public function listen(): array
    {
        return [
            ValidatorFactoryResolved::class,
        ];
    }

    public function process(object $event): void
    {
        /*  @var ValidatorFactoryInterface $validatorFactory */
        $this->validatorFactory = $event->validatorFactory;

        ## 获取所有规则方法
        $ruleMethods = get_class_methods($this);
        array_walk($ruleMethods, function ($item, $key) {
            str_contains($item, 'Rule') && $this->{$item}();
        });
    }

    /**
     * 手机号验证
     */
    protected function phoneRule(): void
    {
        // 注册了 phone 验证器
        $this->validatorFactory->extend('phone', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match(
                '/^1((34[0-8]\d{7})|((3[0-3|5-9])|(4[5-7|9])|(5[0-3|5-9])|(66)|(7[2-3|5-8])|(8[\d])|(9[1|8|9]))\d{8})$/',
                $value
            );
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :phone 占位符
        $this->validatorFactory->replacer('phone', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.phone' && $message = ':phone 格式错误';

            return str_replace(':phone', $attribute, $message);
        });
    }

    /**
     * 汉字验证
     */
    protected function chineseRule(): void
    {
        // 注册了 chinese 验证器
        $this->validatorFactory->extend('chinese', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/[\x{4e00}-\x{9fa5}]+/u', $value);
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :chinese 占位符
        $this->validatorFactory->replacer('chinese', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.chinese' && $message = ':chinese 必须为汉字';

            return str_replace(':chinese', $attribute, $message);
        });
    }

    /**
     * 字母.数字.汉字验证
     */
    protected function alnumChineseRule(): void
    {
        // 注册了 alpha_num_chinese 验证器
        $this->validatorFactory->extend('alnum_chinese', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z0-9]+$/u', $value);
        });
        // 当创建一个自定义验证规则时，你可能有时候需要为错误信息定义自定义占位符这里扩展了 :alpha_num_chinese 占位符
        $this->validatorFactory->replacer('alnum_chinese', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.alnum_chinese' && $message = ':alnum_chinese 必须为汉字、字母、数字';

            return str_replace(':alnum_chinese', $attribute, $message);
        });
    }

    /**
     * 中英文验证
     * @return void
     */
    protected function chineseAndEnglishRule(): void
    {
        $this->validatorFactory->extend('chinese_and_english', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/^[\x{4e00}-\x{9fa5}a-zA-Z]+$/u', $value);
        });
        $this->validatorFactory->replacer('chinese_and_english', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.chinese_and_english' && $message = ':chinese_and_english 必须为汉字、字母、数字';

            return str_replace(':chinese_and_english', $attribute, $message);
        });
    }

    /**
     * 常用标点符号验证
     * @return void
     */
    protected function punctRule(): void
    {
        $this->validatorFactory->extend('punct', function ($attribute, $value, $parameters, $validator) {
            return (bool)preg_match('/^[[:punct:]{。、！？：；﹑＂…“”〝〞¸﹕︰﹔（）}]+$/u', $value);
        });
        $this->validatorFactory->replacer('punct', function ($message, $attribute, $rule, $parameters) {
            $message === 'validation.punct' && $message = ':punct 必须为汉字、字母、数字';

            return str_replace(':punct', $attribute, $message);
        });
    }
}
