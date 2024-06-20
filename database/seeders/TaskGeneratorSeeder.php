<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskGeneratorSeeder extends Seeder
{

    public function run()
    
    {   
        $oneMonthAgo = Carbon::now()->subMonth();
        
        DB::table('task_generators')->insert([
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'shopee_brand_portal_shop',
                'link' => 'https://brandportal.shopee.com/seller/insights/sales/shop',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'shopee_brand_portal_ads',
                'link' => 'https://brandportal.shopee.com/seller/mkt/my-ads-report/overall',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'meta_cpas',
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=197030438627900&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&selected_report_id=120210173859280790&breakdown_regrouping=1&nav_source=no_referrer',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 2,
                'type' => 'meta_cpas',
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=2653240738245579&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&selected_report_id=120211841375530584&view_type=TABLE&breakdown_regrouping=1&nav_source=no_referrer',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 3,
                'type' => 'meta_cpas',
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=1362414174229183&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&selected_report_id=120209137231300785&breakdown_regrouping=1&nav_source=no_referrer',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'selenium_test',
                'link' => 'https://www.google.com',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 2,
                'type' => 'selenium_test',
                'link' => 'https://www.google.com',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'sample_data',
                'link' => 'https://go.microsoft.com/fwlink/?LinkID=521962',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 2,
                'type' => 'sample_data',
                'link' => 'https://go.microsoft.com/fwlink/?LinkID=521962',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 3,
                'type' => 'sample_data',
                'link' => 'https://go.microsoft.com/fwlink/?LinkID=521962',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 4,
                'type' => 'sample_data',
                'link' => 'https://go.microsoft.com/fwlink/?LinkID=521962',
                'frequency' => 'daily',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'tiktok_psa',
                'link' => 'https://ads.tiktok.com/i18n/reporting/pivot/table/edit?reportId=7377225443851649040&aadvid=6959448705942487041',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'tiktok_lsa',
                'link' => 'https://ads.tiktok.com/i18n/reporting/pivot/table/edit?reportId=7377224782003044369&aadvid=6959448705942487041',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 1,
                'type' => 'tiktok_vsa',
                'link' => 'https://ads.tiktok.com/i18n/reporting/pivot/table/edit?reportId=7349018890900340737&aadvid=6959448705942487041',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
        ]);
    }
}
