<?php

namespace App\Imports;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Concerns\ToModel;

class TransactionImport implements ToModel

{
    /**
     * @var Collection
     */
    public $data;
    /**
     * @var string
     */
    private $week_start_date;
    /**
     * @var string
     */
    private $week_end_date;
    /**
     * @var array|mixed
     */
    private $rates;

    public function __construct()
    {
        $this->data = collect();
        $now = Carbon::now();
        $this->week_start_date = $now->startOfWeek();
        $this->week_end_date = $now->endOfWeek();
        $response = Http::get(config('custom.exchange_url'));
        $this->rates = $response->json()['rates'];
    }

    /**
     * @param array $row
     *
     * @return Transaction
     */


    public function model(array $row)
    {
        $fee = Transaction::calculateFee($row, $this->week_start_date, $this->week_end_date, $this->rates);
        $amount = Transaction::calculateAmount($row[4], $fee);

        return new Transaction([
            'date' => Carbon::parse($row[0]),
            'user_id' => $row[1],
            'user_type' => $row[2],
            'transaction_type' => $row[3],
            'amount' => $row[4],
//            'amount' => $amount,
            'currency' => $row[5],
        ]);
    }

}
