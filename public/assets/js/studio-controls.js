/* Native component behavior. No data fetching, account changes or business mutations. */
(() => {
 'use strict';
 const canPopover = typeof HTMLElement.prototype.showPopover === 'function';
 const errorLinks = new WeakSet();
 const menus = new WeakSet(), dates = new WeakSet(), decorated = new WeakSet(), panels = new WeakSet(), anchors = new WeakMap();
 let serial = 0;
 const items = panel => Array.from(panel.querySelectorAll('a[href],button:not(:disabled),input:not(:disabled),[tabindex="0"]')).filter(node => node.getClientRects().length && !node.closest('[hidden]'));
 const open = panel => canPopover && panel.matches(':popover-open');
 function place(panel, trigger) {
  if (!open(panel)) return;
  const box = trigger.getBoundingClientRect();
  if (box.bottom < 0 || box.top > innerHeight) { panel.hidePopover(); return; }
  const width = panel.offsetWidth, height = panel.offsetHeight;
  const inline = document.documentElement.dir === 'rtl' ? box.right - width : box.left;
  const top = box.bottom + 6 + height <= innerHeight - 12 ? box.bottom + 6 : box.top - height - 6;
  panel.style.inset = 'auto';
  panel.style.left = Math.max(12, Math.min(inline, innerWidth - width - 12)) + 'px';
  panel.style.top = Math.max(12, Math.min(top, innerHeight - height - 12)) + 'px';
 }
 function bindPanel(panel, trigger) {
  if (panels.has(panel)) return;
  panels.add(panel); anchors.set(panel, trigger);
  panel.addEventListener('toggle', () => {
   const expanded = open(panel);
   trigger.setAttribute('aria-expanded', String(expanded));
   if (trigger.closest('.workspace-action-menu')!==null) place(panel,trigger);
  });
  trigger.addEventListener('keydown', event => {
   if (!['ArrowDown', 'ArrowUp'].includes(event.key) || !canPopover) return;
   event.preventDefault();
   if (!open(panel)) trigger.click();
   if (trigger.closest('.workspace-action-menu')!==null) place(panel, trigger);
   const all = items(panel); (event.key === 'ArrowUp' ? all.at(-1) : all[0])?.focus();
  });
 }
 function bindRow(menu) {
  if (menus.has(menu)) return;
  const trigger = menu.querySelector(':scope > button'), panel = menu.querySelector(':scope > nav');
  if (!trigger || !panel || !canPopover) return;
  menus.add(menu); panel.setAttribute('popover', 'auto'); panel.dataset.studioMenuPanel = '';
  panel.id ||= 'studio-row-menu-' + ++serial;
  trigger.setAttribute('aria-controls', panel.id); trigger.setAttribute('popovertarget',panel.id); trigger.setAttribute('aria-expanded', 'false');
  bindPanel(panel, trigger);
  panel.addEventListener('click', event => { if (event.target.closest('a,button') && open(panel)) panel.hidePopover(); });
 }
 function bindIcon(button) {
  if(decorated.has(button))return;
  const shapes={eye:[['path',{d:'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z'}],['circle',{cx:'12',cy:'12',r:'3'}]],more:[['circle',{cx:'5',cy:'12',r:'1'}],['circle',{cx:'12',cy:'12',r:'1'}],['circle',{cx:'19',cy:'12',r:'1'}]]};
  const paths=shapes[button.dataset.studioIcon];if(!paths)return;
  decorated.add(button);const label=button.getAttribute('aria-label')||button.textContent.trim();
  button.setAttribute('aria-label',label);button.title=label;button.classList.add('studio-icon-action');
  const ns='http://www.w3.org/2000/svg',svg=document.createElementNS(ns,'svg');
  for(const[key,value]of Object.entries({viewBox:'0 0 24 24',fill:'none',stroke:'currentColor','stroke-width':'1.7','aria-hidden':'true'}))svg.setAttribute(key,value);
  for(const[tag,attributes]of paths){const node=document.createElementNS(ns,tag);for(const[k,v]of Object.entries(attributes))node.setAttribute(k,v);svg.append(node);}button.replaceChildren(svg);
 }
 function bindDate(input) {
  if (dates.has(input) || typeof input.showPicker !== 'function' || input.closest('.studio-date-field,.studio-time-field')) return;
  dates.add(input);
  const label = Array.from(input.labels || []).map(node => node.textContent.trim()).join(' ');
  const wrap = document.createElement('div'), button = document.createElement('button');
  wrap.className = 'studio-date-field'; button.className = 'studio-date-trigger'; button.type = 'button';
  button.setAttribute('aria-label', (document.getElementById('studio-control-labels')?.dataset.date || 'Choose date') + (label ? ': ' + label : ''));
  const ns = 'http://www.w3.org/2000/svg', svg = document.createElementNS(ns, 'svg'), rect = document.createElementNS(ns, 'rect'), path = document.createElementNS(ns, 'path');
  svg.setAttribute('viewBox', '0 0 24 24'); svg.setAttribute('fill', 'none'); svg.setAttribute('stroke', 'currentColor'); svg.setAttribute('stroke-width', '1.7'); svg.setAttribute('aria-hidden', 'true');
  rect.setAttribute('x', '3'); rect.setAttribute('y', '5'); rect.setAttribute('width', '18'); rect.setAttribute('height', '16'); rect.setAttribute('rx', '3');
  path.setAttribute('d', 'M7 3v4m10-4v4M3 11h18'); svg.append(rect, path); button.append(svg);
  input.before(wrap); wrap.append(input, button); button.disabled = input.disabled || input.readOnly;
  button.addEventListener('click', () => { if (input.disabled || input.readOnly) return; try { input.showPicker(); } catch { input.focus(); } });
 }
 function bindTime(input) {
  if (dates.has(input) || typeof input.showPicker !== 'function' || input.closest('.studio-time-field')) return;
  dates.add(input);const wrap=document.createElement('div'),button=document.createElement('button');wrap.className='studio-time-field';button.type='button';button.className='studio-time-trigger';
  const label=Array.from(input.labels||[]).map(e=>e.textContent.trim()).join(' ');button.setAttribute('aria-label',label);button.title=label;
  const ns='http://www.w3.org/2000/svg',svg=document.createElementNS(ns,'svg'),circle=document.createElementNS(ns,'circle'),path=document.createElementNS(ns,'path');for(const[k,v]of Object.entries({viewBox:'0 0 24 24',fill:'none',stroke:'currentColor','stroke-width':'1.7','aria-hidden':'true'}))svg.setAttribute(k,v);circle.setAttribute('cx','12');circle.setAttribute('cy','12');circle.setAttribute('r','9');path.setAttribute('d','M12 7v5l3 2');svg.append(circle,path);button.append(svg);input.before(wrap);wrap.append(input,button);button.disabled=input.disabled||input.readOnly;button.addEventListener('click',()=>{if(input.disabled||input.readOnly)return;try{input.showPicker();}catch{input.focus();}});
 }
 function bindErrorSummary(summary) {
  const owner=summary.closest('form') || summary.parentElement?.querySelector(':scope > form') || summary.closest('dialog')?.querySelector('form');
  if(!owner)return;
  summary.querySelectorAll('a[href^="#"]').forEach(link=>{
   if(errorLinks.has(link))return;
   let key;try{key=decodeURIComponent(link.getAttribute('href').slice(1));}catch{return;}
   const bracketKey=key.replace(/\.([^.]+)/g,'[$1]');
   const control=Array.from(owner.elements).find(field=>field.name===key||field.id===key||field.name===bracketKey);
   if(!control||control.type==='hidden'||control.disabled)return;
   errorLinks.add(link);control.id||='studio-invalid-field-'+ ++serial;link.id||='studio-field-error-'+ ++serial;
   control.setAttribute('aria-invalid','true');
   const described=new Set((control.getAttribute('aria-describedby')||'').split(/\s+/).filter(Boolean));described.add(link.id);control.setAttribute('aria-describedby',Array.from(described).join(' '));
   const focusTarget=()=>control.closest('.studio-combobox')?.querySelector('[role=combobox]')||control;
   const actual=focusTarget();if(actual!==control){actual.setAttribute('aria-invalid','true');actual.setAttribute('aria-describedby',Array.from(described).join(' '));}
   link.setAttribute('href','#'+encodeURIComponent(actual.id||control.id));
   link.addEventListener('click',event=>{event.preventDefault();for(let node=control.parentElement;node&&node!==owner;node=node.parentElement)if(node.tagName==='DETAILS')node.open=true;const target=focusTarget();if(target.getClientRects().length){target.focus({preventScroll:true});target.closest('.form-field')?.scrollIntoView({block:'center'});}});
  });
 }
 function enhance(root) {
  if (root.nodeType !== 1 && root !== document) return;
  if(root.matches?.('[data-native-errors],.form-notice--error'))bindErrorSummary(root);root.querySelectorAll?.('[data-native-errors],.form-notice--error').forEach(bindErrorSummary);
  if(root.matches?.('input[type=time]'))bindTime(root);root.querySelectorAll?.('input[type=time]').forEach(bindTime);
  if(root.matches?.('button[data-studio-icon]'))bindIcon(root);root.querySelectorAll?.('button[data-studio-icon]').forEach(bindIcon);
  if (root.matches?.('.workspace-action-menu')) bindRow(root);
  root.querySelectorAll?.('.workspace-action-menu').forEach(bindRow);
  if (root.matches?.('input[type=date]')) bindDate(root);
  root.querySelectorAll?.('input[type=date]').forEach(bindDate);
  root.querySelectorAll?.('[popovertarget]').forEach(trigger => {
   const panel = document.getElementById(trigger.getAttribute('popovertarget'));
   if (panel?.matches('[data-studio-menu-panel]')) bindPanel(panel, trigger);
  });
 }
 document.addEventListener('keydown', event => {
  if (!canPopover) return;
  const panel = event.target.closest?.('[data-studio-menu-panel]') || document.querySelector('[data-studio-menu-panel]:popover-open');
  if (!panel || !open(panel)) return;
  const trigger = anchors.get(panel);
  if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); panel.hidePopover(); trigger?.focus(); return; }
  if (!panel.contains(event.target) || event.target.matches('input,textarea,select')) return;
  const all = items(panel), current = all.indexOf(document.activeElement);
  if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
   event.preventDefault(); event.stopPropagation();
   const index = event.key === 'Home' ? 0 : event.key === 'End' ? all.length - 1 : (current + (event.key === 'ArrowDown' ? 1 : -1) + all.length) % all.length;
   all[index]?.focus();
  } else if (event.key === 'Tab' && ((!event.shiftKey && current === all.length - 1) || (event.shiftKey && current === 0))) {
   panel.hidePopover(); trigger?.focus();
  }
 }, true);
 const positionOpenRows = () => { if (canPopover) document.querySelectorAll('.workspace-action-menu > nav:popover-open').forEach(panel => place(panel, anchors.get(panel))); };
 window.addEventListener('resize', positionOpenRows);
 window.addEventListener('scroll', event => { if (!event.target.matches?.('[data-studio-menu-panel]')) positionOpenRows(); }, true);
 enhance(document);
 const pending = new Set(); let scheduled = false;
 new MutationObserver(records => {
  for (const record of records) {
   if (record.type === 'attributes') {
    const input = record.target;
    if (input.matches('input[type=date],input[type=time]')) { const button = input.closest('.studio-date-field,.studio-time-field')?.querySelector('button'); if (button) button.disabled = input.disabled || input.readOnly; }
   } else for (const node of record.addedNodes) if (node.nodeType === 1) pending.add(node);
  }
  if (!scheduled && pending.size) { scheduled = true; requestAnimationFrame(() => { scheduled = false; for (const node of pending) if (node.isConnected) enhance(node); pending.clear(); }); }
 }).observe(document.body, {childList:true,subtree:true,attributes:true,attributeFilter:['disabled','readonly']});
})();
