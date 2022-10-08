<?php

namespace App\Console;

use App\Models\Currency;
use App\Models\Generalsetting;
use App\Models\MonthlyOrderReferral;
use App\Models\Order;
use App\Models\Referral;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\CrudGenerator::class,
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function (){
            Log::info("Cron Job Executed successfully");
        });
        $schedule->call(function (){
            Log::info("Monthly bonus Cron Executed successfull");
            //Monthly order for user
            $user_orders=Order::query()
                ->whereNotNull('user_id')
                ->where('created_at','>=',Carbon::parse()->startOfMonth()->format('Y-m-d H:i:s'))
                ->where('created_at','<=',Carbon::parse()->endOfMonth()->format('Y-m-d H:i:s'))
                ->get()
                ->groupBy('user_id');

            $ranges=MonthlyOrderReferral::query()
                ->where('status',1)
                ->get();
            $gs=Generalsetting::find(1);
            $curr = Currency::where('is_default','=',1)->first();
            $minRange=MonthlyOrderReferral::query()
                ->where('status',1)
                ->min('range_from');
            $maxRange=MonthlyOrderReferral::query()
                ->where('status',1)
                ->max('range_to');
            $maxRow=MonthlyOrderReferral::query()
                ->where('range_to',$maxRange)
                ->first();
            if($gs->is_order_referral==1 && $ranges->isNotEmpty()){
                foreach ($user_orders as $user_id=>$order){
                    $order_amount=$order->sum('pay_amount') * $curr->value;
                    $user=User::find($user_id);

                    if($user->ref_by){
                        if($order_amount>=$minRange && $order_amount<=$maxRange){
                            foreach ($ranges as $range){
                                if($order_amount>=$range->range_from && $order_amount<=$range->range_to){
                                    $refferer = User::find($user->ref_by);
                                    $refferer->balance+=$range->amount;
                                    $refferer->update();

                                    $history = new Referral();
                                    $history->referrer_id = $refferer->id;
                                    $history->referee_id = $user->id;
                                    $history->amount = $range->amount;
                                    $history->type = 2;
                                    $history->save();
                                }
                            }
                        }elseif($order_amount>$maxRange){
                            $refferer = User::find($user->ref_by);
                            $refferer->balance+=$maxRow->amount;
                            $refferer->update();

                            $history = new Referral();
                            $history->referrer_id = $refferer->id;
                            $history->referee_id = $user->id;
                            $history->amount = $maxRow->amount;
                            $history->type = 2;
                            $history->save();
                        }
                    }

                }
            }

        })->monthlyOn(date('t'),'23:59');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
