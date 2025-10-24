<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OptionList;

class OptionListSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // 1) layanan pendampingan
            ['group_key'=>'assistance_service','value'=>'pintu_timur_selatan','label'=>'Hanya akses pintu timur/selatan','sort_order'=>1],
            ['group_key'=>'assistance_service','value'=>'ruang_vip','label'=>'Hanya penggunaan ruang VIP','sort_order'=>2],
            ['group_key'=>'assistance_service','value'=>'protokoler_only','label'=>'Hanya pendampingan protokoler','sort_order'=>3],
            ['group_key'=>'assistance_service','value'=>'pintu_plus_protokoler','label'=>'Akses pintu + pendampingan protokoler','sort_order'=>4],
            ['group_key'=>'assistance_service','value'=>'vip_plus_protokoler','label'=>'Ruang VIP + pendampingan protokoler','sort_order'=>5],
            ['group_key'=>'assistance_service','value'=>'pintu_vip_protokoler','label'=>'Akses pintu + ruang VIP + pendampingan protokoler','sort_order'=>6],

            // 2) pintu
            ['group_key'=>'access_door','value'=>'timur','label'=>'Timur','sort_order'=>1],
            ['group_key'=>'access_door','value'=>'selatan','label'=>'Selatan','sort_order'=>2],

            // 3) tujuan akses
            ['group_key'=>'access_purpose','value'=>'jemput','label'=>'Jemput','sort_order'=>1],
            ['group_key'=>'access_purpose','value'=>'antar','label'=>'Antar','sort_order'=>2],

            // 4) jumlah pendamping protokoler (opsional)
            ['group_key'=>'protokoler_count','value'=>'1','label'=>'1','sort_order'=>1],
            ['group_key'=>'protokoler_count','value'=>'2','label'=>'2','sort_order'=>2],

            // 5) perlu pendampingan protokoler
            ['group_key'=>'need_protokoler_escort','value'=>'1','label'=>'Ya','sort_order'=>1],
            ['group_key'=>'need_protokoler_escort','value'=>'0','label'=>'Tidak','sort_order'=>2],
        ];

        foreach ($rows as $r) {
            OptionList::updateOrCreate(
                ['group_key'=>$r['group_key'], 'value'=>$r['value']],
                ['label'=>$r['label'], 'sort_order'=>$r['sort_order']]
            );
        }
    }
}

