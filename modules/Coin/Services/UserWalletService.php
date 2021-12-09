<?php

namespace Modules\Coin\Services;

use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Exceptions\UserWalletNotFoundException;
use Modules\Coin\Models\CoinUserWallet;
use Modules\Core\Services\Traits\HasQuery;

class UserWalletService
{
    use HasQuery {
        one as queryOne;
    }

    /**
     * @var CoinUserWallet
     */
    protected $model;

    public function __construct(CoinUserWallet $model)
    {
        $this->model = $model;
    }

    /**
     * 跟去钱包地址获取用户钱包
     *
     * @param $address
     * @param array $options
     *
     * @return CoinUserWallet
     */
    public function getByAddress($address, array $options = [])
    {
        return $this->one(['address' => $address], $options);
    }

    /**
     * @param \Closure|array|null $where
     * @param array $options
     *
     * @return CoinUserWallet
     */
    public function one($where = null, array $options = [])
    {
        return $this->queryOne($where, array_merge([
            'exception' => function () use ($where) {
                return UserWalletNotFoundException::withData($where);
            },
        ], $options));
    }

    /**
     * 获取用户指定链默认钱包
     *
     * @param $chain
     * @param array $options
     */
    public function getByChain($user, $chain, array $options = [])
    {
        $userId = with_user_id($user);
        $wallet = $this->one([
            'user_id' => $userId,
            'chain'   => $chain,
        ], array_merge(['exception' => false], $options['oneOptions'] ?? []));

        if ( ! $wallet && ($options['create'] ?? true)) { // 是否自动创建钱包
            $wallet = $this->createWithChain($user, $chain);
        }

        return $wallet;
    }

    /**
     * 创建用户钱包
     *
     * @param $user
     * @param $chain
     * @param array $options
     *
     * @return mixed
     */
    public function createWithChain($user, $chain, array $options = [])
    {
        $chain = strtoupper($chain);
        /** @var CoinService $coinService */
        //$coinService = resolve(CoinService::class);
        // 传了chain 为啥还要查对应的coin？？没有这个coin直接报错
        //$coin = $coinService->getBySymbol($chain); // symbol和chain同名表示为主链, 所以直接查询symbol即可

        $userId = with_user_id($user);

        $tokenIO = resolve(TokenIO::class);
        //$newWallet = $tokenIO->newWallet($coin['chain'], $userId);
        $newWallet = $tokenIO->newWallet($chain, $userId);

        return $this->create([
            'user_id' => $userId,
            //'chain'   => $coin->chain,
            'chain'   => $chain,
            'address' => $newWallet['address'],
            'tokenio_version' => 1,
        ], $options);
    }
}
