<?php 

namespace Pishgaman\CyberspaceMonitoring\Services;

use Pishgaman\CyberspaceMonitoring\Repositories\TelegramGroupRepository;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramMessageRepository;
use Pishgaman\CyberspaceMonitoring\Repositories\TelegramUsersRepository;

class StatisticsCalculator
{
    protected $groupRepository;
    protected $messageRepository;
    protected $UsersRepository;

    public function __construct(TelegramGroupRepository $groupRepository, TelegramMessageRepository $messageRepository,TelegramUsersRepository $UsersRepository)
    {
        $this->groupRepository = $groupRepository;
        $this->messageRepository = $messageRepository;
        $this->UsersRepository = $UsersRepository;
    }

    public function getTelegramGroupCount($type='group')
    {
        return $this->groupRepository->groupCount($type);
    }

    public function getTelegramMessageCount($options)
    {
        return $this->messageRepository->TelegramMessageGet($options);
    }

    public function getTelegramSessionCount()
    {
        return $this->groupRepository->sessionCount();
    }

    public function getTelegramUsersCount()
    {
        return $this->UsersRepository->getIdCount();
    } 

    public function ReleaseProcess(array $options)
    {
        return $this->messageRepository->getMessageCounts($options);
    }    
}
