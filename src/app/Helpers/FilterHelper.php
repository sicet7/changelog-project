<?php

namespace App\Helpers;

use App\Database\Entities\Log;
use App\Database\Repositories\LogEntryRepository;
use Doctrine\Common\Collections\Criteria;
use Psr\Http\Message\ServerRequestInterface as Request;
use Respect\Validation\Validator as v;

class FilterHelper
{
    public const FIELD_ALL = 'all';
    public const FIELD_INITIATED_BY = 'initiatedBy';
    public const FIELD_TECH = 'tech';
    public const FIELD_CHANGE_DESC = 'changeDescription';
    public const FIELD_DEVICE = 'device';
    public const FIELD_ROLLBACK_DESC = 'rollbackDescription';
    public const FIELD_CREATED_AT = 'createdAt';

    public const VALID_FIELDS = [
        self::FIELD_ALL,
        self::FIELD_INITIATED_BY,
        self::FIELD_TECH,
        self::FIELD_CHANGE_DESC,
        self::FIELD_DEVICE,
        self::FIELD_ROLLBACK_DESC,
        self::FIELD_CREATED_AT,
    ];

    public const PARAMS = [
        'page',
        'size',
        'filter',
        'value',
        'sort',
        'dir',
    ];

    public const VALID_DIRS = [
        'DESC',
        'ASC'
    ];

    /**
     * @var LogEntryRepository
     */
    private LogEntryRepository $logEntryRepository;

    /**
     * @var Base64Helper
     */
    private Base64Helper $base64Helper;

    /**
     * FilterHelper constructor.
     * @param LogEntryRepository $logEntryRepository
     * @param Base64Helper $base64Helper
     */
    public function __construct(LogEntryRepository $logEntryRepository, Base64Helper $base64Helper)
    {
        $this->logEntryRepository = $logEntryRepository;
        $this->base64Helper = $base64Helper;
    }

    /**
     * @param Log $log
     * @param Request $request
     * @return array[]
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function processEntryFilters(Log $log, Request $request)
    {
        $criteria = Criteria::create();
        $sorting = $this->sorting($request, $criteria);
        $filter = $this->filters($request, $criteria);
        $pagination = $this->pagination($request, $log, $criteria);
        $data = $this->logEntryRepository->getEntries($log, $criteria);
        return [
            'entries' => [
                'data' => $data,
                'pagination' => $pagination,
                'filter' => $filter,
                'sorting' => $sorting,
                'fields' => [
                    self::FIELD_ALL => 'All',
                    self::FIELD_INITIATED_BY => 'Initiated By',
                    self::FIELD_TECH => 'Tech',
                    self::FIELD_CHANGE_DESC => 'Change Description',
                    self::FIELD_DEVICE => 'Device',
                    self::FIELD_ROLLBACK_DESC => 'Rollback Description',
                    self::FIELD_CREATED_AT => 'Performed',
                ]
            ]
        ];
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @return array
     */
    protected function sorting(Request $request, Criteria $criteria)
    {
        $data = $request->getQueryParams();
        $field = 'createdAt';
        $direction = 'DESC';
        if (v::key('sort', v::in(self::VALID_FIELDS))->validate($data)) {
            $field = $data['sort'];
        }

        if (v::key('dir', v::in(self::VALID_DIRS))->validate($data)) {
            $direction = $data['dir'];
        }
        $criteria->orderBy([$field => $direction]);

        return [
            'field' => $field,
            'dir' => $direction
        ];
    }

    /**
     * @param Request $request
     * @param Criteria $criteria
     * @return array
     */
    protected function filters(Request $request, Criteria $criteria)
    {
        $data = $request->getQueryParams();
        if (!v::allOf(
            v::key('filter', v::in(self::VALID_FIELDS)),
            v::key('value', v::stringType()->notEmpty())
        )->validate($data)) {
            return [
                'field' => self::FIELD_ALL,
                'value' => ''
            ];
        }
        $filter = $data['filter'];
        $value = $this->base64Helper->urlsafeDecode($data['value']);

        if (strlen(trim($value)) < 2) {
            return [
                'field' => self::FIELD_ALL,
                'value' => ''
            ];
        }

        if ($filter == self::FIELD_CREATED_AT) {
            $values = array_map('trim', explode('to', strtolower($value)));
            if (!v::each(v::stringType()->date('d-m-Y'))->validate($values)) {
                return [];
            }
            if (count($values) == 1) {
                $start = \DateTime::createFromFormat('d-m-Y', $values[0]);
                $stop = \DateTime::createFromFormat('d-m-Y', $values[0]);
            } else {
                $start = \DateTime::createFromFormat('d-m-Y', $values[0]);
                $stop = \DateTime::createFromFormat('d-m-Y', $values[1]);
            }
            $start->setTime(0,0,1);
            $stop->setTime(23,59,59);
            $criteria->where(Criteria::expr()->gte($filter, $start->format('Y-m-d H:i:s')))
                ->andWhere(Criteria::expr()->lte($filter, $stop->format('Y-m-d H:i:s')));
            return [
                'field' => $filter,
                'value' => [
                    $start->format('d-m-Y'),
                    $stop->format('d-m-Y'),
                ]
            ];
        }

        if ($filter == self::FIELD_ALL) {
            $first = true;
            foreach (self::VALID_FIELDS as $field) {
                if (in_array($field, [self::FIELD_ALL, self::FIELD_CREATED_AT])) {
                    continue;
                }
                if ($first) {
                    $criteria->where(Criteria::expr()->contains($field, $value));
                    $first = false;
                } else {
                    $criteria->orWhere(Criteria::expr()->contains($field, $value));
                }
            }
            return [
                'field' => self::FIELD_ALL,
                'value' => $value,
            ];
        }

        $criteria->where(Criteria::expr()->contains($filter, $value));
        return [
            'field' => $filter,
            'value' => $value,
        ];
    }

    /**
     * @param Request $request
     * @param Log $log
     * @param Criteria $criteria
     * @return array
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    protected function pagination(Request $request, Log $log, Criteria $criteria)
    {
        $maxCount = $this->logEntryRepository->getEntryCount($log, $criteria);
        $size = $this->getNumericQueryParam($request, 'size', 20, 200);
        $highestPage = (int)ceil(($maxCount/$size));
        $page = $this->getNumericQueryParam($request, 'page', 1, $highestPage);
        $offset = ($page-1)*$size;
        $criteria->setFirstResult($offset)
            ->setMaxResults($size);
        $pages = [];
        $i = $page-5;
        while(count($pages) < 5 && $i < $page+20) {
            if ($i > 0 && $i <= $highestPage) {
                $pages[$i] = $this->getPageUrl($i);
            }
            $i++;
        }
        return [
            'current' => $page,
            'max' => $highestPage,
            'pages' => $pages,
            'previous' => ($page-1 > 0 ? $this->getPageUrl($page-1) : '#'),
            'next' => ($page+1 <=  $highestPage  ? $this->getPageUrl($page+1) : '#'),
        ];
    }

    /**
     * @param Request $request
     * @param string $name
     * @param int|string $default
     * @param int|string $max
     * @return mixed
     */
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

    /**
     * @param int|string $i
     * @return string
     */
    protected function getPageUrl($i)
    {
        $uri = $_SERVER['DOCUMENT_URI'];
        $gets = $_GET;
        $gets['page'] = $i;
        return $uri . '?' . http_build_query($gets);
    }

    /**
     * @return string
     */
    protected function getResetUrl()
    {
        $uri = $_SERVER['DOCUMENT_URI'];
        $gets = $_GET;
        foreach ($gets as $key => $get) {
            if (in_array($key, self::PARAMS)) {
                unset($gets[$key]);
            }
        }
        $query = (empty($gets) ? '' : '?' . http_build_query($gets));
        return $uri . $query;
    }
}