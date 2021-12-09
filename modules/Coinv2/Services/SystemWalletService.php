<?php


namespace Modules\Coinv2\Services;

use Leonis\Notifications\EasySms\Channels\EasySmsChannel;
use Modules\Coin\Notifications\SystemWalletMinBalance;
use Notification;
use Modules\Coinv2\Components\TokenIO\TokenIO;
use Modules\Coin\Exceptions\AdminCommonException;
use Modules\Coin\Exceptions\SystemWalletNotFoundException;
use Modules\Coin\Models\CoinSystemWallet;
use Modules\Coin\Notifications\SystemWalletMaxBalance;
use Modules\Core\Services\Traits\HasQuery;

class SystemWalletService
{
    use HasQuery {
        one as queryOne;
    }

    /**
     * @var CoinSystemWallet
     */
    protected $model;

    public function __construct(CoinSystemWallet $model)
    {
        $this->model = $model;
    }

    /**
     * @param $chain
     * @param array $options
     *
     * @return CoinSystemWallet
     */
    public function getColdWalletByChain($chain, array $options = [])
    {
        return $this->one([
            'chain' => $chain,
            'level' => CoinSystemWallet::LEVEL_COLD_WALLET,
        ], $options);
    }

    /**
     * @param $chain
     * @param array $options
     *
     * @return CoinSystemWallet
     */
    public function getWithdrawWalletByChain($chain, $tokenioVersion = 2, array $options = [])
    {
        return $this->one([
            'chain' => $chain,
            'type' => CoinSystemWallet::TYPE_WITHDRAW,
            'tokenio_version' => $tokenioVersion
        ], $options);
    }

    /**
     * @param \Closure|array|null $where
     * @param array $options
     *
     * @return CoinSystemWallet
     */
    public function one($where = null, array $options = [])
    {
        return $this->queryOne($where, array_merge([
            'exception' => function () use ($where) {
                return SystemWalletNotFoundException::withData($where);
            },
        ], $options));
    }


    /**
     * 创建系统钱包
     * @param array $data
     * @param array $option
     * @return bool|\Illuminate\Database\Eloquent\Model
     * @throws AdminCommonException
     * @throws \Modules\Core\Exceptions\ModelSaveException
     */
    public function createWallet(array $data, $option = [])
    {

        //不是有tokenio生成或者生成冷钱包时，需要填写钱包地址直接插入数据
        if ($data['is_tokenio'] == 0 || $data['level'] == 0) {
            if (empty($data['address'])) {
                throw new AdminCommonException(trans('coin::exception.请填写钱包地址'));
            }
        } else {
            //由tokenio生成热钱包，调用生成接口
            //系统热钱包生成方式：account=0，index=1118+N
            $tokenIo = resolve(TokenIO::class);
            $account = 0;
            $index = CoinSystemWallet::query()->where('tokenio_version', 2)
                ->where('tokenio_version', 2)
                ->where('chain', $data['chain'])
                ->count();
            $index = $index + 1118;
            $res = $tokenIo->newWallet($data['chain'], $account, $index);
            if ($res['code'] != 0) {
                throw new \Exception('TokenioV2生成钱包地址失败');
            }

            $data['address'] = $res['data']['address'];

            if (empty($data['address'])) {
                throw new AdminCommonException(trans('coin::exception.TOKENIO生成系统钱包接口失败'));
            }
        }

        //判断唯一处理，存在即不插入
        $haveAddress = $this->one([
            'address' => $data['address'],
            'chain' => $data['chain'],
            'type' => $data['type']
        ], [
            'exception' => false,
        ]);

        if ($haveAddress) {
            throw new AdminCommonException(trans('coin::exception.该主链对应的钱包类型已经创建存在了'));
        }

        return $this->create($data);
    }


    /**
     * 查询系统钱包余额
     * 大于设置值或小于设置时
     * 发送提示
     */
    public function checkBalance()
    {
        $tokenIO = resolve(TokenIO::class);
        $this->query([
            'queryCallback' => function ($query) {
                $query->whereNotNull('notice')
                    ->where(function ($query) {
                        $query->where('notice_min', '>', 0)
                            ->orwhere('notice_max', '>', 0);
                    });
            },
        ])->chunk(50, function ($list) use ($tokenIO) {


            foreach ($list as $wallet) {

                ['balance' => $balance] = $tokenIO->chainBalance($wallet->chain, $wallet->address);

                if (filter_var($wallet->notice, FILTER_VALIDATE_EMAIL)) { // 邮箱发送
                    $route = 'mail';
                } else { // 默认手机号
                    $route = EasySmsChannel::class;
                }

                if ($balance >= $wallet->notice_max) { // 大于设置阈值，提醒

                    $msg = '系统钱包' . $wallet->chain . $wallet->remark . $wallet->address
                        . '钱包余额' . $balance . "已超过设置阈值" . $wallet->notice_max;

                    Notification::route($route, $wallet->notice)
                        ->notify(new SystemWalletMaxBalance($msg));
                }

                if ($balance <= $wallet->notice_min) { // 小于阈值，提醒

                    $msg = '系统钱包' . $wallet->chain . $wallet->remark . $wallet->address
                        . '钱包余额' . $balance . "已不足最小值" . $wallet->notice_max;

                    Notification::route($route, $wallet->notice)
                        ->notify(new SystemWalletMinBalance($msg));
                }
            }
        });
    }
}
