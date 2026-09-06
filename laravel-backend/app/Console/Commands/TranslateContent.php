<?php

namespace App\Console\Commands;

use App\Models\Job;
use App\Models\StaticGk;
use App\Services\TranslationService;
use Illuminate\Console\Command;

class TranslateContent extends Command
{
    protected $signature = 'cgjobs:translate {--type=all : jobs, gk or all}';
    protected $description = 'Translate existing English master content to cached Hindi content';

    public function handle(TranslationService $translator): int
    {
        $type=$this->option('type');
        if(in_array($type,['all','jobs'],true)){
            foreach(Job::query()->get() as $job){
                $job->title_en=$job->title_en?:$job->title;$job->summary_en=$job->summary_en?:$job->summary;$job->detailed_content_en=$job->detailed_content_en?:$job->detailed_content;$job->category_en=$job->category_en?:$job->category;$job->eligibility_en=$job->eligibility_en?:$job->eligibility;$job->selection_process_en=$job->selection_process_en?:$job->selection_process;
                $t=$translator->translateMany(['title'=>$job->title_en,'summary'=>$job->summary_en,'detailed'=>$job->detailed_content_en,'category'=>$job->category_en,'eligibility'=>$job->eligibility_en,'selection'=>$job->selection_process_en]);
                $job->title=$t['title']?:$job->title_en;$job->summary=$t['summary']?:$job->summary_en;$job->detailed_content=$t['detailed']?:$job->detailed_content_en;$job->category=$t['category']?:$job->category_en;$job->eligibility=$t['eligibility']?:$job->eligibility_en;$job->selection_process=$t['selection']?:$job->selection_process_en;$job->relative_time_en=$job->relative_time_en?:'Recently';$job->translation_status=$t['title']?'ready':'pending';$job->translated_at=$t['title']?now():null;$job->save();$this->line("Translated job #{$job->id}");
            }
        }
        if(in_array($type,['all','gk'],true)){
            foreach(StaticGk::query()->get() as $gk){
                $gk->title_en=$gk->title_en?:$gk->hindi_title?:$gk->title;$gk->category_en=$gk->category_en?:$gk->category;$gk->question_en=$gk->question_en?:$gk->question;$gk->answer_en=$gk->answer_en?:$gk->answer;$gk->detailed_notes_en=$gk->detailed_notes_en?:$gk->detailed_notes;$gk->year_exam_reference_en=$gk->year_exam_reference_en?:$gk->year_exam_reference;
                $t=$translator->translateMany(['title'=>$gk->title_en,'category'=>$gk->category_en,'question'=>$gk->question_en,'answer'=>$gk->answer_en,'notes'=>$gk->detailed_notes_en,'reference'=>$gk->year_exam_reference_en]);$points=[];foreach(($gk->key_points_en?:$gk->key_points?:[]) as $point)$points[]=$translator->translate($point)?:$point;
                $gk->title=$t['title']?:$gk->title_en;$gk->hindi_title=$gk->title;$gk->category=$t['category']?:$gk->category_en;$gk->category_hindi=$gk->category;$gk->question=$t['question']?:$gk->question_en;$gk->answer=$t['answer']?:$gk->answer_en;$gk->detailed_notes=$t['notes']?:$gk->detailed_notes_en;$gk->year_exam_reference=$t['reference']?:$gk->year_exam_reference_en;$gk->key_points=$points;$gk->translation_status=$t['title']?'ready':'pending';$gk->translated_at=$t['title']?now():null;$gk->save();$this->line("Translated GK #{$gk->id}");
            }
        }
        return self::SUCCESS;
    }
}
