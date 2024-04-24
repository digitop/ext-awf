<?php

namespace AWF\Extension\Helpers\Responses;

use AWF\Extension\Helpers\Models\ObjectToArray;

class NextProductResponseData extends ObjectToArray
{
    protected bool $success = false;
    protected array $nextSequence = [];
    protected string $message = '';

    public function __construct(bool $success = false, array $data = [], string $message = '')
    {
        $this->success = $success;
        $this->nextSequence = $data;
        $this->message = $message;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function setSuccess(bool $success): NextProductResponseData
    {
        $this->success = $success;
        return $this;
    }

    public function getData(): array
    {
        return $this->nextSequence;
    }

    public function setData(array $data): NextProductResponseData
    {
        $this->nextSequence = $data;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): NextProductResponseData
    {
        $this->message = $message;
        return $this;
    }
}