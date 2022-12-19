<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsResponseBody;

use AlibabaCloud\SDK\Dysmsapi\V20170525\Models\QuerySendDetailsResponseBody\smsSendDetailDTOs\smsSendDetailDTO;
use AlibabaCloud\Tea\Model;

class smsSendDetailDTOs extends Model
{
    /**
     * @var smsSendDetailDTO[]
     */
    public $smsSendDetailDTO;
    protected $_name = [
        'smsSendDetailDTO' => 'SmsSendDetailDTO',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->smsSendDetailDTO) {
            $res['SmsSendDetailDTO'] = [];
            if (null !== $this->smsSendDetailDTO && \is_array($this->smsSendDetailDTO)) {
                $n = 0;
                foreach ($this->smsSendDetailDTO as $item) {
                    $res['SmsSendDetailDTO'][$n++] = null !== $item ? $item->toMap() : $item;
                }
            }
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return smsSendDetailDTOs
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['SmsSendDetailDTO'])) {
            if (!empty($map['SmsSendDetailDTO'])) {
                $model->smsSendDetailDTO = [];
                $n                       = 0;
                foreach ($map['SmsSendDetailDTO'] as $item) {
                    $model->smsSendDetailDTO[$n++] = null !== $item ? smsSendDetailDTO::fromMap($item) : $item;
                }
            }
        }

        return $model;
    }
}
