<?php
namespace Zodream\Domain\ThirdParty\WeChat\Platform;


use Zodream\Domain\ThirdParty\WeChat\Aes;
use Zodream\Infrastructure\Base\MagicObject;
use Zodream\Infrastructure\ObjectExpand\XmlExpand;
use Zodream\Infrastructure\Traits\EventTrait;

/**
 * 推送给平台授权相关通知
 * @package Zodream\Domain\ThirdParty\WeChat\Platform
 * @property string $componentVerifyTicket
 * @property string $appId
 * @property string $createTime
 * @property string $infoType  unauthorized是取消授权，updateauthorized是更新授权，authorized是授权成功通知
 * @property string $authorizerAppid 公众号
 * @property string $authorizationCode   授权码，可用于换取公众号的接口调用凭据
 * @property string $authorizationCodeExpiredTime  授权码过期时间
 */
class Notify extends MagicObject {
    use EventTrait;
    const component_verify_ticket = 'component_verify_ticket';
    const unauthorized = 'unauthorized';
    const updateauthorized = 'updateauthorized';
    const authorized = 'authorized';

    protected $xml;

    public function get($key = null, $default = null) {
        if (empty($this->_data)) {
            $this->setData();
        }
        return parent::get(lcfirst($key), $default);
    }

    public function setData() {
        if (empty($this->xml)) {
            $this->xml = Request::input();
        }
        if (!empty($this->xml)) {
            $args = $this->getData();
            foreach ($args as $key => $item) {
                $this->set(lcfirst($key), $item);
            }
        }
        return $this;
    }

    public function getXml() {
        return $this->xml;
    }

    protected function getData() {
        $data = (array)XmlExpand::decode($this->xml, false);
        $encryptStr = $data['Encrypt'];
        $aes = new Aes($this->aesKey, $this->appId);
        $this->xml = $aes->decrypt($encryptStr);
        $this->appId = $aes->getAppId();
        return (array)XmlExpand::decode($this->xml, false);
    }

    public function run() {
        $this->invoke($this->getEvent(), [$this, $response]);
        return 'success';
    }
}