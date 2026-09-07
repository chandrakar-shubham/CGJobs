document.addEventListener('DOMContentLoaded',function(){
 const q=s=>document.querySelector(s), all=s=>Array.from(document.querySelectorAll(s));
 const title=q('[data-seo="title"]'), summary=q('[data-editable="summary"]'), content=q('[data-editable="detailed_content"]'), image=q('[data-seo="image"]'), apply=q('[data-seo="apply"]'), note=q('[name="official_notification_url"]'), source=q('[name="source_url"]');
 const checks=q('#cjChecks'), score=q('#cjScore'), bar=q('#cjBar'), suggest=q('#cjSuggest'), gt=q('#cjGoogleTitle'), gd=q('#cjGoogleDesc'), gu=q('#cjGoogleUrl'), preview=q('#cjPreview'), count=q('#cjSummaryCount');
 const hiddenSummary=q('#cjSummaryValue'), hiddenContent=q('#cjContentValue');
 const text=e=>e?(e.innerText||e.textContent||'').replace(/\s+/g,' ').trim():'';
 const html=e=>e?e.innerHTML.trim():'';
 function syncEditors(){if(hiddenSummary)hiddenSummary.value=html(summary);if(hiddenContent)hiddenContent.value=html(content)}
 function run(){if(!checks)return;syncEditors();let rows=[],points=0;const t=(title?.value||'').trim(),s=text(summary),c=text(content);
  function add(name,ok,good,bad){rows.push([name,ok,ok?good:bad]);if(ok)points+=10}
  add('Job title length',t.length>=30&&t.length<=70,t.length+' chars','Aim for 30–70 chars');
  add('Short summary',s.length>=50&&s.length<=160,s.length+' chars','Aim for 50–160 chars');
  add('Full description',c.length>=300,c.length+' chars','Aim for 300+ chars');
  add('Keyword in title',/job|recruit|recruitment|bharti|भर्ती|vacancy|notification/i.test(t),'Found','Add a relevant recruitment keyword');
  add('Keyword in content',/job|recruit|recruitment|bharti|भर्ती|vacancy|notification/i.test(t+' '+s+' '+c),'Found','Use the main keyword naturally');
  add('Main image',!!image?.value,'Added','Add a main image');add('Apply URL',!!apply?.value,'Added','Add official Apply URL');add('Notification URL',!!note?.value,'Added','Add official notification URL');add('Source URL',!!source?.value,'Added','Add source URL');
  const pct=Math.round(points/rows.length);score.textContent=pct+'/100';bar.style.width=pct+'%';checks.innerHTML=rows.map(r=>'<li class="'+(r[1]?'ok':'warn')+'"><span>'+r[0]+'</span><span>'+r[2]+'</span></li>').join('');
  const tips=[];if(t.length<30||t.length>70)tips.push('Keep Job Title between 30–70 characters.');if(s.length<50||s.length>160)tips.push('Keep Short Summary between 50–160 characters.');if(c.length<300)tips.push('Add at least 300 characters of useful full description.');if(!image?.value)tips.push('Add a relevant Main Image.');if(!apply?.value)tips.push('Add the official Apply URL.');suggest.innerHTML='<b>Suggestions</b><br>'+((tips.length?tips:['Content is well prepared for publishing.']).map(x=>'• '+x).join('<br>'));
  if(gt)gt.textContent=t||'Your Job Title';if(gd)gd.textContent=s||'Your short summary will appear here.';if(gu)gu.textContent=location.origin+'/jobs/'+(t?t.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/^-|-$/g,''):'your-job');if(count)count.textContent=s.length;
 }
 all('.cj-editable').forEach(editor=>{editor.addEventListener('input',run);editor.addEventListener('blur',syncEditors)});
 all('[data-toolbar] button').forEach(btn=>btn.addEventListener('mousedown',e=>e.preventDefault()));
 all('[data-toolbar] button').forEach(btn=>btn.addEventListener('click',function(){const editor=this.closest('.cj-editor')?.querySelector('.cj-editable');if(!editor)return;editor.focus();const cmd=this.dataset.cmd,block=this.dataset.block,val=this.dataset.value;if(block)document.execCommand('formatBlock',false,block);else if(cmd==='createLink'){const url=prompt('Enter link URL');if(url)document.execCommand('createLink',false,url)}else if(cmd)document.execCommand(cmd,false,val||null);syncEditors();run()}));
 all('form').forEach(form=>form.addEventListener('submit',function(){syncEditors();run()}));
 all('input,textarea,select').forEach(e=>e.addEventListener('input',run));
 image?.addEventListener('input',function(){const u=this.value.trim();if(!preview)return;if(!u){preview.innerHTML='<span>Main image preview</span>';return}preview.innerHTML='<img alt="Main image preview">';const im=preview.querySelector('img');im.src=u;im.onerror=()=>preview.innerHTML='<span>Image could not be loaded</span>'});
 const workflow=q('#cjWorkflow'),schedule=q('#cjSchedule'),state=q('#cjPublishState');
 all('[data-workflow]').forEach(btn=>btn.addEventListener('click',function(e){const v=this.dataset.workflow;if(v==='scheduled'&&workflow?.value==='scheduled'){syncEditors();this.closest('form')?.submit();return}if(workflow)workflow.value=v;if(schedule)schedule.classList.toggle('hidden',v!=='scheduled');if(state)state.textContent=v==='draft'?'Draft will be saved':(v==='scheduled'?'Ready to schedule':'Ready to publish');if(v==='scheduled')schedule?.querySelector('input')?.focus();if(v==='draft')this.closest('form')?.submit()}));
 workflow?.addEventListener('change',function(){if(schedule)schedule.classList.toggle('hidden',this.value!=='scheduled')});
 syncEditors();run();
});
