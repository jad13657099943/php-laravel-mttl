<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Modules\Coin\Models\CoinExchangeSettings;

class SettingController extends Controller
{
    protected $schema = [
        // 集结奖励
        [
            'gather_1_5_reward' => [
                'key' => 'gather_1_5_reward',
                'type' => 'number',
                'title' => '1~5代奖励',
                'value' => '0.3',
                'description' => '%',
            ],
            'gather_6_10_reward' => [
                'key' => 'gather_6_10_reward',
                'type' => 'number',
                'title' => '6~10代奖励',
                'value' => '0.09',
                'description' => '%',
            ],
            'gather_11_20_reward' => [
                'key' => 'gather_11_20_reward',
                'type' => 'number',
                'title' => '11~20代奖励',
                'value' => '0.03',
                'description' => '%',
            ],
        ],
        // 级别奖励
        [
            'level_1_reward' => [
                'key' => 'level_1_reward',
                'type' => 'number',
                'title' => 'V1(火)',
                'value' => '1',
                'description' => '%',
            ],
            'level_2_reward' => [
                'key' => 'level_2_reward',
                'type' => 'number',
                'title' => 'V2(地)',
                'value' => '2',
                'description' => '%',
            ],
            'level_3_reward' => [
                'key' => 'level_3_reward',
                'type' => 'number',
                'title' => 'V3(风)',
                'value' => '3',
                'description' => '%',
            ],
            'level_4_reward' => [
                'key' => 'level_4_reward',
                'type' => 'number',
                'title' => 'V4(水)',
                'value' => '4',
                'description' => '%',
            ],
            'level_5_reward' => [
                'key' => 'level_5_reward',
                'type' => 'number',
                'title' => 'V5(以太)',
                'value' => '5',
                'description' => '%',
            ]
        ],
        // 平级奖励
        [
            'peer_reward' => [
                'key' => 'peer_reward',
                'type' => 'number',
                'title' => '平级奖励',
                'value' => '20',
                'description' => '%',
            ]
        ],
        // 质押设置
        [
            'pledge_amount' => [
                'key' => 'pledge_amount',
                'type' => 'number',
                'title' => '质押金额',
                'value' => '100',
                'description' => 'USDT',
            ],
            'pledge_grade' => [
                'key' => 'pledge_grade',
                'type' => 'number',
                'title' => '质押成为的等级',
                'value' => '1',
                'description' => '输入1-5之间的数字，代表V1-V5',
            ],
            'reward_recommend' => [
                'key' => 'reward_recommend',
                'type' => 'number',
                'title' => '推荐奖励',
                'value' => '300',
                'description' => 'USDT',
            ],
            'take_away' => [
                'key' => 'take_away',
                'type' => 'number',
                'title' => '取走质押所需社群奖励',
                'value' => '90000',
                'description' => 'USDT',
            ],
        ],
        // 兑换配置
        [
            'exchange_open' => [
                'key' => 'exchange_open',
                'type' => 'number',
                'title' => '开启兑换功能',
                'value' => '1',
                'description' => '1为开启兑换，0为关闭兑换',
            ],
            'exchange_ratio' => [
                'key' => 'exchange_ratio',
                'type' => 'number',
                'title' => '兑换汇率',
                'value' => '1',
                'description' => 'USDT兑BMTC的汇率',
            ],
        ]
    ];


    /**
     * @return Factory|View
     */
    public function index()
    {
        return view('user::admin.setting.index', [
            'configList' => $this->normalizeSchema(config('user::config', []), $this->schema[0]),
            //其他类型设置
            'configList2' => $this->normalizeSchema(config('user::config', []), $this->schema[1]),
            'configList3' => $this->normalizeSchema(config('user::config', []), $this->schema[2]),
            'configList4' => $this->normalizeSchema(config('user::config', []), $this->schema[3]),
            'configList5' => $this->normalizeSchema(config('user::config', []), $this->schema[4]),
        ]);
    }


    /**
     * @param array $data
     * @param array $config
     * @return array|array[]
     */
    protected function normalizeSchema(array $data, array $config)
    {
        return array_map(function ($value) use ($data) {
            return array_merge($value, [
                'value' => $data[$value['key']] ?? $value['value'],
            ]);
        }, $config);
    }


    /**
     * @param Request $request
     * @return RedirectResponse
     */
    public function update(Request $request)
    {
        $post = $request->post();
        unset($post['_token']);

        store_config('user::config', $post);

        if (isset($post['exchange_open'])) {
            CoinExchangeSettings::query()
                ->where('coin_pay', 'USDT')
                ->where('coin_get', 'BMTC')
                ->update(['status' => $post['exchange_open']]);
        }
        if (isset($post['exchange_ratio'])) {
            CoinExchangeSettings::query()
                ->where('coin_pay', 'USDT')
                ->where('coin_get', 'BMTC')
                ->update(['ratio' => $post['exchange_ratio']]);
        }

        return response()->redirectTo(route('m.user.admin.setting.index'));
    }
}
