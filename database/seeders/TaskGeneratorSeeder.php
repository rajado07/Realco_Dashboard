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
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=197030438627900&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&attribution_windows=default&breakdowns=days_1%2Cadset_name&empty_comparison_time_range=true&filter_set=had_delivery-STRING%1EEQUAL%1E%221%22&locked_dimensions=1&metrics=spend%2Ccatalog_segment_actions%3Aomni_view_content%2Ccatalog_segment_actions%3Aomni_add_to_cart%2Ccatalog_segment_actions%3Aomni_purchase%2Ccatalog_segment_value%3Aomni_purchase%2Cfrequency%2Cimpressions%2Cclicks%2Cctr&sort_spec=days_1~desc&time_range=2024-05-30_2024-06-06%2Clast_7d&breakdown_regrouping=1&nav_source=no_referrer',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 2,
                'type' => 'meta_cpas',
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=2653240738245579&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&attribution_windows=default&breakdowns=days_1%2Cadset_name&empty_comparison_time_range=true&filter_set=had_delivery-STRING%1EEQUAL%1E%221%22&limit=50&locked_dimensions=1&metrics=spend%2Ccatalog_segment_actions%3Aomni_view_content%2Ccatalog_segment_actions%3Aomni_add_to_cart%2Ccatalog_segment_actions%3Aomni_purchase%2Ccatalog_segment_value%3Aomni_purchase%2Cfrequency%2Cimpressions%2Cclicks%2Cctr&report_name=Untitled%20report&sort_spec=days_1~desc&time_range=2024-05-30_2024-06-06%2Clast_7d&target_currency=IDR&view_type=TABLE',
                'frequency' => 'weekly',
                'run_at' => '09:00:00',
                'created_at' => $oneMonthAgo,
                'updated_at' => $oneMonthAgo,
            ],
            [
                'brand_id' => 1,
                'market_place_id' => 3,
                'type' => 'meta_cpas',
                'link' => 'https://adsmanager.facebook.com/adsmanager/reporting/view?act=1362414174229183&business_id=141399696986824&event_source=SANITIZE_METRICS&global_scope_id=141399696986824&attribution_windows=default&breakdowns=days_1%2Cadset_name&empty_comparison_time_range=true&filter_set=had_delivery-STRING%1EEQUAL%1E%221%22&locked_dimensions=1&metrics=spend%2Ccatalog_segment_actions%3Aomni_view_content%2Ccatalog_segment_actions%3Aomni_add_to_cart%2Ccatalog_segment_actions%3Aomni_purchase%2Ccatalog_segment_value%3Aomni_purchase%2Cimpressions%2Cclicks%2Cctr&sort_spec=days_1~desc&time_range=2024-06-09_2024-06-16%2Clast_7d&breakdown_regrouping=1&nav_source=no_referrer',
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
