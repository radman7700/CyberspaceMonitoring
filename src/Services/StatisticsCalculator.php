<?php 

namespace Pishgaman\CyberspaceMonitoring\Services;

use Pishgaman\CyberspaceMonitoring\Repositories\TelegramGroupRepository;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;

class StatisticsCalculator
{
    protected $groupRepository;
    protected $messageRepository;

    public function __construct(TelegramGroupRepository $groupRepository, TelegramMessageRepository $messageRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->messageRepository = $messageRepository;
    }

    public function getTelegramGroupCount()
    {
        return $this->groupRepository->groupCount();
    }

    public function getTelegramMessageCount()
    {
        return $this->messageRepository->getIdCount();
    }

    public function getTelegramSessionCount()
    {
        return $this->groupRepository->sessionCount();
    }

    public function ReleaseProcess(array $options)
    {
        return $this->messageRepository->getMessageCounts($options);
    }    
}
