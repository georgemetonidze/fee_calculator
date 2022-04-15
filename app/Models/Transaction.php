<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;

class Transaction extends Model
{
    const DEPOSIT_FEE = 0.03;
    const PRIVATE_WITHDRAWAL_FEE = 0.3;
    const BUSINESS_WITHDRAWAL_FEE = 0.5;
    const WEEKLY_WITHDRAWAL_LIMIT = 1000;
    use HasFactory;

    protected $fillable = [
        'date',
        'user_id',
        'user_type',
        'transaction_type',
        'amount',
        'currency'
    ];

    protected static function round_up($number, $precision = 2)
    {
        $fig = (int)str_pad('1', $precision, '0');
        return (ceil($number * $fig) / $fig);
    }

    public static function calculateFee(
        array $row,
        Carbon $week_start_date,
        Carbon $week_end_date,
        array $rates
    )
    {
        $fee = 0;
        switch ($row[3]) {
            case 'deposit':
                $fee = self::DEPOSIT_FEE;
                break;
            case 'withdraw':
                switch ($row[2]) {
                    case 'private':
                        $transactions = Transaction::where('user_id', $row[1])
                            ->whereBetween('created_at',[$week_start_date,$week_end_date])->get();
                        if ($transactions->count() > 3) {
                            $fee = self::PRIVATE_WITHDRAWAL_FEE;
                        } else {
//                            $sumInEuros = 0;
//                            foreach ($transactions as $transaction) {
//                                $row[5] --- "JPY"
//                                $sumInEuros += $transaction->amount*$rates[];
//                            }
                            $fee = self::PRIVATE_WITHDRAWAL_FEE;
                        }
                        break;
                    case 'business':
                        $fee = self::BUSINESS_WITHDRAWAL_FEE;
                        break;
                }
                break;
        }
        return $fee;
    }

    public static function calculateAmount($amount, $fee)
    {


        $amount += (($amount / 100) * $fee);
        return $amount;
    }

    public static function convertToEuro(array $rates, Transaction $transaction) {

    }
}
