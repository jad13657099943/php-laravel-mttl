<?php


namespace Modules\Mttl\Services;


use Modules\Core\Services\Traits\HasQuery;
use Modules\Mttl\Models\UserDemotion;
use Modules\Mttl\Models\UserGradeRecord;

class UserGradeRecordService
{
    use HasQuery;

    public function __construct(UserGradeRecord $model)
    {
        $this->model = $model;
    }

    /**
     * 用户等级变化
     * @param integer $user_id
     * @param integer $old
     * @param integer $new
     * @throws
     */
    public function add($user_id, $old, $new)
    {
        $exclude_userid = array();
        if ($new < $old) {
            for ($i = $new; $i < $old; $i++) {
                $childGradeGroup = \DB::select("
                    SELECT
                        user_id
                    FROM
                        ti_project_user
                    WHERE
                        user_id IN (
                            SELECT
                                user_id
                            FROM
                                ti_user_invitation_tree
                            WHERE
                            JSON_CONTAINS( DATA, '{$user_id}' )
                        )
                    AND grade = {$i}
                ");

                if (isset($childGradeGroup[0])) {
                    foreach ($childGradeGroup as $group) {
                        $exclude_userid[] = $group->user_id;
                    }
                }
            }
        }

        // 增加记录
        $record = new UserGradeRecord();
        $record->user_id = $user_id;
        $record->old = $old;
        $record->new = $new;
        $record->type = $new < $old ? 2 : 1;
        $record->exclude_userid = $exclude_userid;
        $record->operation = 2;
        $record->save();
    }
}
