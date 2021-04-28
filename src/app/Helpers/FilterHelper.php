<?php

namespace App\Helpers;

use App\Database\Entities\Log;
use App\Database\Repositories\LogEntryRepository;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class FilterHelper
{
    /**
     * @var LogEntryRepository
     */
    private LogEntryRepository $logEntryRepository;

    /**
     * FilterHelper constructor.
     * @param LogEntryRepository $logEntryRepository
     */
    public function __construct(LogEntryRepository $logEntryRepository)
    {
        $this->logEntryRepository = $logEntryRepository;
    }


    public function processEntryFilters(Log $log, Request $request)
    {
        if (($result = $this->processWithFilters($log, $request)) !== false) {
            return $result;
        }
        $total = $this->logEntryRepository->getEntryCount($log);
        $pageSize = $this->getNumericQueryParam($request, 'size', 20, 200);
        $highestPage = (int)ceil(($total/$pageSize));
        $page = $this->getNumericQueryParam($request, 'page', 1, $highestPage);
        $offset = ($page-1)*$pageSize;
        $data = $log->getEntries()->slice($offset, $pageSize);
        /** @var \DateTimeImmutable $imt */
        /*$imt = $data[0]->getCreatedAt();
        $target = new \DateTimeZone('Europe/Copenhagen');
        $new = \DateTime::createFromImmutable($imt);
        var_dump($new->add(new \DateInterval('PT' . $target->getOffset($imt) . 'S'))->format('d-m-Y H:i:s'));
        die;*/
        return [
            'entries' => [
                'data' => $data,
                'page' => $page,
                'size' => $pageSize,
                'total' => $total,
                'max' => $highestPage
            ]
        ];
    }

    /**
     * @param Log $log
     * @param Request $request
     * @return array|bool
     */
    protected function processWithFilters(Log $log, Request $request)
    {
        return false;
    }

    protected function getNumericQueryParam(Request $request, string $name, $default, $max)
    {
        $hasValue = v::arrayType()->notEmpty()->key($name,
            v::notEmpty()->numericVal()->positive()
        )->validate($request->getQueryParams());
        if ($hasValue) {
            $value = $request->getQueryParams()[$name];
            if ($value > $max) {
                return $max;
            }
            return $value;
        }
        return $default;
    }

}