<?php

namespace Modules\Coin\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Coin\Models\CoinTokenioNotice;

class TokenioNoticeProcessed
{
    use SerializesModels;

    /**
     * @var CoinTokenioNotice
     */
    public $coinTokenioNotice;

    public function __construct(CoinTokenioNotice $coinTokenioNotice)
    {
        $this->coinTokenioNotice = $coinTokenioNotice;
    }
}
