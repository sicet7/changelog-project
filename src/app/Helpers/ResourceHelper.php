<?php

namespace App\Helpers;

use App\Database\Entities\Log;
use App\Database\Repositories\LogRepository;

class ResourceHelper
{
    /**
     * @var bool
     */
    private bool $loadLogs = false;

    /**
     * @var bool
     */
    private bool $editMode = false;

    /**
     * @var mixed
     */
    private $entryMode = false;

    /**
     * @var Log|null
     */
    private ?Log $selected = null;

    /**
     * @var LogRepository
     */
    private LogRepository $logRepository;

    /**
     * ResourceHelper constructor.
     * @param LogRepository $logRepository
     */
    public function __construct(LogRepository $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * @return Log|null
     */
    public function getSelected(): ?Log
    {
        return $this->selected;
    }

    /**
     * @param Log|null $selected
     */
    public function setSelected(?Log $selected): void
    {
        $this->selected = $selected;
    }

    /**
     * @return bool
     */
    public function isLoadLogs(): bool
    {
        return $this->loadLogs;
    }

    /**
     * @param bool $loadLogs
     */
    public function setLoadLogs(bool $loadLogs): void
    {
        $this->loadLogs = $loadLogs;
    }

    /**
     * @return bool
     */
    public function isEditMode(): bool
    {
        return $this->editMode;
    }

    /**
     * @param bool $editMode
     */
    public function setEditMode(bool $editMode): void
    {
        $this->editMode = $editMode;
    }

    /**
     * @return mixed
     */
    public function getEntryMode()
    {
        return $this->entryMode;
    }

    /**
     * @param mixed $entryMode
     */
    public function setEntryMode($entryMode): void
    {
        $this->entryMode = $entryMode;
    }

    /**
     * @return array
     */
    public function getLoadedArray(): array
    {
        $returnArray = [];

        if ($this->isLoadLogs()) {
            $returnArray['logs'] = $this->getLogs();
        }

        if ($this->getSelected() !== null) {
            $log = $this->getSelected();
            $returnArray['selected'] = $this->getSelectedArray($log);
        }

        if ($this->getEntryMode() !== false) {
            $returnArray['entry_mode'] = $this->getEntryMode();
        }

        return $returnArray;
    }

    /**
     * @return array[]
     */
    protected function getLogs()
    {
        return array_map(function(Log $log) {
            return [
                'id' => $log->getId(),
                'name' => $log->getName(),
                'link' => '/changelogs/' . $log->getId()
            ];
        }, $this->logRepository->getAllLogs());
    }

    protected function getSelectedArray(Log $log)
    {
        $returnArray = [
            'id' => $log->getId(),
            'name' => $log->getName(),
            'description' => $log->getDescription(),
            'show_link' => '/changelogs/' . $log->getId(),
            'edit_link' => '/changelogs/' . $log->getId() . '/edit',
            'delete_link' => '/changelogs/' . $log->getId() . '/delete',
            'new_entry_link' => '/changelogs/' . $log->getId() . '/entry/new',
        ];

        if ($this->isEditMode()) {
            $returnArray['edit_mode'] = true;
        }

        return $returnArray;
    }
}