<?php

namespace Modules\Coin\Services;

use Illuminate\Database\Eloquent\Builder;
use Modules\Coin\Exceptions\CoinException;
use Modules\Coin\Models\CoinLogModules;
use Modules\Core\Exceptions\DataExistsException;
use Modules\Core\Services\Traits\HasConfigQuery;
use Illuminate\Support\Facades\DB;

class CoinLogModuleService
{
    use HasConfigQuery {
        one as queryOne;
        all as queryAll;
    }

    public $key = 'coin::log_modules';
    /**
     * @var CoinLogModules
     */
    protected $actionModel;

    /**
     * @param array $options
     * @return Builder
     */
    protected function actionQuery(array $options = []): Builder
    {
        if ($this->actionModel === null) {
            $this->actionModel = resolve(CoinLogModules::class);
        }

        $query = $this->actionModel->newQuery();

        if (empty($options)) {
            return $query;
        }

        return $this->withActionQueryOptions($query, $options);
    }

    /**
     * @param $query
     * @param array $options
     */
    protected function withActionQueryOptions(Builder $query, array $options): Builder
    {
        if ($where = $options['where'] ?? false) {
            $query->where($where);
        }

        if ($whereIn = $options['whereIn'] ?? false) {
            foreach ($whereIn as $key => $values) {
                $query->whereIn($key, $values);
            }
        }

        if ($with = $options['with'] ?? false) {
            $query->with($with);
        }

        if ($orderBy = $options['orderBy'] ?? false) {
            call_user_func_array([$query, 'orderBy'], !is_array($orderBy) ? [$orderBy] : $orderBy);
        }

        if ($callback = $options['queryCallback'] ?? false) {
            $callback($query);
        }

        return $query;
    }

    /**
     * @param null $where
     * @param array $options
     *
     * @return mixed
     */
    public function one($where = null, array $options = [])
    {
        $module = $this->queryOne($where, $options);

        if ($module) {
            $module['actions'] = $this->getActionsByModule($module['key']);
        }

        return $module;
    }

    /**
     * @param \Closure|array|null $where
     * @param array $options
     */
    public function all($where = [], array $options = [])
    {

        return collect($this->queryAll($where, $options))
            ->map(function ($module, $key){
                $module['actions'] = $this->getActionsByModule($module['key']);
                return $module;
            });
    }

    /**
     * @param array $where
     * @param array $options
     *
     * @return mixed
     */
    public function getModules($where = [], array $options = []) // TODO 删除?
    {
        return $this->all($where, $options);
    }

    /**
     * 获取模块
     *
     * @param $key string 模块标识
     * @return mixed|null
     */
    public function getModuleByName($moduleName, array $options = [])
    {
        return $this->getByKey($moduleName, array_merge([
            'exception' => function () use ($moduleName) {
                throw new CoinException(trans('coin::exception.模块未找到', ['module' => $moduleName]));
            }
        ], $options));
    }

    /**
     * 添加模块
     *
     * @param $module 模块标识
     * @param $name 模块名称
     * @param array $options
     * @return Array
     */
    public function addModule($module, $name, array $options = [])
    {
        //$this->deleteByKey($module);
        return $this->create([
            'key' => $module,
            'name' => $name
        ], $options);
    }

    /**
     * @param $module
     * @param array $options
     * @return bool
     * @throws CoinException
     */
    public function hasModule($module, array $options = [])
    {
        if (!$this->has(['key' => $module])) {
            if ($options['exception'] ?? true) {
                throw new CoinException(trans('coin::exception.模块未定义', ['module' => $module]));
            }
            return false;
        }

        return true;
    }

    /**
     * 添加动作
     * @param $module 模块标识
     * @param $action 动作标识
     * @param $title 动作名称
     * @param $remark 备注
     * @param array $options
     * @return bool|Model
     * @throws \Modules\Core\Exceptions\ModelSaveException
     */
    public function addAction($module, $action, $title, $remark, array $options = [])
    {
        $this->hasModule($module);

        $actionModel = $this->actionQuery()->where([
            'module' => $module,
            'action' => $action
        ])->first();

        if ($actionModel) {
            if (!($options['force'] ?? false)) {
                throw new DataExistsException(trans('coin::exception.动作已存在', ['action' => $action]));
            }
            $actionModel->fill([
                'title' => $title,
                'remark' => $remark,
                'enable' => $options['is_enable'] ?? 1
            ]);
        } else {
            $actionModel = $this->actionModel->newInstance([
                'module' => $module,
                'action' => $action,
                'title' => $title,
                'remark' => $remark,
                'enable' => $options['is_enable'] ?? 1
            ]);
        }

        $actionModel->saveOrFail();

        return $actionModel;
    }

    /**
     * 批量添加动作
     * @param $module 模块标识
     * @param array $data 动作集合
     * @param array $options
     * @throws \Throwable
     */
    public function addActions($module, array $data, array $options = [])
    {
        // TODO 优化批量插入
        DB::transaction(function () use ($data, $module, $options) {
            foreach ($data as $item) {
                $this->addAction(
                    $module,
                    $item['action'],
                    $item['title'],
                    $item['remark'],
                    $options
                );
            }
        });
        return true;
    }

    /**
     * 根据模块标识查找动作列表
     *
     * @param $name string 模块标识
     * @param array $options
     */
    public function getActionsByModule($module, array $options = [])
    {
        $options['where'] = array_merge($options['where'] ?? [], ['module' => $module]);
        $query = $this->actionQuery($options);

        if ($options['paginate'] ?? false) {
            $limit = $options['limit'] ?? request('limit', 15);
            $pageName = $options['pageName'] ?? 'page';
            $columns = $options['columns'] ?? ['*'];
            $page = $options['page'] ?? null;

            $maxLimit = $options['maxLimit'] ?? config('core::system.paginate_maxLimit', 100);
            if ($limit > $maxLimit) {
                $limit = $maxLimit;
            }

            return $query->paginate($limit, $columns, $pageName, $page);
        }

        return $query->get();
    }

    public function title($module)
    {
        $module = explode('.', $module);
        return $this->actionQuery()->where([
            'module' => $module[0],
            'action' => $module[1]
        ])->value('title'); // TODO 不应该value返回  应该直接返回数据 让controller来处理value数据, 否则视逻辑来处理
    }
}
