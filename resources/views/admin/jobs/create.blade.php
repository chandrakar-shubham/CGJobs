@extends('layouts.admin')
@section('title','नई नौकरी जोड़ें')
@section('content')
<div class="max-w-5xl mx-auto space-y-6">
  <div><h2 class="text-xl font-bold text-slate-900">नई नौकरी प्रकाशित करें</h2><p class="text-xs text-slate-500 mt-1">Jobs के लिए केवल 4 मुख्य categories और concerned department इस्तेमाल करें.</p></div>
  <form method="POST" action="{{ route('admin.jobs.store') }}" class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-8 space-y-6">@csrf
    <div class="grid md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-2xl">
      <div><label class="label">Main Job Category *</label><select name="job_category" required class="input">@foreach($jobCategories as $c)<option value="{{ $c }}" @selected(old('job_category','CGSSB')===$c)>{{ $c }}</option>@endforeach</select></div>
      <div><label class="label">Concerned Department *</label><select name="department" required class="input">@foreach($departments as $d)<option value="{{ $d }}" @selected(old('department')===$d)>{{ $d }}</option>@endforeach</select></div>
      <div><label class="label">Post Type *</label><select name="post_type" class="input"><option value="job">Job Notification</option><option value="admit_card">Admit Card</option><option value="result">Result</option><option value="syllabus">Syllabus</option><option value="answer_key">Answer Key</option></select><input type="hidden" name="section" value="jobs"></div>
    </div>
    <div class="grid md:grid-cols-3 gap-5"><div class="md:col-span-2"><label class="label">Job Name *</label><input name="title" value="{{ old('title') }}" required class="input" placeholder="Lab Assistant Recruitment 2026"></div><div><label class="label">Released By</label><input name="source" value="{{ old('source') }}" class="input" placeholder="CGSSB / CGPSC / SSC"></div></div>
    <div class="grid md:grid-cols-4 gap-4"><div><label class="label">Total Posts</label><input name="vacancies" value="{{ old('vacancies') }}" class="input" placeholder="36"></div><div><label class="label">Apply From</label><input name="application_start" value="{{ old('application_start') }}" class="input" placeholder="07 Jul 2026"></div><div><label class="label">Last Date</label><input name="last_date" value="{{ old('last_date') }}" class="input" placeholder="31 Jul 2026"></div><div><label class="label">Exam Date</label><input name="exam_date" value="{{ old('exam_date') }}" class="input"></div></div>
    <div class="grid md:grid-cols-3 gap-4"><div><label class="label">Eligibility</label><input name="eligibility" value="{{ old('eligibility') }}" class="input"></div><div><label class="label">Age Limit</label><input name="age_limit" value="{{ old('age_limit') }}" class="input"></div><div><label class="label">Salary</label><input name="salary" value="{{ old('salary') }}" class="input"></div></div>
    <div><label class="label">Short Summary *</label><textarea name="summary" rows="3" required class="input">{{ old('summary') }}</textarea></div>
    <div><label class="label">Detailed Information</label><textarea name="detailed_content" rows="7" class="input">{{ old('detailed_content') }}</textarea></div>
    <div class="grid md:grid-cols-2 gap-5"><div><label class="label">Official Notification URL</label><input type="url" name="official_notification_url" value="{{ old('official_notification_url') }}" class="input"></div><div><label class="label">Apply Online URL</label><input type="url" name="apply_url" value="{{ old('apply_url') }}" class="input"></div></div>
    <div class="flex flex-wrap gap-6 border-t pt-4"><label class="check"><input type="checkbox" name="is_breaking" value="1"> HOT / Breaking</label><label class="check"><input type="checkbox" name="broadcast_push" value="1" checked> Android Push Notification</label></div>
    <div class="flex justify-end"><button class="px-6 py-3 rounded-xl bg-brand-600 text-white font-bold">Publish Job</button></div>
  </form>
</div>
<style>.label{display:block;font-size:11px;font-weight:800;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:.04em}.input{width:100%;border:1px solid #dbe2ea;border-radius:12px;padding:11px 13px;background:#fff;font-size:14px;outline:none}.check{font-size:13px;font-weight:700;color:#475569;display:flex;gap:8px;align-items:center}</style>
@endsection
