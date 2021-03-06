<?php

namespace Modules\Ivehicles\Repositories\Eloquent;

use Modules\Core\Repositories\Eloquent\EloquentBaseRepository;
use Modules\Ivehicles\Repositories\BrandRepository;
use Illuminate\Database\Eloquent\Builder;

class EloquentBrandRepository extends EloquentBaseRepository implements BrandRepository
{
    /**
     * @param bool $params
     * @return mixed
     */
    public function getItemsBy($params = false)
    {

        $query = $this->model->query();

        if (in_array('*', $params->include)) {
            $query->with([]);
        } else {
            $includeDefault = ['translations'];
            if (isset($params->include))
                $includeDefault = array_merge($includeDefault, $params->include);
            $query->with($includeDefault);
        }


        if (isset($params->filter)) {
            $filter = $params->filter;

            if (isset($filter->date)) {
                $date = $filter->date;
                $date->field = $date->field ?? 'created_at';
                if (isset($date->from))
                    $query->whereDate($date->field, '>=', $date->from);
                if (isset($date->to))
                    $query->whereDate($date->field, '<=', $date->to);
            }


            if (isset($filter->order)) {
                $orderByField = $filter->order->field ?? 'created_at';
                $orderWay = $filter->order->way ?? 'desc';
                $query->orderBy($orderByField, $orderWay);
            }
        }


        if (isset($params->fields) && count($params->fields))
            $query->select($params->fields);


        if (isset($params->page) && $params->page) {
            return $query->paginate($params->take);
        } else {
            $params->take ? $query->take($params->take) : false;
            return $query->get();
        }
    }

    /**
     * @param $criteria
     * @param bool $params
     * @return mixed
     */
    public function getItem($criteria, $params = false)
    {

        $query = $this->model->query();


        if (in_array('*', $params->include)) {
            $query->with([]);
        } else {
            $includeDefault = ['translations'];
            if (isset($params->include))
                $includeDefault = array_merge($includeDefault, $params->include);
            $query->with($includeDefault);
        }

        if (isset($params->filter)) {
            $filter = $params->filter;

            if (isset($filter->field))
                $field = $filter->field;
        }


        if (isset($params->fields) && count($params->fields))
            $query->select($params->fields);


        return $query->where($field ?? 'id', $criteria)->first();
    }

    /**
     * @inheritdoc
     */
    public function findBySlug($slug)
    {
        if (method_exists($this->model, 'translations')) {
            return $this->model->whereHas('translations', function (Builder $q) use ($slug) {
                $q->where('slug', $slug);
            })->with('translations','vehicles')->active()->firstOrFail();
        }

        return $this->model->where('slug', $slug)->with('modelsVehicles','vehicles')->active()->firstOrFail();

    }
}
