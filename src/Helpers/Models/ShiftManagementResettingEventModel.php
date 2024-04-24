<?php

namespace AWF\Extension\Helpers\Models;

class ShiftManagementResettingEventModel extends ObjectToArray
{
    public const DEFAULT = 'default';

    protected bool|null $success = null;
    protected string|null $status = null;

    public function __construct(bool|null $success = null, string|null $status = null)
    {
        $this->success = $success;
        $this->status = $status;
    }

    public function getSuccess(): bool|null
    {
        return $this->success;
    }

    public function setSuccess(bool|null $success): ShiftManagementResettingEventModel
    {
        $this->success = $success;
        return $this;
    }

    public function getStatus(): string|null
    {
        return $this->status;
    }

    public function setStatus(string|null $status): ShiftManagementResettingEventModel
    {
        $this->status = $status;
        return $this;
    }
}
