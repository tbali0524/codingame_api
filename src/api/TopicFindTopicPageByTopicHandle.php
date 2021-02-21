<?php

declare(strict_types=1);

namespace CG\api;

use CG\MySelf;
use CG\CodinGameApi;

final class TopicFindTopicPageByTopicHandle extends CodinGameApi
{
    public string $topicHandle;

    public const SERVICE_URL = "Topic/findTopicPageByTopicHandle";

    public function __construct(string $_topicHandle = parent::DEFAULT_TOPIC_HANDLE)
    {
        $this->serviceURL = parent::BASE_URL . self::SERVICE_URL;
        $this->topicHandle = $_topicHandle;
        $this->requestJSON = '["' . $this->topicHandle . '"]';
    }
    // function __construct
}
