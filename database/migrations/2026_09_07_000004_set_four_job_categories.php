<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $now = now();
        DB::table('categories')->where('section_id', 'jobs')->update(['is_active' => false, 'updated_at' => $now]);

        $categories = [
            ['name'=>'CGSSB','hindi_name'=>'छत्तीसगढ़ कर्मचारी चयन बोर्ड (CGSSB)','slug'=>'cgssb','icon'=>'users','color'=>'#E97800','display_order'=>1],
            ['name'=>'CGPSC','hindi_name'=>'छत्तीसगढ़ लोक सेवा आयोग (CGPSC)','slug'=>'cgpsc','icon'=>'landmark','color'=>'#1565C0','display_order'=>2],
            ['name'=>'Central Govt','hindi_name'=>'केंद्र सरकार की नौकरियां','slug'=>'central-govt','icon'=>'building-columns','color'=>'#2E7D32','display_order'=>3],
            ['name'=>'Contractual','hindi_name'=>'संविदा / अनुबंध आधारित नौकरियां','slug'=>'contractual','icon'=>'file-contract','color'=>'#616161','display_order'=>4],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug'=>$category['slug']],
                array_merge($category, ['section_id'=>'jobs','is_active'=>true,'updated_at'=>$now,'created_at'=>$now])
            );
        }
    }

    public function down(): void
    {
        DB::table('categories')->whereIn('slug',['cgssb','cgpsc','central-govt','contractual'])->update(['is_active'=>false,'updated_at'=>now()]);
    }
};
