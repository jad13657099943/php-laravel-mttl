<?php

namespace Modules\Otc\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Modules\Otc\Jobs\TradeExpiredJob;
use Modules\Otc\Models\OtcTrade;

class CancelExpireOtcTrade extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'otc:cancel-expired-trade';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将已过期的撮合交易立即取消';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $otcTradeList = OtcTrade::where('expired_at', '<=', Carbon::now())
            ->where('status',OtcTrade::STATUS_FROZEN)->get();
        $this->line("Waiting Cancel Otc Trade Count:" . count($otcTradeList));
        foreach ($otcTradeList as $otcTrade) {
            TradeExpiredJob::dispatch($otcTrade);
        }
    }
}
