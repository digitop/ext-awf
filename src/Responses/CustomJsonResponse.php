<?php

namespace AWF\Extension\Responses;

use AWF\Extension\Helpers\Responses\JsonResponseModel;
use Illuminate\Http\JsonResponse;

class CustomJsonResponse extends JsonResponse
{
    /**
     * Create a new JSON response instance.
     *
     * @param JsonResponseModel $responseModel
     */
    public function __construct(JsonResponseModel $responseModel)
    {
        parent::__construct(
            $responseModel->getResponseData(),
            $responseModel->getStatus(),
            $responseModel->getHeaders(),
            $responseModel->isJson()
        );
    }
}
