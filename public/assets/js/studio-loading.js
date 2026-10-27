/* Delay feedback, never data. No fake progress or artificial minimum duration. */
(()=>{'use strict';window.StudioLoading={begin(region,{initial=false,columns=1,rows=4}={}){
 if(!region)return()=>{};region.setAttribute('aria-busy','true');let shapes=[];
 const timer=setTimeout(()=>{if(!initial)return;for(let i=0;i<rows;i++){const table=region.tagName==='TBODY',row=document.createElement(table?'tr':'div');row.className='studio-skeleton';row.setAttribute('aria-hidden','true');for(let j=0;j<columns;j++){const cell=document.createElement(table?'td':'span');const line=document.createElement('i');line.className='studio-skeleton-line';cell.append(line);row.append(cell);}region.append(row);shapes.push(row);}},300);
 return()=>{clearTimeout(timer);shapes.forEach(e=>e.remove());region.setAttribute('aria-busy','false');};
 }};})();
